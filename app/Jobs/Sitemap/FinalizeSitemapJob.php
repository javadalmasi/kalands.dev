<?php

namespace App\Jobs\Sitemap;

use App\Models\SitemapRunLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FinalizeSitemapJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 120;

    public function __construct(
        protected string $runId,
        protected bool $force = false,
        protected bool $separateStores = false,
    ) {}

    public function handle(): void
    {
        $files = glob(public_path('sitemaps/sitemap-*.xml.gz'));

        if (empty($files)) {
            $this->info('No sitemap files found. Nothing to finalize.');

            $this->markCompleted();

            return;
        }

        sort($files);

        if ($this->separateStores) {
            $this->generateSeparateIndexes($files);
        } else {
            $this->generateSingleIndex($files);
        }

        $this->markCompleted();

        $this->info('Sitemap index generated with '.count($files).' sitemaps.');
    }

    private function generateSingleIndex(array $files): void
    {
        $appUrl = rtrim(config('app.url'), '/');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($files as $file) {
            $filename = basename($file);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime($file));
            $loc = "{$appUrl}/sitemaps/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function generateSeparateIndexes(array $files): void
    {
        $appUrl = rtrim(config('app.url'), '/');

        $dkFiles = [];
        $bsFiles = [];
        $otherFiles = [];

        foreach ($files as $file) {
            $basename = basename($file);
            if (preg_match('/-dk-\d+\.xml\.gz$/', $basename)) {
                $dkFiles[] = $file;
            } elseif (preg_match('/-bs-\d+\.xml\.gz$/', $basename)) {
                $bsFiles[] = $file;
            } else {
                $otherFiles[] = $file;
            }
        }

        $subIndexes = [];

        if (!empty($dkFiles)) {
            $dkPath = public_path('sitemap-digikala.xml');
            $this->writeSubIndex($dkFiles, $dkPath, $appUrl);
            $subIndexes[] = $dkPath;
            $this->info('Generated sitemap-digikala.xml with '.count($dkFiles).' sitemaps.');
        }

        if (!empty($bsFiles)) {
            $bsPath = public_path('sitemap-basalam.xml');
            $this->writeSubIndex($bsFiles, $bsPath, $appUrl);
            $subIndexes[] = $bsPath;
            $this->info('Generated sitemap-basalam.xml with '.count($bsFiles).' sitemaps.');
        }

        if (!empty($otherFiles)) {
            $otherPath = public_path('sitemap-other.xml');
            $this->writeSubIndex($otherFiles, $otherPath, $appUrl);
            $subIndexes[] = $otherPath;
            $this->info('Generated sitemap-other.xml with '.count($otherFiles).' sitemaps.');
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($subIndexes as $indexFile) {
            $filename = basename($indexFile);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime($indexFile));
            $loc = "{$appUrl}/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        file_put_contents(public_path('sitemap.xml'), $xml);
    }

    private function writeSubIndex(array $files, string $outputPath, string $appUrl): void
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($files as $file) {
            $filename = basename($file);
            $lastmod = gmdate('Y-m-d\TH:i:sP', filemtime($file));
            $loc = "{$appUrl}/sitemaps/{$filename}";

            $xml .= '<sitemap>';
            $xml .= '<loc>'.htmlspecialchars($loc, ENT_XML1 | ENT_QUOTES, 'UTF-8').'</loc>';
            $xml .= '<lastmod>'.$lastmod.'</lastmod>';
            $xml .= '</sitemap>';
        }

        $xml .= '</sitemapindex>';

        file_put_contents($outputPath, $xml);
    }

    private function markCompleted(): void
    {
        SitemapRunLog::query()
            ->where('run_id', $this->runId)
            ->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

        Cache::forget('sitemap:running');
    }

    private function info(string $message): void
    {
        Log::info("SitemapGenerator: {$message}");
    }
}
