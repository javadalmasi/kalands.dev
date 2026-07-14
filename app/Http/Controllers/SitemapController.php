<?php

namespace App\Http\Controllers;

use App\Services\Sitemap\SitemapGenerationService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Serves the dynamic sitemap: the index and each product sub-sitemap are
 * rendered on demand (and cached) from shard metadata.
 */
class SitemapController extends Controller
{
    public function __construct(
        private readonly SitemapGenerationService $service,
    ) {}

    /**
     * GET /sitemap.xml — the sitemap index.
     */
    public function index(): Response
    {
        if (! $this->service->hasSitemap()) {
            return $this->notFound();
        }

        return $this->xml($this->service->renderIndex());
    }

    /**
     * GET /product-sitemap{shard}.xml — one product sub-sitemap.
     */
    public function shard(int $shard): Response
    {
        $xml = $this->service->renderShard($shard);

        if ($xml === null) {
            return $this->notFound();
        }

        return $this->xml($xml);
    }

    private function xml(string $body): Response
    {
        return response($body, SymfonyResponse::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    private function notFound(): Response
    {
        return response('Sitemap not available.', SymfonyResponse::HTTP_NOT_FOUND, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
