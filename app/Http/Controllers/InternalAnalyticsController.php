<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsAlert;
use App\Repositories\SettingsRepository;
use App\Services\ActivityLogger;
use App\Services\InternalAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternalAnalyticsController extends Controller
{
    public function collect(Request $request, InternalAnalyticsService $analytics): JsonResponse
    {
        if ($request->has('pixel')) {
            return $this->pixelCollect($request, $analytics);
        }

        if ($request->has('events')) {
            $data = $request->validate([
                'events' => ['required', 'array', 'max:10'],
                'events.*.event_type' => ['required', 'string', 'in:pageview,goal,error,heartbeat'],
                'events.*.visitor_id' => ['nullable', 'string', 'max:80'],
                'events.*.goal_key' => ['nullable', 'string', 'max:80'],
                'events.*.funnel_key' => ['nullable', 'string', 'max:80'],
                'events.*.funnel_step' => ['nullable', 'integer', 'min:0', 'max:20'],
                'events.*.funnel_step_name' => ['nullable', 'string', 'max:120'],
                'events.*.product_id' => ['nullable', 'string', 'max:120'],
                'events.*.search_term' => ['nullable', 'string', 'max:190'],
                'events.*.url' => ['nullable', 'string', 'max:1000'],
                'events.*.path' => ['nullable', 'string', 'max:500'],
                'events.*.title' => ['nullable', 'string', 'max:500'],
                'events.*.referrer' => ['nullable', 'string', 'max:1000'],
                'events.*.scroll_depth' => ['nullable', 'integer', 'min:0', 'max:100'],
                'events.*.session_duration' => ['nullable', 'integer', 'min:0'],
                'events.*.session_end' => ['nullable', 'boolean'],
                'events.*.error_message' => ['nullable', 'string', 'max:1000'],
                'events.*.error_source' => ['nullable', 'string', 'max:500'],
                'events.*.error_line' => ['nullable', 'integer', 'min:0'],
            ]);

            $tracked = 0;
            foreach ($data['events'] as $event) {
                $tracked += $analytics->track($request, $event) ? 1 : 0;
            }

            return response()->json(['ok' => true, 'tracked' => $tracked]);
        }

        $data = $request->validate([
            'event_type' => ['required', 'string', 'in:pageview,goal,error,heartbeat'],
            'visitor_id' => ['nullable', 'string', 'max:80'],
            'goal_key' => ['nullable', 'string', 'max:80'],
            'funnel_key' => ['nullable', 'string', 'max:80'],
            'funnel_step' => ['nullable', 'integer', 'min:0', 'max:20'],
            'funnel_step_name' => ['nullable', 'string', 'max:120'],
            'product_id' => ['nullable', 'string', 'max:120'],
            'search_term' => ['nullable', 'string', 'max:190'],
            'url' => ['nullable', 'string', 'max:1000'],
            'path' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:1000'],
            'scroll_depth' => ['nullable', 'integer', 'min:0', 'max:100'],
            'session_duration' => ['nullable', 'integer', 'min:0'],
            'session_end' => ['nullable', 'boolean'],
            'error_message' => ['nullable', 'string', 'max:1000'],
            'error_source' => ['nullable', 'string', 'max:500'],
            'error_line' => ['nullable', 'integer', 'min:0'],
        ]);

        $tracked = $analytics->track($request, $data);

        return response()->json(['ok' => true, 'tracked' => $tracked]);
    }

    private function pixelCollect(Request $request, InternalAnalyticsService $analytics): Response
    {
        if ($request->has('data')) {
            $payload = json_decode((string) $request->input('data'), true);
            if (is_array($payload)) {
                $analytics->track($request, $payload);
            }
        }

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==');

        return response($gif, 200)
            ->header('Content-Type', 'image/gif')
            ->header('Content-Length', (string) strlen($gif))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function report(Request $request, InternalAnalyticsService $analytics): JsonResponse
    {
        $data = $request->validate([
            'section' => ['nullable', 'string', 'max:40'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
            'period' => ['nullable', 'string', 'in:day,week,month'],
            'country' => ['nullable', 'string', 'max:8'],
            'device_type' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'activity' => ['nullable', 'string', 'in:pageview,goal,error'],
            'search' => ['nullable', 'string', 'max:190'],
            'path' => ['nullable', 'string', 'max:500'],
            'goal_key' => ['nullable', 'string', 'max:80'],
            'source' => ['nullable', 'string', 'max:120'],
            'browser' => ['nullable', 'string', 'max:120'],
            'platform' => ['nullable', 'string', 'max:120'],
            'campaign' => ['nullable', 'string', 'max:120'],
            'session_status' => ['nullable', 'string', 'in:new,returning,bounce,long'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $section = (string) ($data['section'] ?? 'overview');

        return response()->json([
            'ok' => true,
            'data' => $analytics->report($section, $data),
        ]);
    }

    public function liveCount(InternalAnalyticsService $analytics): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => ['count' => $analytics->liveUsersCount()],
        ]);
    }

    public function dashboard(InternalAnalyticsService $analytics): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $analytics->dashboard(),
        ]);
    }

    public function userActivity(Request $request, int $userId, InternalAnalyticsService $analytics): JsonResponse
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'event_type' => ['nullable', 'string', 'in:pageview,goal,error'],
            'search' => ['nullable', 'string', 'max:190'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:10', 'max:500'],
        ]);

        return response()->json([
            'ok' => true,
            'data' => $analytics->userActivity($userId, $filters),
        ]);
    }

    public function userJourney(Request $request, InternalAnalyticsService $analytics): JsonResponse
    {
        $data = $request->validate([
            'session_id' => ['required', 'string', 'max:80'],
        ]);

        return response()->json([
            'ok' => true,
            'data' => $analytics->userJourney($data['session_id']),
        ]);
    }

    public function saveAlert(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'metric' => ['required', 'string', 'max:60'],
            'condition' => ['required', 'string', 'in:gt,lt,gte,lte,eq,pct_change_gt,pct_change_lt'],
            'threshold' => ['required', 'numeric'],
            'cooldown_hours' => ['nullable', 'integer', 'min:1', 'max:720'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        AnalyticsAlert::query()->create($data);

        $activityLogger->log('analytics.alert.save', auth('admin')->user(), "هشدار {$data['name']} ایجاد شد");

        return back()->with('message', 'هشدار با موفقیت ایجاد شد.');
    }

    public function deleteAlert(int $id, ActivityLogger $activityLogger): RedirectResponse
    {
        AnalyticsAlert::query()->where('id', $id)->delete();
        $activityLogger->log('analytics.alert.delete', auth('admin')->user(), "هشدار #{$id} حذف شد");

        return back()->with('message', 'هشدار حذف شد.');
    }

    public function csvExport(Request $request, InternalAnalyticsService $analytics): StreamedResponse
    {
        $data = $request->validate([
            'section' => ['required', 'string', 'in:overview,reports,content,search,goals'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'period' => ['nullable', 'string', 'in:day,week,month'],
            'country' => ['nullable', 'string', 'max:8'],
            'device_type' => ['nullable', 'string', 'in:desktop,mobile,tablet'],
            'activity' => ['nullable', 'string', 'in:pageview,goal,error'],
        ]);

        $csv = $analytics->csvExport($data['section'], $data);
        $fileName = 'analytics-' . $data['section'] . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, $fileName, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public function saveSettings(
        Request $request,
        SettingsRepository $settingsRepository,
        ActivityLogger $activityLogger,
        InternalAnalyticsService $analytics
    ): RedirectResponse {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'tracking_script_enabled' => ['nullable', 'boolean'],
            'error_tracking_enabled' => ['nullable', 'boolean'],
            'heartbeat_enabled' => ['nullable', 'boolean'],
            'alerting_enabled' => ['nullable', 'boolean'],
            'report_cache_seconds' => ['required', 'integer', 'min:10', 'max:3600'],
            'live_user_window_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'raw_event_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'stats_retention_days' => ['required', 'integer', 'min:30', 'max:1825'],
            'session_timeout_minutes' => ['nullable', 'integer', 'min:5', 'max:120'],
        ]);

        $settings = array_merge($analytics->defaultSettings(), $data, [
            'enabled' => $request->boolean('enabled'),
            'tracking_script_enabled' => $request->boolean('tracking_script_enabled'),
            'error_tracking_enabled' => $request->boolean('error_tracking_enabled'),
            'heartbeat_enabled' => $request->boolean('heartbeat_enabled'),
            'alerting_enabled' => $request->boolean('alerting_enabled'),
        ]);

        $settingsRepository->set('analytics.settings', $settings);
        $activityLogger->log('settings.analytics.update', auth('admin')->user(), 'بروزرسانی تنظیمات آنالیزور', $settings);

        return back()->with('message', 'تنظیمات آنالیزور ذخیره شد.');
    }

    public function export(InternalAnalyticsService $analytics): StreamedResponse
    {
        $fileName = 'analytics-' . now()->format('Ymd-His') . '.json';

        return response()->streamDownload(function () use ($analytics) {
            echo json_encode($analytics->exportData(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    public function import(Request $request, InternalAnalyticsService $analytics, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'analytics_file' => ['required', 'file', 'mimes:json,txt', 'max:5120'],
        ]);

        $payload = json_decode(file_get_contents($data['analytics_file']->getRealPath()), true);
        if (!is_array($payload)) {
            return back()->withErrors(['analytics_file' => 'فایل وارد شده JSON معتبر نیست.']);
        }

        $imported = $analytics->importData($payload);
        $activityLogger->log('analytics.import', auth('admin')->user(), 'درون‌ریزی آمار آنالیزور', ['rows' => $imported]);

        return back()->with('message', "{$imported} ردیف آماری وارد شد.");
    }

    public function prune(Request $request, InternalAnalyticsService $analytics, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:errors,sessions,all'],
            'older_than' => ['nullable', 'string', 'in:all,30,90,180,365'],
        ]);

        $olderThanDays = $data['older_than'] === 'all' ? null : (int) $data['older_than'];
        $count = $analytics->prune($data['type'], $olderThanDays);

        $activityLogger->log('analytics.prune', auth('admin')->user(), 'پاکسازی داده‌های آنالیزور', [
            'type' => $data['type'],
            'older_than_days' => $olderThanDays,
            'count' => $count
        ]);

        return back()->with('message', "تعداد {$count} ردیف داده با موفقیت حذف شد.");
    }
}
