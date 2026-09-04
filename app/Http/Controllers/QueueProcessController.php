<?php

namespace App\Http\Controllers;

use App\Models\QueueExecutionLog;
use App\Repositories\SettingsRepository;
use App\Services\GeoIPService;
use App\Services\IndexNowService;
use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class QueueProcessController extends Controller
{
    public function process(
        SettingsRepository $settingsRepository,
        GeoIPService $geoIPService,
        SitemapGenerationService $sitemapGenerationService,
        IndexNowService $indexNowService,
    ): JsonResponse {
        $queueSettings = $settingsRepository->get('queue.settings', []);

        if (! ($queueSettings['webservice_enabled'] ?? true)) {
            return response()->json(['message' => 'وبسرویس صف غیرفعال شده است.'], 403);
        }

        $expectedToken = $queueSettings['cron_token'] ?? null;
        $providedToken = request()->header('X-Queue-Token');

        if (! $expectedToken || ! $providedToken || ! hash_equals((string) $expectedToken, (string) $providedToken)) {
            QueueExecutionLog::query()->create([
                'executed_at' => now(),
                'status' => 'failed',
                'error_message' => 'Invalid queue token',
            ]);

            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $meta = [
            'mode' => $queueSettings['mode'] ?? 'cron',
            'tasks' => [],
        ];

        try {
            $refreshedCounts = $sitemapGenerationService->refreshCountsIfDue(10);
            if ($refreshedCounts) {
                $meta['tasks'][] = 'بروزرسانی شمار محصولات سایت‌مپ (هر ۱۰ دقیقه) انجام شد.';
            }

            if ($run = $sitemapGenerationService->startAuto()) {
                $type = $run->mode === 'full' ? 'بازسازی کامل دوره‌ای' : 'اجرای افزایشی';
                $meta['tasks'][] = "شروع خودکار تولید سایت مپ ({$type}): اجرای {$run->run_id} در صف قرار گرفت.";
            }

            if ($indexNowService->dispatchContinuousIfDue()) {
                $meta['tasks'][] = 'شروع خودکار IndexNow برای ساعت جاری در صف قرار گرفت.';
            }

            // 1. Process Queue Jobs
            $initialJobsCount = DB::table('jobs')->count();
            $sitemapQueue = (string) config('queue.sitemap_queue', 'default');
            $queueOrder = implode(',', array_values(array_unique([$sitemapQueue, 'default'])));
            Artisan::call('queue:work', [
                '--queue' => $queueOrder,
                '--stop-when-empty' => true,
                '--tries' => 3,
                '--timeout' => 300,
            ]);
            $finalJobsCount = DB::table('jobs')->count();
            $processedJobs = max(0, $initialJobsCount - $finalJobsCount);

            $meta['tasks'][] = "پردازش صف: {$processedJobs} مورد انجام شد.";
            $meta['processed_jobs_count'] = $processedJobs;

            // 3. Cleanup Queue Execution Logs
            $retentionQueue = (int) ($queueSettings['queue_log_retention_days'] ?? 7);
            $deletedQueueLogs = QueueExecutionLog::query()
                ->where('executed_at', '<', now()->subDays($retentionQueue))
                ->delete();
            if ($deletedQueueLogs > 0) {
                $meta['tasks'][] = "پاکسازی لاگ‌های صف: {$deletedQueueLogs} مورد قدیمی حذف شدند.";
            }

            // 4. Cleanup Laravel Logs
            $retentionLaravel = (int) ($queueSettings['laravel_log_retention_days'] ?? 14);
            $deletedLaravelFiles = $this->cleanupLaravelLogs($retentionLaravel);
            if ($deletedLaravelFiles > 0) {
                $meta['tasks'][] = "پاکسازی لاگ‌های لاراول: {$deletedLaravelFiles} فایل قدیمی حذف شدند.";
            }

            // 5. GeoIP Database Update (Every 5 hours)
            $lastGeoUpdate = $settingsRepository->get('geoip.last_run');
            if (! $lastGeoUpdate || now()->diffInHours($lastGeoUpdate, true) >= 5) {
                $geoResult = $geoIPService->updateDatabases();
                if ($geoResult['success']) {
                    $meta['tasks'][] = 'بروزرسانی خودکار دیتابیس GeoIP با موفقیت انجام شد.';
                } else {
                    $meta['tasks'][] = 'بروزرسانی خودکار دیتابیس GeoIP با خطا مواجه شد (جزئیات در ماژول GeoIP).';
                }
            }

            QueueExecutionLog::query()->create([
                'executed_at' => now(),
                'status' => 'success',
                'meta' => $meta,
            ]);

            return response()->json(['message' => 'Queue processed', 'details' => $meta]);
        } catch (\Throwable $exception) {
            QueueExecutionLog::query()->create([
                'executed_at' => now(),
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'meta' => $meta,
            ]);

            return response()->json(['message' => 'خطای سیستمی در پردازش صف رخ داد.'], 500);
        }
    }

    private function cleanupLaravelLogs(int $days): int
    {
        $logPath = storage_path('logs');
        if (! File::isDirectory($logPath)) {
            return 0;
        }

        $files = File::files($logPath);
        $deletedCount = 0;
        $threshold = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if ($file->getExtension() === 'log' && $file->getMTime() < $threshold) {
                File::delete($file->getPathname());
                $deletedCount++;
            }
        }

        return $deletedCount;
    }
}
