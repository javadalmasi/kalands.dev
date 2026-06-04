<?php

namespace App\Console\Commands;

use App\Jobs\Sitemap\ProcessSitemapChunkJob;
use App\Models\Product;
use App\Models\SitemapRunLog;
use App\Repositories\SettingsRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class GenerateSitemapCommand extends Command
{
    protected $signature = 'sitemap:generate {--force : Force full regeneration from scratch}';

    protected $description = 'Generate product sitemaps incrementally using the queue';

    public function handle(): int
    {
        if (Cache::get('sitemap:running')) {
            $this->warn('Sitemap generation is already in progress.');

            return Command::SUCCESS;
        }

        if (!$this->isInScheduleWindow()) {
            $this->info('Outside scheduled window. Skipping.');

            return Command::SUCCESS;
        }

        $settings = app(SettingsRepository::class);
        $separateStores = (bool) $settings->get('sitemap.separate_stores', false);

        $runId = now()->format('Ymd_His');
        $force = $this->option('force');

        SitemapRunLog::query()->create([
            'run_id' => $runId,
            'status' => 'running',
            'force_mode' => $force,
            'started_at' => now(),
            'total_products' => Product::query()->where('is_active', true)->count(),
        ]);

        Cache::put('sitemap:running', true, 86400);

        if ($force) {
            $this->info('Force mode: will regenerate all sitemaps.');
        }

        if ($separateStores) {
            $this->info('Separate stores mode: Digikala first, then Basalam.');
        }

        ProcessSitemapChunkJob::dispatch(
            $runId,
            lastId: null,
            force: $force,
            store: $separateStores ? 'dk' : '',
            separateStores: $separateStores,
        );

        $this->info("Sitemap generation initiated (run: {$runId})");

        return Command::SUCCESS;
    }

    private function isInScheduleWindow(): bool
    {
        $settings = app(SettingsRepository::class);
        $enabled = (bool) $settings->get('sitemap.schedule_enabled', true);

        if (!$enabled) {
            return true;
        }

        $start = (int) $settings->get('sitemap.schedule_start', 1);
        $end = (int) $settings->get('sitemap.schedule_end', 5);
        $hour = (int) now()->timezone('Asia/Tehran')->format('G');

        if ($start <= $end) {
            return $hour >= $start && $hour < $end;
        }

        return $hour >= $start || $hour < $end;
    }
}
