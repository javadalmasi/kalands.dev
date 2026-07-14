<?php

return [

    /*
    |--------------------------------------------------------------------------
    | URLs per sub-sitemap
    |--------------------------------------------------------------------------
    |
    | Each product sub-sitemap (/product-sitemap{n}.xml) holds up to this many
    | URLs. The sitemaps protocol allows up to 50,000; a smaller number keeps
    | each file light for crawlers.
    |
    */

    'urls_per_shard' => (int) env('SITEMAP_URLS_PER_SHARD', 10_000),

    /*
    |--------------------------------------------------------------------------
    | Products planned per build pass
    |--------------------------------------------------------------------------
    |
    | A build only records shard boundaries (not XML), so passes are cheap. Each
    | pass plans this many products before re-dispatching, keeping every pass
    | under the queue timeout on very large catalogs.
    |
    */

    'products_per_pass' => (int) env('SITEMAP_PRODUCTS_PER_PASS', 100_000),

    /*
    |--------------------------------------------------------------------------
    | Rendered-XML cache TTL (seconds)
    |--------------------------------------------------------------------------
    |
    | The index and each sub-sitemap are rendered on demand and cached for this
    | long. A rebuild flips the generation pointer, which invalidates the cache
    | immediately regardless of TTL. Default: 6 hours.
    |
    */

    'cache_ttl' => (int) env('SITEMAP_CACHE_TTL', 21_600),

    /*
    |--------------------------------------------------------------------------
    | Cache store
    |--------------------------------------------------------------------------
    |
    | The module keeps its rendered XML (and run locks) in a dedicated store so
    | a `redis FLUSHALL` never wipes the sitemap. The file store persists on
    | disk and survives cache flushes; the shard metadata itself already lives
    | in the database, so a build never needs to re-run — only re-render.
    |
    */

    'cache_store' => env('SITEMAP_CACHE_STORE', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Automatic rebuild interval (hours)
    |--------------------------------------------------------------------------
    |
    | In auto mode, the scheduled command re-plans the sitemap when the last
    | build is older than this. Rebuilds are cheap (metadata only).
    |
    */

    'rebuild_interval_hours' => (int) env('SITEMAP_REBUILD_INTERVAL_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Job timeout / tries
    |--------------------------------------------------------------------------
    */

    'job_timeout' => 280,
    'job_tries' => 3,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Build jobs run on the shared module queue so both the scheduled worker and
    | the on-demand queue webservice drain them.
    |
    */

    'queue' => env('SITEMAP_QUEUE', 'default'),

];
