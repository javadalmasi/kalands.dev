<?php

namespace App\Http\Controllers;

use App\Repositories\SettingsRepository;
use App\Services\ActivityLogger;
use App\Services\InternalAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InternalAnalyticsController extends Controller
{
    public function collect(Request $request, InternalAnalyticsService $analytics): JsonResponse
    {
        if ($request->has('events')) {
            $data = $request->validate([
                'events' => ['required', 'array', 'max:10'],
                'events.*.event_type' => ['required', 'string', 'in:pageview,goal,error'],
                'events.*.visitor_id' => ['nullable', 'string', 'max:80'],
                'events.*.goal_key' => ['nullable', 'string', 'max:80'],
                'events.*.product_id' => ['nullable', 'string', 'max:120'],
                'events.*.search_term' => ['nullable', 'string', 'max:190'],
                'events.*.url' => ['nullable', 'string', 'max:1000'],
                'events.*.path' => ['nullable', 'string', 'max:500'],
                'events.*.title' => ['nullable', 'string', 'max:500'],
                'events.*.referrer' => ['nullable', 'string', 'max:1000'],
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
            'event_type' => ['required', 'string', 'in:pageview,goal,error'],
            'visitor_id' => ['nullable', 'string', 'max:80'],
            'goal_key' => ['nullable', 'string', 'max:80'],
            'product_id' => ['nullable', 'string', 'max:120'],
            'search_term' => ['nullable', 'string', 'max:190'],
            'url' => ['nullable', 'string', 'max:1000'],
            'path' => ['nullable', 'string', 'max:500'],
            'title' => ['nullable', 'string', 'max:500'],
            'referrer' => ['nullable', 'string', 'max:1000'],
            'error_message' => ['nullable', 'string', 'max:1000'],
            'error_source' => ['nullable', 'string', 'max:500'],
            'error_line' => ['nullable', 'integer', 'min:0'],
        ]);

        $tracked = $analytics->track($request, $data);

        return response()->json(['ok' => true, 'tracked' => $tracked]);
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
        ]);
        $section = (string) ($data['section'] ?? 'overview');

        return response()->json([
            'ok' => true,
            'data' => $analytics->report($section, $data),
        ]);
    }

    public function dashboard(InternalAnalyticsService $analytics): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $analytics->dashboard(),
        ]);
    }

    public function userActivity(int $userId, InternalAnalyticsService $analytics): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'data' => $analytics->userActivity($userId),
        ]);
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
            'report_cache_seconds' => ['required', 'integer', 'min:10', 'max:3600'],
            'live_user_window_minutes' => ['required', 'integer', 'min:1', 'max:60'],
            'raw_event_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'stats_retention_days' => ['required', 'integer', 'min:30', 'max:1825'],
        ]);

        $settings = array_merge($analytics->defaultSettings(), $data, [
            'enabled' => $request->boolean('enabled'),
            'tracking_script_enabled' => $request->boolean('tracking_script_enabled'),
            'error_tracking_enabled' => $request->boolean('error_tracking_enabled'),
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
            'type' => ['required', 'string', 'in:errors,all'],
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
