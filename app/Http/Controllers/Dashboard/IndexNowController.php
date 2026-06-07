<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
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
        $sharedKey = $this->indexNowService->getSharedVerificationKey();

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

        $nowTehran = now();
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

        $engineStats = [];
        foreach ($engines as $engine) {
            $aggregates = IndexNowRunLog::query()
                ->where('engine', $engine)
                ->where('created_at', '>=', now()->subDays(7))
                ->selectRaw('SUM(total_queued) as queued, SUM(total_submitted) as submitted, SUM(total_failed) as failed')
                ->first();

            $engineStats[$engine] = [
                'queued' => (int) ($aggregates?->queued ?? 0),
                'submitted' => (int) ($aggregates?->submitted ?? 0),
                'failed' => (int) ($aggregates?->failed ?? 0),
            ];
        }

        return view('dash.admin.indexnow-hub', compact(
            'weights',
            'enabled',
            'keys',
            'sharedKey',
            'dailyLimit',
            'todaySubmitted',
            'lastRuns',
            'totalActive',
            'pendingProducts',
            'currentHour',
            'nowTehran',
            'estimatedDaily',
            'engineStats',
        ));
    }

    public function saveSettings(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $engines = ['bing', 'yandex'];

        $request->validate([
            'daily_limit' => 'required|integer|min:100|max:1000000',
            'bing_enabled' => 'nullable|boolean',
            'yandex_enabled' => 'nullable|boolean',
            'shared_key' => 'nullable|string|max:255',
            'bing_weights' => 'nullable',
            'yandex_weights' => 'nullable',
        ]);

        $this->indexNowService->setDailyLimit((int) $request->input('daily_limit'));

        $newSharedKey = (string) $request->input('shared_key', '');
        $oldSharedKey = $this->indexNowService->getSharedVerificationKey();

        if ($newSharedKey !== $oldSharedKey) {
            if (!empty($oldSharedKey)) {
                $oldPath = public_path("{$oldSharedKey}.txt");
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $this->indexNowService->setSharedVerificationKey($newSharedKey);
        }

        if ($newSharedKey !== '') {
            $this->indexNowService->generateVerificationFile();
        }

        foreach ($engines as $engine) {
            $enabled = (bool) $request->input("{$engine}_enabled", false);
            $this->indexNowService->setEnabled($engine, $enabled);

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
        $oldKey = $this->indexNowService->getSharedVerificationKey();
        if (!empty($oldKey)) {
            $oldPath = public_path("{$oldKey}.txt");
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $newKey = strtolower(Str::random(32));
        $this->indexNowService->setSharedVerificationKey($newKey);
        $this->indexNowService->generateVerificationFile();

        $activityLogger->log(
            'settings.indexnow.key.shared',
            auth('admin')->user(),
            'تولید کلید وریفای مشترک جدید برای بینگ و یاندکس',
        );

        return response()->json(['key' => $newKey]);
    }

}
