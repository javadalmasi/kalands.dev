<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Jobs\IndexNow\DispatchHourlyJob;
use App\Models\IndexNowRunLog;
use App\Models\Product;
use App\Repositories\SettingsRepository;
use App\Services\ActivityLogger;
use App\Services\IndexNowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class IndexNowController extends Controller
{
    public function __construct(
        private IndexNowService $indexNowService,
        private SettingsRepository $settings,
    ) {}

    public function hub(Request $request)
    {
        $engines = ['bing', 'yandex'];

        $weights = [];
        $enabled = [];
        $keys = [];
        foreach ($engines as $e) {
            $weights[$e] = $this->indexNowService->getHourlyWeights($e);
            $enabled[$e] = $this->indexNowService->isEnabled($e);
            $keys[$e] = $this->indexNowService->getVerificationKey($e);
        }

        $dailyLimit = $this->indexNowService->getDailyLimit();
        $today = now()->format('Y-m-d');
        $lastSubmissionsDate = $this->settings->get('indexnow.last_submission_date', '');

        $todaySubmitted = IndexNowRunLog::query()
            ->where('started_at', '>=', $today)
            ->where('status', '!=', 'pending')
            ->sum('total_submitted');

        $lastRuns = IndexNowRunLog::query()
            ->latest('id')
            ->limit(50)
            ->get();

        $totalActive = Product::query()->where('is_active', true)->count();
        $pendingProducts = Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('indexnow_submitted_at')
                  ->orWhereColumn('updated_at', '>', 'indexnow_submitted_at');
            })
            ->count();

        $nowTehran = now()->timezone('Asia/Tehran');
        $currentHour = (int) $nowTehran->format('G');

        $estimatedDaily = [];
        foreach ($engines as $e) {
            if ($enabled[$e] && !empty($keys[$e])) {
                $totalWeight = array_sum($weights[$e]);
                $estimatedDaily[$e] = $totalWeight > 0
                    ? number_format($dailyLimit)
                    : 0;
            } else {
                $estimatedDaily[$e] = 0;
            }
        }

        return view('dash.admin.indexnow-hub', compact(
            'weights',
            'enabled',
            'keys',
            'dailyLimit',
            'todaySubmitted',
            'lastRuns',
            'totalActive',
            'pendingProducts',
            'currentHour',
            'nowTehran',
            'estimatedDaily',
        ));
    }

    public function saveSettings(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $engines = ['bing', 'yandex'];

        $request->validate([
            'daily_limit' => 'required|integer|min:100|max:1000000',
            'bing_enabled' => 'nullable|boolean',
            'yandex_enabled' => 'nullable|boolean',
            'bing_key' => 'nullable|string|max:255',
            'yandex_key' => 'nullable|string|max:255',
            'bing_weights' => 'nullable',
            'yandex_weights' => 'nullable',
        ]);

        $this->indexNowService->setDailyLimit((int) $request->input('daily_limit'));

        foreach ($engines as $engine) {
            $enabled = (bool) $request->input("{$engine}_enabled", false);
            $this->indexNowService->setEnabled($engine, $enabled);

            $newKey = $request->input("{$engine}_key");
            $oldKey = $this->indexNowService->getVerificationKey($engine);

            if ($newKey !== $oldKey) {
                if (!empty($oldKey)) {
                    $this->indexNowService->removeVerificationFile($engine);
                }
                $this->indexNowService->setVerificationKey($engine, $newKey ?? '');
                if (!empty($newKey)) {
                    $this->indexNowService->generateVerificationFile($engine);
                }
            } elseif (!empty($newKey)) {
                $this->indexNowService->generateVerificationFile($engine);
            }

            $weightsInput = $request->input("{$engine}_weights");
            $weights = is_string($weightsInput) ? json_decode($weightsInput, true) : $weightsInput;
            if (is_array($weights) && count($weights) === 24) {
                $this->indexNowService->setHourlyWeights($engine, $weights);
            }
        }

        $activityLogger->log(
            'settings.indexnow.update',
            auth('admin')->user(),
            'بروزرسانی تنظیمات IndexNow',
        );

        return back()->with('message', 'تنظیمات IndexNow ذخیره شد.');
    }

    public function regenerateKey(Request $request, ActivityLogger $activityLogger): JsonResponse
    {
        $engine = $request->input('engine');
        if (!in_array($engine, ['bing', 'yandex'])) {
            return response()->json(['error' => 'Invalid engine'], 400);
        }

        $oldKey = $this->indexNowService->getVerificationKey($engine);
        if (!empty($oldKey)) {
            $this->indexNowService->removeVerificationFile($engine);
        }

        $newKey = strtolower(Str::random(32));
        $this->indexNowService->setVerificationKey($engine, $newKey);
        $this->indexNowService->generateVerificationFile($engine);

        $activityLogger->log(
            "settings.indexnow.key.{$engine}",
            auth('admin')->user(),
            "تولید کلید وریفای جدید برای {$this->indexNowService->engineLabel($engine)}",
        );

        return response()->json(['key' => $newKey]);
    }

    public function triggerHour(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $hour = (int) $request->input('hour', now()->format('G'));
        if ($hour < 0 || $hour > 23) {
            return back()->withErrors(['message' => 'ساعت وارد شده معتبر نیست.']);
        }

        DispatchHourlyJob::dispatch($hour);

        $activityLogger->log(
            'settings.indexnow.trigger',
            auth('admin')->user(),
            "اجرای دستی IndexNow برای ساعت {$hour}",
        );

        return back()->with('message', "پردازش ساعت {$hour} در صف قرار گرفت.");
    }

    public function getHourlyStats(Request $request): JsonResponse
    {
        $engine = $request->input('engine', 'bing');
        if (!in_array($engine, ['bing', 'yandex'])) {
            return response()->json(['error' => 'Invalid engine'], 400);
        }

        $logs = IndexNowRunLog::query()
            ->where('engine', $engine)
            ->where('created_at', '>=', now()->subDays(7))
            ->selectRaw('hour, SUM(total_queued) as queued, SUM(total_submitted) as submitted, SUM(total_failed) as failed')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        $stats = [];
        for ($h = 0; $h < 24; $h++) {
            $stats[$h] = [
                'queued' => (int) ($logs[$h]->queued ?? 0),
                'submitted' => (int) ($logs[$h]->submitted ?? 0),
                'failed' => (int) ($logs[$h]->failed ?? 0),
            ];
        }

        return response()->json($stats);
    }
}
