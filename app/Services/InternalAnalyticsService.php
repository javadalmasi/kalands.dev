<?php

namespace App\Services;

use App\Models\AnalyticsDailyStat;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsLiveVisitor;
use App\Models\Product;
use App\Models\User;
use App\Repositories\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InternalAnalyticsService
{
    private const CACHE_PREFIX = 'internal_analytics:';

    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly GeoIPService $geoIPService,
    ) {
    }

    public function defaultSettings(): array
    {
        return [
            'enabled' => true,
            'tracking_script_enabled' => true,
            'error_tracking_enabled' => true,
            'report_cache_seconds' => 60,
            'live_user_window_minutes' => 5,
            'raw_event_retention_days' => 90,
            'stats_retention_days' => 365,
        ];
    }

    public function settings(): array
    {
        return array_merge(
            $this->defaultSettings(),
            (array) $this->settings->get('analytics.settings', [])
        );
    }

    public function track(Request $request, array $payload): bool
    {
        $settings = $this->settings();
        if (!($settings['enabled'] ?? true) || $this->isLikelyBot((string) $request->userAgent())) {
            return false;
        }

        $eventType = (string) ($payload['event_type'] ?? 'pageview');
        if (!in_array($eventType, ['pageview', 'goal', 'error'], true)) {
            return false;
        }

        if ($eventType === 'error' && !($settings['error_tracking_enabled'] ?? true)) {
            return false;
        }

        $url = $this->limit((string) ($payload['url'] ?? url()->current()), 1000);
        $path = $this->normalisePath((string) ($payload['path'] ?? parse_url($url, PHP_URL_PATH) ?? '/'));
        $referrerUrl = $this->limit((string) ($payload['referrer'] ?? ''), 1000) ?: null;
        $sessionId = $this->normaliseSessionId((string) ($payload['visitor_id'] ?? ''));
        $userAgent = $this->limit((string) $request->userAgent(), 500);
        $ip = (string) $request->ip();
        $location = $this->geoIPService->lookupCountry($ip);
        $pathContext = $this->extractPathContext($path, $url);
        $goal = $this->normaliseGoal((string) ($payload['goal_key'] ?? ''));
        $error = $this->normaliseError($payload);
        $utm = $this->extractUtm($url);

        $event = AnalyticsEvent::query()->create([
            'session_id' => $sessionId,
            'visitor_hash' => $this->hash($sessionId),
            'user_id' => auth('web')->id(),
            'event_type' => $eventType,
            'goal_key' => $eventType === 'goal' ? $goal['key'] : null,
            'goal_label' => $eventType === 'goal' ? $goal['label'] : null,
            'product_id' => $this->limit((string) ($payload['product_id'] ?? $pathContext['product_id'] ?? ''), 120) ?: null,
            'category_key' => $pathContext['category_key'],
            'seller_id' => $pathContext['seller_id'],
            'search_term' => $this->limit((string) ($payload['search_term'] ?? $pathContext['search_term'] ?? ''), 190) ?: null,
            'utm_source' => $utm['utm_source'],
            'utm_medium' => $utm['utm_medium'],
            'utm_campaign' => $utm['utm_campaign'],
            'utm_term' => $utm['utm_term'],
            'utm_content' => $utm['utm_content'],
            'search_engine' => $this->extractSearchEngine($referrerUrl),
            'url' => $url,
            'path' => $path,
            'title' => $this->limit((string) ($payload['title'] ?? ''), 500) ?: null,
            'referrer_url' => $referrerUrl,
            'referrer_host' => $referrerUrl ? $this->limit((string) parse_url($referrerUrl, PHP_URL_HOST), 190) : null,
            'referrer_type' => $this->referrerType($referrerUrl, $request->getHost()),
            'country_code' => $location['country_code'] ?? null,
            'country_name' => $location['country_name'] ?? null,
            'device_type' => $this->deviceType($userAgent),
            'device_brand' => $this->deviceBrand($userAgent),
            'browser' => $this->browser($userAgent),
            'platform' => $this->platform($userAgent),
            'ip_address' => $this->limit($ip, 45),
            'ip_hash' => $this->hash($ip),
            'user_agent' => $userAgent,
            'user_agent_hash' => $this->hash($userAgent),
            'error_message' => $eventType === 'error' ? $error['message'] : null,
            'error_source' => $eventType === 'error' ? $error['source'] : null,
            'error_line' => $eventType === 'error' ? $error['line'] : null,
            'occurred_at' => now(),
        ]);

        if ($event->event_type === 'pageview') {
            $this->touchLiveVisitor($event);
        }

        return true;
    }

    public function aggregatePendingEvents(int $limit = 2000): int
    {
        $events = AnalyticsEvent::query()
            ->whereNull('processed_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($events->isEmpty()) {
            $this->cleanup();
            return 0;
        }

        $increments = [];
        $eventIds = [];

        foreach ($events as $event) {
            $eventIds[] = $event->id;
            $date = $event->occurred_at->toDateString();

            if ($event->event_type === 'pageview') {
                $this->addIncrement($increments, $date, 'pageview', '__all__', 'همه بازدیدها');
                $this->addIncrement($increments, $date, 'page', $event->path ?: '/', $event->title ?: $event->path ?: '/');

                if ($event->country_code) {
                    $this->addIncrement($increments, $date, 'country', $event->country_code, $event->country_name ?: $event->country_code);
                }

                if ($event->referrer_type) {
                    $this->addIncrement($increments, $date, 'referrer', $event->referrer_type, $this->referrerLabel($event->referrer_type));
                }

                if ($event->product_id) {
                    $this->addIncrement($increments, $date, 'product', $event->product_id, $this->productLabel($event->product_id));
                }

                if ($event->category_key) {
                    $this->addIncrement($increments, $date, 'category', $event->category_key, $event->category_key);
                }

                if ($event->seller_id) {
                    $this->addIncrement($increments, $date, 'seller', $event->seller_id, $event->seller_id);
                }

                if ($event->search_term) {
                    $this->addIncrement($increments, $date, 'search', $event->search_term, $event->search_term);
                }

                if ($event->device_brand) {
                    $this->addIncrement($increments, $date, 'device_brand', $event->device_brand, $event->device_brand);
                }

                if ($event->device_type) {
                    $this->addIncrement($increments, $date, 'device_type', $event->device_type, $this->deviceTypeLabel($event->device_type));
                }

                if ($event->browser) {
                    $this->addIncrement($increments, $date, 'browser', $event->browser, $event->browser);
                }

                if ($event->platform) {
                    $this->addIncrement($increments, $date, 'platform', $event->platform, $event->platform);
                }

                if ($event->user_id) {
                    $this->addIncrement($increments, $date, 'user', (string) $event->user_id, $this->userLabel((int) $event->user_id));
                }

                if ($event->utm_source) {
                    $this->addIncrement($increments, $date, 'utm_source', $event->utm_source, $event->utm_source);
                }

                if ($event->utm_medium) {
                    $this->addIncrement($increments, $date, 'utm_medium', $event->utm_medium, $event->utm_medium);
                }

                if ($event->utm_campaign) {
                    $this->addIncrement($increments, $date, 'utm_campaign', $event->utm_campaign, $event->utm_campaign);
                }

                if ($event->search_engine) {
                    $this->addIncrement($increments, $date, 'search_engine', $event->search_engine, $event->search_engine);
                }
            }

            $this->addIncrement($increments, $date, 'activity', $event->event_type, $this->activityLabel($event->event_type));

            if ($event->event_type === 'goal' && $event->goal_key) {
                $this->addIncrement($increments, $date, 'goal', $event->goal_key, $event->goal_label ?: $event->goal_key);
            }

            if ($event->event_type === 'error') {
                $this->addIncrement($increments, $date, 'error', '__all__', 'همه خطاها');
                $this->addIncrement($increments, $date, 'error', $event->path ?: '/', $event->error_message ?: $event->path ?: 'خطای ناشناس');
            }
        }

        foreach ($increments as $item) {
            $stat = AnalyticsDailyStat::query()->firstOrNew([
                'date' => $item['date'],
                'metric' => $item['metric'],
                'dimension_key' => $item['dimension_key'],
            ]);

            $stat->dimension_label = $item['dimension_label'];
            $stat->count = (int) $stat->count + $item['count'];
            $stat->save();
        }

        AnalyticsEvent::query()
            ->whereIn('id', $eventIds)
            ->update(['processed_at' => now()]);

        $this->cleanup();
        Cache::put(self::CACHE_PREFIX . 'last_aggregated_at', now()->toDateTimeString(), 86400);

        return count($eventIds);
    }

    public function dashboard(): array
    {
        return $this->cachedReport('dashboard', 45, function () {
            return [
                'today' => $this->sumMetric('pageview', now(), now()),
                'chart' => $this->series('pageview', now()->subDays(29), now()),
                'goals' => $this->topMetric('goal', now(), now(), 6),
                'live' => $this->liveUsersCount(),
            ];
        });
    }

    public function userActivity(int $userId): array
    {
        $events = AnalyticsEvent::query()
            ->where('user_id', $userId)
            ->latest('occurred_at')
            ->limit(100)
            ->get();

        return [
            'user' => $this->userLabel($userId),
            'events' => $events->map(fn($e) => [
                'type' => $e->event_type,
                'path' => $e->path,
                'title' => $e->title,
                'time' => $e->occurred_at->diffForHumans(),
                'full_time' => $e->occurred_at->toDateTimeString(),
                'ip' => $e->ip_address,
                'country' => $e->country_name ?: $e->country_code,
                'device' => $e->device_brand . ' / ' . $e->device_type,
                'browser' => $e->browser,
                'search_engine' => $e->search_engine,
                'utm' => array_filter([
                    'source' => $e->utm_source,
                    'medium' => $e->utm_medium,
                    'campaign' => $e->utm_campaign,
                ]),
                'goal' => $e->goal_label,
            ])->all(),
        ];
    }

    public function report(string $section, array $filters = []): array
    {
        $filters = $this->normaliseReportFilters($filters);
        $start = $filters['start'];
        $end = $filters['end'];
        $cacheKey = $this->reportCacheKey($section, $filters);
        $cacheTtl = $this->hasRawFilters($filters) ? 15 : 60;

        return match ($section) {
            'overview' => $this->cachedReport('overview:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'today' => $this->filteredEventsCount($filters, 'pageview', now(), now()),
                'week' => $this->filteredEventsCount($filters, 'pageview', now()->subDays(6), now()),
                'month' => $this->filteredEventsCount($filters, 'pageview', now()->subDays(29), now()),
                'range_total' => $this->filteredEventsCount($filters, 'pageview', $start, $end),
                'live' => $this->liveUsersCount(),
                'chart' => $this->filteredSeries($filters, 'pageview'),
                'activity' => $this->eventTypeBreakdown($filters),
            ]),
            'reports' => $this->cachedReport('reports:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'countries' => $this->topEventsDimension($filters, 'country_code', 'country_name', 80),
                'referrers' => $this->topEventsDimension($filters, 'referrer_type', null, 12, fn ($value) => $this->referrerLabel((string) $value)),
                'device_types' => $this->topEventsDimension($filters, 'device_type', null, 12, fn ($value) => $this->deviceTypeLabel((string) $value)),
                'device_brands' => $this->topEventsDimension($filters, 'device_brand', null, 12),
                'browsers' => $this->topEventsDimension($filters, 'browser', null, 12),
                'platforms' => $this->topEventsDimension($filters, 'platform', null, 12),
                'utm_sources' => $this->topEventsDimension($filters, 'utm_source', null, 12),
                'utm_mediums' => $this->topEventsDimension($filters, 'utm_medium', null, 12),
                'utm_campaigns' => $this->topEventsDimension($filters, 'utm_campaign', null, 12),
                'search_engines' => $this->topEventsDimension($filters, 'search_engine', null, 12),
                'activities' => $this->eventTypeBreakdown($filters),
                'map' => $this->countryMap($this->topEventsDimension($filters, 'country_code', 'country_name', 250)),
            ]),
            'content' => $this->cachedReport('content:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'pages' => $this->topEventsDimension($filters, 'path', 'title', 20),
                'products' => $this->topEventsDimension($filters, 'product_id', null, 20, fn ($value) => $this->productLabel((string) $value)),
                'categories' => $this->topEventsDimension($filters, 'category_key', null, 20),
                'sellers' => $this->topEventsDimension($filters, 'seller_id', null, 20),
            ]),
            'search' => $this->cachedReport('search:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'keywords' => $this->topEventsDimension($filters, 'search_term', null, 50),
            ]),
            'goals' => $this->cachedReport('goals:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'today' => $this->topEventsDimension(array_merge($filters, ['start' => now()->startOfDay(), 'end' => now()->endOfDay()]), 'goal_key', 'goal_label', 20, null, 'goal'),
                'yesterday' => $this->topEventsDimension(array_merge($filters, ['start' => now()->subDay()->startOfDay(), 'end' => now()->subDay()->endOfDay()]), 'goal_key', 'goal_label', 20, null, 'goal'),
                'month' => $this->topEventsDimension($filters, 'goal_key', 'goal_label', 50, null, 'goal'),
            ]),
            'live' => [
                'count' => $this->liveUsersCount(),
                'users' => $this->liveUsers(),
                'map' => $this->liveCountryMap(),
            ],
            'users' => $this->cachedReport('users:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'users' => $this->topEventsDimension($filters, 'user_id', null, 50, fn ($value) => $this->userLabel((int) $value)),
            ]),
            'errors' => $this->cachedReport('errors:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'total' => $this->filteredEventsCount($filters, 'error', $start, $end),
                'items' => $this->topEventsDimension($filters, 'path', 'error_message', 30, null, 'error'),
                'recent' => $this->recentErrors(),
            ]),
            default => [],
        };
    }

    public function exportData(): array
    {
        return [
            'version' => 1,
            'exported_at' => now()->toDateTimeString(),
            'settings' => $this->settings(),
            'daily_stats' => AnalyticsDailyStat::query()->latest('date')->limit(5000)->get()->toArray(),
        ];
    }

    public function prune(string $type, ?int $olderThanDays = null): int
    {
        $query = AnalyticsEvent::query();

        if ($type === 'errors') {
            $query->where('event_type', 'error');
        } elseif ($type === 'all') {
            // No type filter
        } else {
            return 0;
        }

        if ($olderThanDays !== null) {
            $query->where('occurred_at', '<', now()->subDays($olderThanDays));
        }

        $count = $query->count();
        $query->delete();

        // Also prune daily stats if type is all and older than days is set
        if ($type === 'all' && $olderThanDays !== null) {
            AnalyticsDailyStat::query()
                ->where('date', '<', now()->subDays($olderThanDays)->toDateString())
                ->delete();
        }

        return $count;
    }

    public function importData(array $payload): int
    {
        $rows = collect($payload['daily_stats'] ?? [])->take(5000);
        $imported = 0;

        if (isset($payload['settings']) && is_array($payload['settings'])) {
            $this->settings->set('analytics.settings', array_merge(
                $this->defaultSettings(),
                array_intersect_key($payload['settings'], $this->defaultSettings())
            ));
        }

        DB::transaction(function () use ($rows, &$imported) {
            foreach ($rows as $row) {
                if (empty($row['date']) || empty($row['metric']) || !isset($row['dimension_key'])) {
                    continue;
                }

                AnalyticsDailyStat::query()->updateOrCreate(
                    [
                        'date' => Carbon::parse($row['date'])->toDateString(),
                        'metric' => $this->limit((string) $row['metric'], 60),
                        'dimension_key' => $this->limit((string) $row['dimension_key'], 190),
                    ],
                    [
                        'dimension_label' => $this->limit((string) ($row['dimension_label'] ?? $row['dimension_key']), 255),
                        'count' => max(0, (int) ($row['count'] ?? 0)),
                    ]
                );

                $imported++;
            }
        });

        return $imported;
    }

    private function touchLiveVisitor(AnalyticsEvent $event): void
    {
        AnalyticsLiveVisitor::query()->updateOrCreate(
            ['session_id' => $event->session_id],
            [
                'visitor_hash' => $event->visitor_hash,
                'user_id' => $event->user_id,
                'url' => $event->url,
                'path' => $event->path,
                'title' => $event->title,
                'product_id' => $event->product_id,
                'country_code' => $event->country_code,
                'country_name' => $event->country_name,
                'device_type' => $event->device_type,
                'device_brand' => $event->device_brand,
                'ip_address' => $event->ip_address,
                'user_agent' => $event->user_agent,
                'first_seen_at' => AnalyticsLiveVisitor::query()->where('session_id', $event->session_id)->value('first_seen_at') ?: now(),
                'last_seen_at' => now(),
            ]
        );
    }

    private function cleanup(): void
    {
        $settings = $this->settings();
        AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '<', now()->subMinutes((int) $settings['live_user_window_minutes']))
            ->delete();

        AnalyticsEvent::query()
            ->whereNotNull('processed_at')
            ->where('processed_at', '<', now()->subDays((int) $settings['raw_event_retention_days']))
            ->delete();

        AnalyticsDailyStat::query()
            ->where('date', '<', now()->subDays((int) $settings['stats_retention_days'])->toDateString())
            ->delete();
    }

    private function cachedReport(string $key, int $ttl, callable $callback): array
    {
        $settings = $this->settings();
        $ttl = max(10, (int) ($settings['report_cache_seconds'] ?? $ttl));

        return Cache::remember(self::CACHE_PREFIX . $key . ':' . now()->format('YmdHi'), $ttl, $callback);
    }

    private function series(string $metric, Carbon $start, Carbon $end): array
    {
        $rows = AnalyticsDailyStat::query()
            ->where('metric', $metric)
            ->where('dimension_key', '__all__')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('count', 'date');

        $series = [];
        for ($day = $start->copy()->startOfDay(); $day->lte($end); $day->addDay()) {
            $date = $day->toDateString();
            $series[$date] = (int) ($rows[$date] ?? 0);
        }

        return $series;
    }

    private function sumMetric(string $metric, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsDailyStat::query()
            ->where('metric', $metric)
            ->where('dimension_key', '__all__')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('count');
    }

    private function topMetric(string $metric, Carbon $start, Carbon $end, int $limit): array
    {
        return AnalyticsDailyStat::query()
            ->selectRaw('dimension_key, MAX(dimension_label) as dimension_label, SUM(count) as total')
            ->where('metric', $metric)
            ->where('dimension_key', '!=', '__all__')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('dimension_key')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => [
                'key' => $row->dimension_key,
                'label' => $row->dimension_label ?: $row->dimension_key,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    private function normaliseReportFilters(array $filters): array
    {
        $start = isset($filters['from'])
            ? Carbon::parse($filters['from'])->startOfDay()
            : now()->subDays(29)->startOfDay();
        $end = isset($filters['to'])
            ? Carbon::parse($filters['to'])->endOfDay()
            : now()->endOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        if ($start->diffInDays($end) > 731) {
            $start = $end->copy()->subDays(731)->startOfDay();
        }

        return [
            'start' => $start,
            'end' => $end,
            'period' => in_array($filters['period'] ?? 'day', ['day', 'week', 'month'], true) ? $filters['period'] : 'day',
            'country' => strtoupper($this->limit((string) ($filters['country'] ?? ''), 8)),
            'device_type' => $this->limit((string) ($filters['device_type'] ?? ''), 40),
            'activity' => $this->limit((string) ($filters['activity'] ?? ''), 40),
            'search' => $this->limit((string) ($filters['search'] ?? ''), 190),
        ];
    }

    private function publicFilters(array $filters): array
    {
        return [
            'from' => $filters['start']->toDateString(),
            'to' => $filters['end']->toDateString(),
            'period' => $filters['period'],
            'country' => $filters['country'],
            'device_type' => $filters['device_type'],
            'activity' => $filters['activity'],
            'search' => $filters['search'],
        ];
    }

    private function reportCacheKey(string $section, array $filters): string
    {
        return $section . ':' . md5(json_encode($this->publicFilters($filters), JSON_UNESCAPED_UNICODE));
    }

    private function hasRawFilters(array $filters): bool
    {
        return $filters['country'] !== ''
            || $filters['device_type'] !== ''
            || $filters['activity'] !== ''
            || $filters['search'] !== ''
            || $filters['period'] !== 'day';
    }

    private function filteredEventsQuery(array $filters, ?string $eventType = null)
    {
        $query = AnalyticsEvent::query()
            ->whereBetween('occurred_at', [$filters['start'], $filters['end']]);

        if ($eventType) {
            $query->where('event_type', $eventType);
        } elseif ($filters['activity'] !== '') {
            $query->where('event_type', $filters['activity']);
        }

        if ($filters['country'] !== '') {
            $query->where('country_code', $filters['country']);
        }

        if ($filters['device_type'] !== '') {
            $query->where('device_type', $filters['device_type']);
        }

        if ($filters['search'] !== '') {
            $search = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['search']) . '%';
            $query->where(function ($nested) use ($search) {
                $nested->where('path', 'like', $search)
                    ->orWhere('title', 'like', $search)
                    ->orWhere('search_term', 'like', $search)
                    ->orWhere('goal_label', 'like', $search)
                    ->orWhere('country_name', 'like', $search)
                    ->orWhere('device_brand', 'like', $search)
                    ->orWhere('browser', 'like', $search)
                    ->orWhere('platform', 'like', $search);
            });
        }

        return $query;
    }

    private function filteredEventsCount(array $filters, string $eventType, Carbon $start, Carbon $end): int
    {
        $rangeFilters = array_merge($filters, [
            'start' => $start->copy()->startOfDay(),
            'end' => $end->copy()->endOfDay(),
        ]);

        return (int) $this->filteredEventsQuery($rangeFilters, $eventType)->count();
    }

    private function filteredSeries(array $filters, string $eventType): array
    {
        $period = $filters['period'];
        $driver = DB::connection()->getDriverName();
        $expression = match ($period) {
            'month' => $driver === 'sqlite'
                ? "strftime('%Y-%m', occurred_at)"
                : "DATE_FORMAT(occurred_at, '%Y-%m')",
            'week' => $driver === 'sqlite'
                ? "strftime('%Y-W%W', occurred_at)"
                : "DATE_FORMAT(occurred_at, '%x-W%v')",
            default => $driver === 'sqlite'
                ? "date(occurred_at)"
                : "DATE(occurred_at)",
        };

        $rows = $this->filteredEventsQuery($filters, $eventType)
            ->selectRaw("{$expression} as bucket, COUNT(*) as total")
            ->groupBy('bucket')
            ->orderBy('bucket')
            ->pluck('total', 'bucket');

        $series = [];
        $cursor = $filters['start']->copy()->startOfDay();
        while ($cursor->lte($filters['end'])) {
            $bucket = match ($period) {
                'month' => $cursor->format('Y-m'),
                'week' => $cursor->format('o-\WW'),
                default => $cursor->toDateString(),
            };
            $series[$bucket] = (int) ($rows[$bucket] ?? 0);
            match ($period) {
                'month' => $cursor->addMonthNoOverflow(),
                'week' => $cursor->addWeek(),
                default => $cursor->addDay(),
            };
        }

        return $series;
    }

    private function topEventsDimension(array $filters, string $keyColumn, ?string $labelColumn, int $limit, ?callable $labelResolver = null, ?string $eventType = 'pageview'): array
    {
        $selects = "{$keyColumn} as dimension_key, COUNT(*) as total";
        if ($labelColumn) {
            $selects .= ", MAX({$labelColumn}) as dimension_label";
        }

        return $this->filteredEventsQuery($filters, $eventType)
            ->whereNotNull($keyColumn)
            ->where($keyColumn, '!=', '')
            ->selectRaw($selects)
            ->groupBy($keyColumn)
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($row) use ($labelResolver) {
                $key = (string) $row->dimension_key;
                $label = $labelResolver ? $labelResolver($key) : ($row->dimension_label ?: $key);

                return [
                    'key' => $key,
                    'label' => $label,
                    'count' => (int) $row->total,
                ];
            })
            ->all();
    }

    private function eventTypeBreakdown(array $filters): array
    {
        return $this->filteredEventsQuery($filters, null)
            ->selectRaw('event_type as dimension_key, COUNT(*) as total')
            ->groupBy('event_type')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'key' => (string) $row->dimension_key,
                'label' => $this->activityLabel((string) $row->dimension_key),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    private function countryMap(array $countries): array
    {
        return collect($countries)
            ->map(fn ($country) => [
                'code' => strtoupper((string) $country['key']),
                'label' => $country['label'],
                'count' => (int) $country['count'],
            ])
            ->values()
            ->all();
    }

    private function liveCountryMap(): array
    {
        $rows = AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '>=', now()->subMinutes((int) $this->settings()['live_user_window_minutes']))
            ->whereNotNull('country_code')
            ->where('country_code', '!=', '')
            ->selectRaw('country_code as dimension_key, MAX(country_name) as dimension_label, COUNT(*) as total')
            ->groupBy('country_code')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'key' => (string) $row->dimension_key,
                'label' => $row->dimension_label ?: $row->dimension_key,
                'count' => (int) $row->total,
            ])
            ->all();

        return $this->countryMap($rows);
    }

    private function liveUsersCount(): int
    {
        return AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '>=', now()->subMinutes((int) $this->settings()['live_user_window_minutes']))
            ->count();
    }

    private function liveUsers(): array
    {
        return AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '>=', now()->subMinutes((int) $this->settings()['live_user_window_minutes']))
            ->latest('last_seen_at')
            ->limit(50)
            ->get()
            ->map(fn (AnalyticsLiveVisitor $visitor) => [
                'path' => $visitor->path,
                'title' => $visitor->title,
                'product_id' => $visitor->product_id,
                'user_id' => $visitor->user_id,
                'user' => $visitor->user_id ? $this->userLabel((int) $visitor->user_id) : 'مهمان',
                'ip' => $visitor->ip_address ?: '-',
                'country' => $visitor->country_name ?: $visitor->country_code ?: '-',
                'device' => $visitor->device_type ?: '-',
                'device_brand' => $visitor->device_brand ?: '-',
                'user_agent' => $visitor->user_agent ?: '-',
                'last_seen' => $visitor->last_seen_at?->diffForHumans(),
            ])
            ->all();
    }

    private function addIncrement(array &$increments, string $date, string $metric, string $dimensionKey, ?string $label): void
    {
        $key = "{$date}|{$metric}|{$dimensionKey}";
        if (!isset($increments[$key])) {
            $increments[$key] = [
                'date' => $date,
                'metric' => $metric,
                'dimension_key' => $this->limit($dimensionKey, 190),
                'dimension_label' => $this->limit((string) $label, 255),
                'count' => 0,
            ];
        }

        $increments[$key]['count']++;
    }

    private function extractPathContext(string $path, string $url): array
    {
        $context = [
            'product_id' => null,
            'category_key' => null,
            'seller_id' => null,
            'search_term' => null,
        ];

        if (preg_match('#^/product/(?!brand/)([^/]+)#', $path, $matches)) {
            $context['product_id'] = urldecode($matches[1]);
        }

        if (preg_match('#^/(?:result|main)/([^/?]+)#', $path, $matches)) {
            $context['category_key'] = urldecode($matches[1]);
        }

        if (preg_match('#^/seller/([^/?]+)#', $path, $matches)) {
            $context['seller_id'] = urldecode($matches[1]);
        }

        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        foreach (['q', 'query', 'search', 's'] as $key) {
            if (!empty($query[$key])) {
                $context['search_term'] = trim((string) $query[$key]);
                break;
            }
        }

        return $context;
    }

    private function extractUtm(string $url): array
    {
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        return [
            'utm_source' => $this->limit((string) ($query['utm_source'] ?? ''), 120) ?: null,
            'utm_medium' => $this->limit((string) ($query['utm_medium'] ?? ''), 120) ?: null,
            'utm_campaign' => $this->limit((string) ($query['utm_campaign'] ?? ''), 120) ?: null,
            'utm_term' => $this->limit((string) ($query['utm_term'] ?? ''), 120) ?: null,
            'utm_content' => $this->limit((string) ($query['utm_content'] ?? ''), 120) ?: null,
        ];
    }

    private function extractSearchEngine(?string $referrerUrl): ?string
    {
        if (!$referrerUrl) return null;
        $host = (string) parse_url($referrerUrl, PHP_URL_HOST);
        if (preg_match('/google\./i', $host)) return 'Google';
        if (preg_match('/bing\./i', $host)) return 'Bing';
        if (preg_match('/yahoo\./i', $host)) return 'Yahoo';
        if (preg_match('/duckduckgo\./i', $host)) return 'DuckDuckGo';
        if (preg_match('/yandex\./i', $host)) return 'Yandex';
        if (preg_match('/baidu\./i', $host)) return 'Baidu';
        return null;
    }

    private function normaliseGoal(string $goalKey): array
    {
        $goals = [
            'tr_atc' => 'افزودن به سبد خرید',
            'tr_sl' => 'کلیک روی لیست فروشندگان',
            'tr_dk' => 'کلیک Digikala',
            'tr_bs' => 'کلیک Basalam',
        ];

        $key = array_key_exists($goalKey, $goals) ? $goalKey : $this->limit($goalKey, 80);

        return [
            'key' => $key ?: 'custom',
            'label' => $goals[$key] ?? $key ?: 'هدف سفارشی',
        ];
    }

    private function normaliseError(array $payload): array
    {
        return [
            'message' => $this->limit((string) ($payload['error_message'] ?? $payload['message'] ?? 'خطای سمت کاربر'), 1000),
            'source' => $this->limit((string) ($payload['error_source'] ?? $payload['source'] ?? ''), 500) ?: null,
            'line' => isset($payload['error_line']) ? max(0, (int) $payload['error_line']) : null,
        ];
    }

    private function normaliseSessionId(string $sessionId): string
    {
        if ($sessionId === '') {
            $sessionId = (string) Str::uuid();
        }

        return $this->limit(preg_replace('/[^a-zA-Z0-9\-_.]/', '', $sessionId) ?: (string) Str::uuid(), 80);
    }

    private function normalisePath(string $path): string
    {
        $path = '/' . ltrim($path ?: '/', '/');
        return $this->limit($path, 500);
    }

    private function referrerType(?string $referrerUrl, string $host): string
    {
        if (!$referrerUrl) {
            return 'direct';
        }

        $refHost = (string) parse_url($referrerUrl, PHP_URL_HOST);
        if ($refHost === '' || $refHost === $host) {
            return 'internal';
        }

        if (preg_match('/google|bing|duckduckgo|yahoo|yandex/i', $refHost)) {
            return 'search';
        }

        if (preg_match('/instagram|telegram|twitter|x\.com|facebook|linkedin|whatsapp/i', $refHost)) {
            return 'social';
        }

        return 'referral';
    }

    private function referrerLabel(string $type): string
    {
        return [
            'direct' => 'مستقیم',
            'internal' => 'همین سایت',
            'search' => 'موتور جستجو',
            'social' => 'شبکه اجتماعی',
            'referral' => 'ارجاعی',
        ][$type] ?? $type;
    }

    private function deviceTypeLabel(string $type): string
    {
        return [
            'desktop' => 'دسکتاپ',
            'mobile' => 'موبایل',
            'tablet' => 'تبلت',
        ][$type] ?? $type;
    }

    private function activityLabel(string $type): string
    {
        return [
            'pageview' => 'بازدید صفحه',
            'goal' => 'تحقق هدف',
            'error' => 'خطای کاربر',
        ][$type] ?? $type;
    }

    private function deviceType(string $userAgent): string
    {
        if (preg_match('/tablet|ipad/i', $userAgent)) {
            return 'tablet';
        }

        if (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function deviceBrand(string $userAgent): string
    {
        return match (true) {
            preg_match('/iPhone|iPad|Macintosh|Mac OS/i', $userAgent) === 1 => 'Apple',
            preg_match('/Samsung|SM-|GT-/i', $userAgent) === 1 => 'Samsung',
            preg_match('/Huawei|HONOR/i', $userAgent) === 1 => 'Huawei',
            preg_match('/Xiaomi|Redmi|Mi /i', $userAgent) === 1 => 'Xiaomi',
            preg_match('/OPPO/i', $userAgent) === 1 => 'OPPO',
            preg_match('/Vivo/i', $userAgent) === 1 => 'Vivo',
            preg_match('/Windows/i', $userAgent) === 1 => 'Windows PC',
            preg_match('/Linux/i', $userAgent) === 1 => 'Linux PC',
            default => 'Other',
        };
    }

    private function browser(string $userAgent): string
    {
        return match (true) {
            str_contains($userAgent, 'Firefox') => 'Firefox',
            str_contains($userAgent, 'Edg/') => 'Edge',
            str_contains($userAgent, 'Chrome') => 'Chrome',
            str_contains($userAgent, 'Safari') => 'Safari',
            default => 'Other',
        };
    }

    private function platform(string $userAgent): string
    {
        return match (true) {
            preg_match('/Windows/i', $userAgent) === 1 => 'Windows',
            preg_match('/Macintosh|Mac OS/i', $userAgent) === 1 => 'macOS',
            preg_match('/Android/i', $userAgent) === 1 => 'Android',
            preg_match('/iPhone|iPad|iOS/i', $userAgent) === 1 => 'iOS',
            preg_match('/Linux/i', $userAgent) === 1 => 'Linux',
            default => 'Other',
        };
    }

    private function productLabel(string $productId): string
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'product_label:' . $productId,
            3600,
            fn () => Product::query()->whereKey($productId)->value('title') ?: $productId
        );
    }

    private function userLabel(int $userId): string
    {
        return Cache::remember(
            self::CACHE_PREFIX . 'user_label:' . $userId,
            3600,
            fn () => optional(User::query()->find($userId))->name ?: "User #{$userId}"
        );
    }

    private function recentErrors(): array
    {
        return AnalyticsEvent::query()
            ->where('event_type', 'error')
            ->latest('occurred_at')
            ->limit(20)
            ->get()
            ->map(fn (AnalyticsEvent $event) => [
                'message' => $event->error_message ?: 'خطای ناشناس',
                'path' => $event->path,
                'source' => $event->error_source,
                'line' => $event->error_line,
                'user' => $event->user_id ? $this->userLabel((int) $event->user_id) : 'مهمان',
                'ip' => $event->ip_address ?: '-',
                'country' => $event->country_name ?: $event->country_code ?: '-',
                'device' => trim(($event->device_brand ?: '-') . ' / ' . ($event->device_type ?: '-')),
                'user_agent' => $event->user_agent ?: '-',
                'time' => $event->occurred_at?->diffForHumans(),
            ])
            ->all();
    }

    private function isLikelyBot(string $userAgent): bool
    {
        return $userAgent === '' || preg_match('/bot|crawler|spider|headless|preview|curl|wget|python-requests/i', $userAgent) === 1;
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', $value, (string) config('app.key'));
    }

    private function limit(string $value, int $length): string
    {
        return Str::limit(trim($value), $length, '');
    }
}
