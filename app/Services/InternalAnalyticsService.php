<?php

namespace App\Services;

use App\Models\AnalyticsAlert;
use App\Models\AnalyticsDailyStat;
use App\Models\AnalyticsEvent;
use App\Models\AnalyticsFunnel;
use App\Models\AnalyticsFunnelDaily;
use App\Models\AnalyticsLiveVisitor;
use App\Models\AnalyticsSession;
use App\Models\Product;
use App\Models\User;
use App\Repositories\SettingsRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Detection\MobileDetect;

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
            'session_timeout_minutes' => 30,
            'heartbeat_enabled' => true,
            'alerting_enabled' => true,
            'anomaly_sensitivity' => 3,
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
        if (!in_array($eventType, ['pageview', 'goal', 'error', 'heartbeat'], true)) {
            return false;
        }

        if ($eventType === 'error' && !($settings['error_tracking_enabled'] ?? true)) {
            return false;
        }

        if ($eventType === 'heartbeat') {
            return $this->trackHeartbeat($request, $payload);
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

        $detect = new MobileDetect();
        $detect->setHttpHeaders(array_map(fn($values) => implode(', ', $values), $request->headers->all()));

        $isBounce = null;
        $sessionDuration = null;
        $sessionNum = null;

        if ($eventType === 'pageview') {
            $isBounce = $this->checkIsBounce($sessionId);
            $sessionDuration = $this->getSessionDuration($sessionId, $payload);
            $sessionNum = $this->getSessionNum($sessionId);
        }

        $event = AnalyticsEvent::query()->create([
            'session_id' => $sessionId,
            'visitor_hash' => $this->hash($sessionId),
            'user_id' => auth('web')->id(),
            'event_type' => $eventType,
            'goal_key' => $eventType === 'goal' ? $goal['key'] : null,
            'goal_label' => $eventType === 'goal' ? $goal['label'] : null,
            'funnel_key' => $this->limit((string) ($payload['funnel_key'] ?? ''), 80) ?: null,
            'funnel_step' => isset($payload['funnel_step']) ? (int) $payload['funnel_step'] : null,
            'funnel_step_name' => $this->limit((string) ($payload['funnel_step_name'] ?? ''), 120) ?: null,
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
            'city' => $this->limit((string) ($location['city'] ?? ''), 120) ?: null,
            'region' => $this->limit((string) ($location['region'] ?? ''), 120) ?: null,
            'device_type' => $this->deviceType($userAgent),
            'device_brand' => $this->deviceBrand($detect, $userAgent),
            'browser' => $this->browser($detect, $userAgent),
            'platform' => $this->platform($detect, $userAgent),
            'ip_address' => $this->limit($ip, 45),
            'ip_hash' => $this->hash($ip),
            'user_agent' => $userAgent,
            'user_agent_hash' => $this->hash($userAgent),
            'error_message' => $eventType === 'error' ? $error['message'] : null,
            'error_source' => $eventType === 'error' ? $error['source'] : null,
            'error_line' => $eventType === 'error' ? $error['line'] : null,
            'scroll_depth_pct' => $eventType === 'pageview' ? $this->limit((int) ($payload['scroll_depth'] ?? 0), 100) : null,
            'session_num' => $sessionNum,
            'session_duration' => $sessionDuration,
            'is_bounce' => $isBounce,
            'occurred_at' => now(),
        ]);

        if ($event->event_type === 'pageview') {
            $this->touchLiveVisitor($event);
        }

        return true;
    }

    private function trackHeartbeat(Request $request, array $payload): bool
    {
        $sessionId = $this->normaliseSessionId((string) ($payload['visitor_id'] ?? ''));
        if (!$sessionId) return false;

        $visitor = AnalyticsLiveVisitor::query()->where('session_id', $sessionId)->first();
        if ($visitor) {
            $visitor->timestamps = false;
            $visitor->updateQuietly(['last_seen_at' => now()]);
        }

        return true;
    }

    private function checkIsBounce(string $sessionId): bool
    {
        $visitor = AnalyticsLiveVisitor::query()->where('session_id', $sessionId)->first();
        if ($visitor) {
            $visitor->timestamps = false;
            $visitor->increment('pageviews_count');
            $visitor->updateQuietly(['is_bounced' => false]);
        }
        return false;
    }

    private function getSessionDuration(string $sessionId, array $payload): ?int
    {
        if (!empty($payload['session_duration'])) {
            return (int) $payload['session_duration'];
        }

        $visitor = AnalyticsLiveVisitor::query()->where('session_id', $sessionId)->first();
        if ($visitor && $visitor->first_seen_at) {
            return (int) $visitor->first_seen_at->diffInSeconds(now());
        }

        return null;
    }

    private function getSessionNum(string $sessionId): int
    {
        $sessionDate = now()->toDateString();
        $count = AnalyticsEvent::query()
            ->where('session_id', $sessionId)
            ->where('event_type', 'pageview')
            ->whereNull('session_num')
            ->count();

        $existing = AnalyticsEvent::query()
            ->where('session_id', $sessionId)
            ->whereNotNull('session_num')
            ->value('session_num');

        return $existing ?: 1;
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
            $this->processSessions();
            $this->aggregateFunnels();
            $this->checkAlerts();
            return 0;
        }

        $increments = [];
        $eventIds = [];
        $sessionData = [];
        $uniqueVisitors = [];
        $funnelEvents = [];

        foreach ($events as $event) {
            $eventIds[] = $event->id;
            $date = $event->occurred_at->toDateString();

            if ($event->event_type === 'heartbeat') continue;

            if ($event->event_type === 'pageview') {
                $this->addIncrement($increments, $date, 'pageview', '__all__', 'همه بازدیدها');
                $this->addIncrement($increments, $date, 'page', $event->path ?: '/', $event->title ?: $event->path ?: '/');

                $sessionData[$event->session_id] = $event;

                $vh = $event->visitor_hash;
                if (!isset($uniqueVisitors[$date][$vh])) {
                    $uniqueVisitors[$date][$vh] = true;
                }

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

                if ($event->city) {
                    $this->addIncrement($increments, $date, 'city', $event->city, $event->city);
                }
            }

            $this->addIncrement($increments, $date, 'activity', $event->event_type, $this->activityLabel($event->event_type));

            if ($event->event_type === 'goal' && $event->goal_key) {
                $this->addIncrement($increments, $date, 'goal', $event->goal_key, $event->goal_label ?: $event->goal_key);
            }

            if ($event->funnel_key) {
                $funnelEvents[] = $event;
            }

            if ($event->event_type === 'error') {
                $this->addIncrement($increments, $date, 'error', '__all__', 'همه خطاها');
                $this->addIncrement($increments, $date, 'error', $event->path ?: '/', $event->error_message ?: $event->path ?: 'خطای ناشناس');
            }
        }

        foreach ($uniqueVisitors as $date => $visitors) {
            $this->addIncrement($increments, $date, 'unique_visitor', '__all__', 'بازدیدکننده یکتا', count($visitors));
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
        $this->processSessions();
        $this->aggregateFunnels($funnelEvents);
        $this->checkAlerts();
        Cache::put(self::CACHE_PREFIX . 'last_aggregated_at', now()->toDateTimeString(), 86400);

        return count($eventIds);
    }

    private function processSessions(): void
    {
        $timeout = (int) $this->settings()['session_timeout_minutes'];
        $cutoff = now()->subMinutes($timeout);

        $expiredVisitors = AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '<', $cutoff)
            ->get();

        foreach ($expiredVisitors as $visitor) {
            $duration = $visitor->first_seen_at->diffInSeconds($visitor->last_seen_at);
            $date = $visitor->first_seen_at->toDateString();
            $isBounce = $visitor->pageviews_count <= 1;

            AnalyticsSession::query()->updateOrCreate(
                ['session_id' => $visitor->session_id],
                [
                    'visitor_hash' => $visitor->visitor_hash,
                    'user_id' => $visitor->user_id,
                    'date' => $date,
                    'started_at' => $visitor->first_seen_at,
                    'ended_at' => $visitor->last_seen_at,
                    'duration_seconds' => $duration,
                    'pageviews_count' => $visitor->pageviews_count,
                    'is_bounce' => $isBounce,
                    'entry_path' => $this->getFirstPath($visitor->session_id),
                    'exit_path' => $visitor->path,
                    'device_type' => $visitor->device_type,
                    'country_code' => $visitor->country_code,
                ]
            );

            $this->aggregateSessionMetrics($date, $duration, $isBounce);

            $visitor->delete();
        }
    }

    private function aggregateSessionMetrics(string $date, int $duration, bool $isBounce): void
    {
        $stat = AnalyticsDailyStat::query()->firstOrNew([
            'date' => $date,
            'metric' => 'session',
            'dimension_key' => '__all__',
        ]);
        $stat->dimension_label = 'جلسات';
        $stat->count = (int) $stat->count + 1;
        $stat->save();

        $durStat = AnalyticsDailyStat::query()->firstOrNew([
            'date' => $date,
            'metric' => 'session_duration',
            'dimension_key' => '__all__',
        ]);
        $durStat->dimension_label = 'مدت جلسه (ثانیه)';
        $durStat->count = (int) $durStat->count + $duration;
        $durStat->save();

        if ($isBounce) {
            $bounceStat = AnalyticsDailyStat::query()->firstOrNew([
                'date' => $date,
                'metric' => 'bounce',
                'dimension_key' => '__all__',
            ]);
            $bounceStat->dimension_label = 'نرخ پرش';
            $bounceStat->count = (int) $bounceStat->count + 1;
            $bounceStat->save();
        }
    }

    private function getFirstPath(string $sessionId): ?string
    {
        return AnalyticsEvent::query()
            ->where('session_id', $sessionId)
            ->where('event_type', 'pageview')
            ->oldest('occurred_at')
            ->value('path');
    }

    private function aggregateFunnels(array $funnelEvents = []): void
    {
        if (empty($funnelEvents)) {
            $funnelEvents = AnalyticsEvent::query()
                ->whereNotNull('funnel_key')
                ->whereNull('processed_at')
                ->get();
        }

        $funnels = AnalyticsFunnel::query()->where('is_active', true)->get()->keyBy('key');

        foreach ($funnelEvents as $event) {
            $fk = $event->funnel_key;
            if (!$fk || !isset($funnels[$fk])) continue;

            $step = (int) $event->funnel_step;
            $date = $event->occurred_at->toDateString();

            AnalyticsFunnelDaily::query()->updateOrCreate(
                [
                    'funnel_key' => $fk,
                    'date' => $date,
                    'step' => $step,
                ],
                [
                    'step_name' => (string) ($event->funnel_step_name ?: "Step {$step}"),
                    'entered' => DB::raw('entered + 1'),
                ]
            );
        }
    }

    public function funnelReport(string $funnelKey, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $funnel = AnalyticsFunnel::query()->where('key', $funnelKey)->first();
        if (!$funnel) return [];

        $start = $start ?: now()->subDays(29)->startOfDay();
        $end = $end ?: now()->endOfDay();

        $steps = is_string($funnel->steps) ? json_decode($funnel->steps, true) : ($funnel->steps ?? []);

        $daily = AnalyticsFunnelDaily::query()
            ->where('funnel_key', $funnelKey)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('step, step_name, SUM(entered) as total_entered, SUM(exited) as total_exited')
            ->groupBy('step', 'step_name')
            ->orderBy('step')
            ->get()
            ->keyBy('step');

        $result = [];
        $previousTotal = null;

        foreach ($steps as $i => $step) {
            $stepNum = $i + 1;
            $data = $daily->get($stepNum);
            $entered = $data ? (int) $data->total_entered : 0;
            $exited = $data ? (int) $data->total_exited : 0;
            $conversion = $previousTotal !== null && $previousTotal > 0
                ? round(($entered / $previousTotal) * 100, 1)
                : 100;
            $dropoff = $previousTotal !== null ? $previousTotal - $entered : 0;
            $dropoffRate = $previousTotal !== null && $previousTotal > 0
                ? round(($dropoff / $previousTotal) * 100, 1)
                : 0;

            $result[] = [
                'step' => $stepNum,
                'name' => $step['name'] ?? $data->step_name ?? "Step {$stepNum}",
                'entered' => $entered,
                'exited' => $exited,
                'conversion_pct' => $conversion,
                'dropoff' => $dropoff,
                'dropoff_pct' => $dropoffRate,
            ];

            $previousTotal = $entered;
        }

        return [
            'funnel' => $funnel->toArray(),
            'steps' => $result,
        ];
    }

    public function cohortAnalysis(?Carbon $start = null, int $periods = 12): array
    {
        $start = $start ?: now()->subMonths($periods)->startOfMonth();
        $end = now()->endOfMonth();

        $firstVisits = AnalyticsEvent::query()
            ->selectRaw('visitor_hash, MIN(DATE(occurred_at)) as first_date')
            ->where('event_type', 'pageview')
            ->whereNotNull('visitor_hash')
            ->where('occurred_at', '>=', $start)
            ->groupBy('visitor_hash')
            ->get()
            ->groupBy('first_date');

        $cohorts = [];
        foreach ($firstVisits as $firstDate => $visitors) {
            $hashes = $visitors->pluck('visitor_hash')->toArray();
            $cohortMonth = Carbon::parse($firstDate)->startOfMonth()->toDateString();

            $monthlyActivity = AnalyticsEvent::query()
                ->selectRaw('strftime(\'%Y-%m\', occurred_at) as month, COUNT(DISTINCT visitor_hash) as active')
                ->whereIn('visitor_hash', $hashes)
                ->where('event_type', 'pageview')
                ->where('occurred_at', '>=', $firstDate)
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('active', 'month');

            $total = count($hashes);
            $periodData = [];
            for ($i = 0; $i < 6; $i++) {
                $m = Carbon::parse($cohortMonth)->addMonthsNoOverflow($i)->format('Y-m');
                $active = (int) ($monthlyActivity[$m] ?? 0);
                $periodData[] = [
                    'period' => $m,
                    'active' => $active,
                    'retention_pct' => $total > 0 ? round(($active / $total) * 100, 1) : 0,
                ];
            }

            $cohorts[] = [
                'cohort' => $cohortMonth,
                'total' => $total,
                'periods' => $periodData,
            ];
        }

        return array_slice($cohorts, 0, 12);
    }

    public function userJourney(string $sessionId, int $limit = 50): array
    {
        $session = AnalyticsSession::query()->where('session_id', $sessionId)->first();

        $events = AnalyticsEvent::query()
            ->where('session_id', $sessionId)
            ->where('event_type', '!=', 'heartbeat')
            ->orderBy('occurred_at')
            ->limit($limit)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'type' => $e->event_type,
                'url' => $e->url,
                'path' => $e->path,
                'title' => $e->title,
                'time' => $e->occurred_at->toDateTimeString(),
                'time_ago' => $e->occurred_at->diffForHumans(),
                'goal' => $e->goal_label,
                'goal_key' => $e->goal_key,
                'funnel' => $e->funnel_key ? [
                    'key' => $e->funnel_key,
                    'step' => $e->funnel_step,
                    'step_name' => $e->funnel_step_name,
                ] : null,
                'ip' => $e->ip_address,
                'country' => $e->country_name ?: $e->country_code,
                'city' => $e->city,
                'region' => $e->region,
                'device' => $e->device_type,
                'device_brand' => $e->device_brand,
                'browser' => $e->browser,
                'platform' => $e->platform,
                'user_agent' => $e->user_agent,
                'referrer_type' => $e->referrer_type,
                'referrer_host' => $e->referrer_host,
                'referrer_url' => $e->referrer_url,
                'search_engine' => $e->search_engine,
                'utm' => array_filter([
                    'source' => $e->utm_source,
                    'medium' => $e->utm_medium,
                    'campaign' => $e->utm_campaign,
                    'term' => $e->utm_term,
                    'content' => $e->utm_content,
                ]),
                'duration' => $e->session_duration,
                'scroll' => $e->scroll_depth_pct,
                'error_message' => $e->error_message,
                'error_source' => $e->error_source,
                'error_line' => $e->error_line,
            ]);

        $paths = AnalyticsEvent::query()
            ->where('session_id', $sessionId)
            ->where('event_type', 'pageview')
            ->orderBy('occurred_at')
            ->pluck('path')
            ->toArray();

        $baseQuery = null;
        if ($session?->visitor_hash) {
            $baseQuery = AnalyticsSession::query()->where('visitor_hash', $session->visitor_hash);
        } elseif ($session?->user_id) {
            $baseQuery = AnalyticsSession::query()->where('user_id', $session->user_id);
        }

        $relatedSessions = $baseQuery
            ? (clone $baseQuery)
                ->where('session_id', '!=', $sessionId)
                ->latest('started_at')
                ->limit(8)
                ->get()
                ->map(fn ($item) => [
                    'session_id' => $item->session_id,
                    'started_at' => $item->started_at?->toISOString(),
                    'ended_at' => $item->ended_at?->toISOString(),
                    'duration_seconds' => $item->duration_seconds,
                    'pageviews_count' => $item->pageviews_count,
                    'entry_path' => $item->entry_path,
                    'exit_path' => $item->exit_path,
                    'landing_source' => $item->landing_source,
                    'referrer_type' => $item->referrer_type,
                    'country_code' => $item->country_code,
                    'device_type' => $item->device_type,
                    'browser' => $item->browser,
                    'platform' => $item->platform,
                ])
                ->all()
            : [];

        $goalCount = $events->where('type', 'goal')->count();
        $errorCount = $events->where('type', 'error')->count();
        $pageviewCount = $events->where('type', 'pageview')->count();
        $lastEvent = $events->last();

        return [
            'events' => $events,
            'path_sequence' => $paths,
            'session' => $session,
            'summary' => [
                'pageviews' => $pageviewCount,
                'goals' => $goalCount,
                'errors' => $errorCount,
                'events' => $events->count(),
                'last_event_at' => $lastEvent['time'] ?? null,
                'last_event_ago' => $lastEvent['time_ago'] ?? null,
            ],
            'related_sessions' => $relatedSessions,
        ];
    }

    public function popularPaths(?Carbon $start = null, ?Carbon $end = null, int $limit = 20): array
    {
        $start = $start ?: now()->subDays(29)->startOfDay();
        $end = $end ?: now()->endOfDay();

        $transitions = AnalyticsEvent::query()
            ->from('analytics_events as a1')
            ->join('analytics_events as a2', function ($join) {
                $join->on('a1.session_id', '=', 'a2.session_id')
                    ->whereRaw('a2.id = (SELECT MIN(id) FROM analytics_events WHERE session_id = a1.session_id AND id > a1.id)');
            })
            ->where('a1.event_type', 'pageview')
            ->where('a2.event_type', 'pageview')
            ->whereBetween('a1.occurred_at', [$start, $end])
            ->selectRaw('a1.path as from_path, a2.path as to_path, COUNT(*) as count')
            ->groupBy('a1.path', 'a2.path')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();

        return $transitions->map(fn($t) => [
            'from' => $t->from_path,
            'to' => $t->to_path,
            'count' => (int) $t->count,
        ])->all();
    }

    public function rawData(array $filters = [], int $perPage = 50, int $page = 1): array
    {
        $query = AnalyticsEvent::query()
            ->where('event_type', '!=', 'heartbeat');

        if (!empty($filters['from'])) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['from']));
        }
        if (!empty($filters['to'])) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['to']));
        }
        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }
        if (!empty($filters['session_id'])) {
            $query->where('session_id', $filters['session_id']);
        }
        if (!empty($filters['visitor_hash'])) {
            $query->where('visitor_hash', $filters['visitor_hash']);
        }
        if (!empty($filters['path'])) {
            $query->where('path', 'like', '%' . $filters['path'] . '%');
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(fn($q) => $q->where('path', 'like', $s)
                ->orWhere('title', 'like', $s)
                ->orWhere('search_term', 'like', $s));
        }

        $total = $query->count();
        $items = $query->latest('occurred_at')
            ->skip(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'type' => $e->event_type,
                'session_id' => $e->session_id,
                'user_id' => $e->user_id,
                'path' => $e->path,
                'title' => $e->title,
                'goal' => $e->goal_label,
                'referrer' => $e->referrer_type,
                'country' => $e->country_name,
                'city' => $e->city,
                'device' => $e->device_brand . ' ' . $e->device_type,
                'browser' => $e->browser,
                'platform' => $e->platform,
                'utm_source' => $e->utm_source,
                'utm_campaign' => $e->utm_campaign,
                'search_term' => $e->search_term,
                'duration' => $e->session_duration,
                'bounce' => $e->is_bounce,
                'scroll' => $e->scroll_depth_pct,
                'error' => $e->error_message,
                'time' => $e->occurred_at->toDateTimeString(),
            ]);

        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function comparativeReport(string $metric, Carbon $currentStart, Carbon $currentEnd, Carbon $previousStart, Carbon $previousEnd): array
    {
        $current = AnalyticsEvent::query()
            ->where('event_type', $metric === 'pageview' ? 'pageview' : ($metric === 'goal' ? 'goal' : 'error'))
            ->whereBetween('occurred_at', [$currentStart, $currentEnd])
            ->count();

        $previous = AnalyticsEvent::query()
            ->where('event_type', $metric === 'pageview' ? 'pageview' : ($metric === 'goal' ? 'goal' : 'error'))
            ->whereBetween('occurred_at', [$previousStart, $previousEnd])
            ->count();

        $change = $previous > 0 ? round((($current - $previous) / $previous) * 100, 1) : ($current > 0 ? 100 : 0);

        $currentSeries = $this->filteredSeries(['start' => $currentStart, 'end' => $currentEnd, 'period' => 'day'], $metric === 'pageview' ? 'pageview' : ($metric === 'goal' ? 'goal' : 'error'));
        $previousSeries = $this->filteredSeries(['start' => $previousStart, 'end' => $previousEnd, 'period' => 'day'], $metric === 'pageview' ? 'pageview' : ($metric === 'goal' ? 'goal' : 'error'));

        return [
            'metric' => $metric,
            'current' => ['start' => $currentStart->toDateString(), 'end' => $currentEnd->toDateString(), 'total' => $current],
            'previous' => ['start' => $previousStart->toDateString(), 'end' => $previousEnd->toDateString(), 'total' => $previous],
            'change' => $change,
            'change_direction' => $change >= 0 ? 'up' : 'down',
            'series' => [
                'current' => $currentSeries,
                'previous' => $previousSeries,
            ],
        ];
    }

    public function checkAlerts(): void
    {
        if (!($this->settings()['alerting_enabled'] ?? true)) return;

        $alerts = AnalyticsAlert::query()->where('is_active', true)->get();
        if ($alerts->isEmpty()) return;

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $lastWeek = now()->subDays(7)->toDateString();

        foreach ($alerts as $alert) {
            if ($alert->last_triggered_at && $alert->last_triggered_at->diffInHours(now()) < $alert->cooldown_hours) {
                continue;
            }

            $actualValue = $this->getMetricValue($alert->metric, $today);

            $shouldTrigger = match ($alert->condition) {
                'gt' => $actualValue > $alert->threshold,
                'lt' => $actualValue < $alert->threshold,
                'gte' => $actualValue >= $alert->threshold,
                'lte' => $actualValue <= $alert->threshold,
                'eq' => $actualValue == $alert->threshold,
                'pct_change_gt' => $this->getMetricPctChange($alert->metric, $today, $yesterday) > $alert->threshold,
                'pct_change_lt' => $this->getMetricPctChange($alert->metric, $today, $yesterday) < $alert->threshold,
                default => false,
            };

            if ($shouldTrigger) {
                $alert->update(['last_triggered_at' => now()]);
                DB::table('analytics_alert_logs')->insert([
                    'alert_id' => $alert->id,
                    'actual_value' => $actualValue,
                    'threshold' => $alert->threshold,
                    'triggered_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function getMetricValue(string $metric, string $date): float
    {
        $stat = AnalyticsDailyStat::query()
            ->where('date', $date)
            ->where('metric', $metric)
            ->where('dimension_key', '__all__')
            ->first();
        return (float) ($stat->count ?? 0);
    }

    private function getMetricPctChange(string $metric, string $currentDate, string $previousDate): float
    {
        $current = $this->getMetricValue($metric, $currentDate);
        $previous = $this->getMetricValue($metric, $previousDate);
        if ($previous == 0) return $current > 0 ? 100 : 0;
        return round((($current - $previous) / $previous) * 100, 1);
    }

    public function alertLogs(int $limit = 50): array
    {
        return DB::table('analytics_alert_logs')
            ->join('analytics_alerts', 'analytics_alert_logs.alert_id', '=', 'analytics_alerts.id')
            ->select('analytics_alert_logs.*', 'analytics_alerts.name', 'analytics_alerts.metric')
            ->latest('analytics_alert_logs.triggered_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    public function dashboard(): array
    {
        return $this->cachedReport('dashboard', 45, function () {
            $today = now()->toDateString();
            $todayCount = $this->sumMetric('pageview', now(), now());
            $todayUniques = $this->getMetricRaw('unique_visitor', $today);
            $todaySessions = $this->getMetricRaw('session', $today);
            $todayBounces = $this->getMetricRaw('bounce', $today);
            $bounceRate = $todaySessions > 0 ? round(($todayBounces / $todaySessions) * 100, 1) : 0;

            return [
                'today' => $todayCount,
                'today_uniques' => $todayUniques,
                'today_sessions' => $todaySessions,
                'bounce_rate' => $bounceRate,
                'chart' => $this->series('pageview', now()->subDays(29), now()),
                'goals' => $this->topMetric('goal', now(), now(), 6),
                'live' => $this->liveUsersCount(),
            ];
        });
    }

    private function getMetricRaw(string $metric, string $date): int
    {
        return (int) AnalyticsDailyStat::query()
            ->where('date', $date)
            ->where('metric', $metric)
            ->where('dimension_key', '__all__')
            ->value('count');
    }

    public function userActivity(int $userId, array $filters = []): array
    {
        $query = AnalyticsEvent::query()
            ->where('user_id', $userId)
            ->where('event_type', '!=', 'heartbeat');

        if (!empty($filters['from'])) {
            $query->where('occurred_at', '>=', Carbon::parse($filters['from']));
        }
        if (!empty($filters['to'])) {
            $query->where('occurred_at', '<=', Carbon::parse($filters['to']));
        }
        if (!empty($filters['event_type'])) {
            $query->where('event_type', $filters['event_type']);
        }
        if (!empty($filters['search'])) {
            $s = '%' . $filters['search'] . '%';
            $query->where(fn($q) => $q->where('path', 'like', $s)->orWhere('title', 'like', $s));
        }

        $perPage = min((int) ($filters['per_page'] ?? 100), 500);
        $page = max(1, (int) ($filters['page'] ?? 1));
        $total = $query->count();

        $events = $query->latest('occurred_at')
            ->skip(($page - 1) * $perPage)
            ->limit($perPage)
            ->get()
            ->map(fn($e) => [
                'id' => $e->id,
                'type' => $e->event_type,
                'path' => $e->path,
                'title' => $e->title,
                'time' => $e->occurred_at->diffForHumans(),
                'full_time' => $e->occurred_at->toDateTimeString(),
                'ip' => $e->ip_address,
                'country' => $e->country_name ?: $e->country_code,
                'city' => $e->city,
                'device' => $e->device_brand . ' / ' . $e->device_type,
                'browser' => $e->browser,
                'platform' => $e->platform,
                'duration' => $e->session_duration,
                'scroll' => $e->scroll_depth_pct,
                'bounce' => $e->is_bounce,
                'search_engine' => $e->search_engine,
                'utm' => array_filter([
                    'source' => $e->utm_source,
                    'medium' => $e->utm_medium,
                    'campaign' => $e->utm_campaign,
                ]),
                'goal' => $e->goal_label,
                'funnel' => $e->funnel_key ? [
                    'key' => $e->funnel_key,
                    'step' => $e->funnel_step,
                    'step_name' => $e->funnel_step_name,
                ] : null,
            ])->all();

        return [
            'user' => $this->userLabel($userId),
            'events' => $events,
            'total' => $total,
            'per_page' => $perPage,
            'page' => $page,
            'last_page' => max(1, (int) ceil($total / $perPage)),
        ];
    }

    public function report(string $section, array $filters = []): array
    {
        $filters = $this->normaliseReportFilters($filters);
        $start = $filters['start'];
        $end = $filters['end'];
        $cacheKey = $this->reportCacheKey($section, $filters);
        $cacheTtl = $this->hasRawFilters($filters) ? 15 : 60;

        if (in_array($section, ['live'], true)) {
            $cacheTtl = 0;
        }

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
                'today_uniques' => $this->getMetricForDateRange('unique_visitor', $start, $end),
                'sessions' => $this->getMetricForDateRange('session', $start, $end),
                'bounce_rate' => $this->bounceRateForRange($start, $end),
                'avg_duration' => $this->avgDurationForRange($start, $end),
            ]),
            'reports' => $this->cachedReport('reports:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'countries' => $this->topEventsDimension($filters, 'country_code', 'country_name', 80),
                'cities' => $this->topEventsDimension($filters, 'city', 'city', 20),
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
            'live' => $this->cachedLiveData(),
            'users' => $this->cachedReport('users:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'users' => collect($this->topEventsDimension($filters, 'user_id', null, 50, fn ($value) => $this->userLabel((int) $value)))
                    ->filter(fn ($row) => (int) ($row['key'] ?? 0) > 0)
                    ->values()
                    ->all(),
            ]),
            'errors' => $this->cachedReport('errors:' . $cacheKey, $cacheTtl, fn () => [
                'filters' => $this->publicFilters($filters),
                'total' => $this->filteredEventsCount($filters, 'error', $start, $end),
                'items' => $this->topEventsDimension($filters, 'path', 'error_message', 30, null, 'error'),
                'recent' => $this->recentErrors(),
            ]),
            'funnels' => $this->funnelsReport($start, $end),
            'sessions' => $this->sessionsReport($start, $end),
            'journeys' => $this->popularPaths($start, $end),
            'comparative' => $this->comparativeReport('pageview', $start, $end, Carbon::parse($start)->subDays($start->diffInDays($end) + 1), Carbon::parse($end)->subDays($start->diffInDays($end) + 1)),
            'cohorts' => ['cohorts' => $this->cohortAnalysis($start)],
            'raw' => $this->rawData($filters, 50, (int) ($filters['page'] ?? 1)),
            'alerts' => [
                'alerts' => AnalyticsAlert::query()->where('is_active', true)->get()->toArray(),
                'logs' => $this->alertLogs(),
            ],
            default => [],
        };
    }

    private function funnelsReport(Carbon $start, Carbon $end): array
    {
        $funnels = AnalyticsFunnel::query()->where('is_active', true)->get();
        return [
            'funnels' => $funnels->map(fn($f) => [
                'key' => $f->key,
                'name' => $f->name,
                'steps' => is_string($f->steps) ? json_decode($f->steps, true) : ($f->steps ?? []),
            ]),
            'report' => $funnels->map(fn($f) => $this->funnelReport($f->key, $start, $end)),
        ];
    }

    private function sessionsReport(Carbon $start, Carbon $end): array
    {
        $sessions = AnalyticsSession::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COUNT(*) as total, SUM(is_bounce) as bounces, AVG(duration_seconds) as avg_duration, SUM(pageviews_count) as total_pageviews')
            ->first();

        $total = (int) ($sessions->total ?? 0);
        $bounces = (int) ($sessions->bounces ?? 0);
        $avgDuration = (int) ($sessions->avg_duration ?? 0);
        $totalPv = (int) ($sessions->total_pageviews ?? 0);

        $byDevice = AnalyticsSession::query()
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('device_type, COUNT(*) as total, AVG(duration_seconds) as avg_dur, SUM(is_bounce) as bounces')
            ->whereNotNull('device_type')
            ->groupBy('device_type')
            ->get();

        return [
            'total' => $total,
            'bounces' => $bounces,
            'bounce_rate' => $total > 0 ? round(($bounces / $total) * 100, 1) : 0,
            'avg_duration_seconds' => $avgDuration,
            'avg_duration_formatted' => gmdate('i:s', $avgDuration),
            'avg_pageviews_per_session' => $total > 0 ? round($totalPv / $total, 1) : 0,
            'by_device' => $byDevice,
        ];
    }

    private function getMetricForDateRange(string $metric, Carbon $start, Carbon $end): int
    {
        return (int) AnalyticsDailyStat::query()
            ->where('metric', $metric)
            ->where('dimension_key', '__all__')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('count');
    }

    private function bounceRateForRange(Carbon $start, Carbon $end): float
    {
        $sessions = $this->getMetricForDateRange('session', $start, $end);
        $bounces = $this->getMetricForDateRange('bounce', $start, $end);
        return $sessions > 0 ? round(($bounces / $sessions) * 100, 1) : 0;
    }

    private function avgDurationForRange(Carbon $start, Carbon $end): int
    {
        $totalDur = (int) AnalyticsDailyStat::query()
            ->where('metric', 'session_duration')
            ->where('dimension_key', '__all__')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->sum('count');
        $sessions = $this->getMetricForDateRange('session', $start, $end);
        return $sessions > 0 ? (int) round($totalDur / $sessions) : 0;
    }

    public function csvExport(string $section, array $filters = []): string
    {
        $data = $this->report($section, $filters);
        $rows = [];

        $headerMap = [
            'overview' => ['معیار', 'مقدار'],
            'reports' => ['کلید', 'برچسب', 'تعداد'],
            'content' => ['صفحه', 'عنوان', 'بازدید'],
            'search' => ['کلمه جستجو', 'تعداد'],
            'goals' => ['هدف', 'تعداد'],
            'users' => ['کاربر', 'بازدید'],
            'errors' => ['خطا', 'مسیر', 'تعداد'],
            'sessions' => ['نوع', 'مقدار'],
            'raw' => ['آی‌دی', 'نوع', 'مسیر', 'عنوان', 'کشور', 'مرورگر', 'دستگاه', 'زمان'],
        ];

        $headers = $headerMap[$section] ?? ['کلید', 'مقدار'];

        return match ($section) {
            'overview' => $this->arrayToCsv(array_merge(
                [['معیار', 'مقدار']],
                [['بازدید امروز', $data['today'] ?? 0]],
                [['بازدید ۷ روز', $data['week'] ?? 0]],
                [['بازدید ۳۰ روز', $data['month'] ?? 0]],
                [['کاربران زنده', $data['live'] ?? 0]],
                [['بازدیدکننده یکتا', $data['today_uniques'] ?? 0]],
                [['جلسات', $data['sessions'] ?? 0]],
                [['نرخ پرش', ($data['bounce_rate'] ?? 0) . '%']],
            )),
            'reports' => $this->arrayToCsv($this->flattenReport($data, ['countries', 'referrers', 'device_types', 'browsers', 'platforms'])),
            'content' => $this->arrayToCsv($this->flattenReport($data, ['pages', 'products', 'categories', 'sellers'])),
            'search' => $this->arrayToCsv(($data['keywords'] ?? []), ['برچسب', 'تعداد']),
            'goals' => $this->arrayToCsv(($data['month'] ?? []), ['هدف', 'تعداد']),
            'sessions' => $this->arrayToCsv([
                ['معیار', 'مقدار'],
                ['کل جلسات', $data['total'] ?? 0],
                ['نرخ پرش', ($data['bounce_rate'] ?? 0) . '%'],
                ['مدت متوسط', $data['avg_duration_formatted'] ?? '0:00'],
                ['میانگین صفحات', $data['avg_pageviews_per_session'] ?? 0],
            ]),
            'raw' => $this->eventsToCsv($data['data'] ?? []),
            default => '',
        };
    }

    private function flattenReport(array $data, array $keys): array
    {
        $rows = [['بخش', 'برچسب', 'تعداد']];
        foreach ($keys as $key) {
            foreach ($data[$key] ?? [] as $item) {
                $rows[] = [$key, $item['label'] ?? '-', $item['count'] ?? 0];
            }
        }
        return $rows;
    }

    private function eventsToCsv(array $events): string
    {
        $rows = [['آی‌دی', 'نوع', 'مسیر', 'عنوان', 'کشور', 'مرورگر', 'دستگاه', 'زمان']];
        foreach ($events as $e) {
            $rows[] = [
                $e['id'] ?? '',
                $e['type'] ?? '',
                $e['path'] ?? '',
                $e['title'] ?? '',
                $e['country'] ?? '',
                $e['browser'] ?? '',
                $e['device'] ?? '',
                $e['time'] ?? '',
            ];
        }
        return $this->arrayToCsv($rows);
    }

    private function arrayToCsv(array $rows, ?array $header = null): string
    {
        if (empty($rows)) return '';

        $output = fopen('php://temp', 'r+');
        foreach ($rows as $row) {
            if (is_array($row)) {
                fputcsv($output, $row);
            }
        }
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        return $csv;
    }

    public function exportData(): array
    {
        return [
            'version' => 2,
            'exported_at' => now()->toDateTimeString(),
            'settings' => $this->settings(),
            'daily_stats' => AnalyticsDailyStat::query()->latest('date')->limit(5000)->get()->toArray(),
            'funnels' => AnalyticsFunnel::query()->get()->toArray(),
            'alerts' => AnalyticsAlert::query()->get()->toArray(),
        ];
    }

    public function prune(string $type, ?int $olderThanDays = null): int
    {
        $query = AnalyticsEvent::query();

        if ($type === 'errors') {
            $query->where('event_type', 'error');
        } elseif ($type === 'sessions') {
            // Prune sessions
        } elseif ($type !== 'all') {
            return 0;
        }

        if ($olderThanDays !== null) {
            $query->where('occurred_at', '<', now()->subDays($olderThanDays));
        }

        $count = $query->count();
        $query->delete();

        if (in_array($type, ['all', 'sessions']) && $olderThanDays !== null) {
            AnalyticsSession::query()
                ->where('date', '<', now()->subDays($olderThanDays)->toDateString())
                ->delete();
        }

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

        if (!empty($payload['funnels'])) {
            foreach ($payload['funnels'] as $f) {
                AnalyticsFunnel::query()->updateOrCreate(
                    ['key' => $f['key']],
                    ['name' => $f['name'], 'steps' => $f['steps'], 'is_active' => $f['is_active'] ?? true]
                );
            }
        }

        if (!empty($payload['alerts'])) {
            foreach ($payload['alerts'] as $a) {
                AnalyticsAlert::query()->updateOrCreate(
                    ['name' => $a['name'], 'metric' => $a['metric']],
                    ['condition' => $a['condition'], 'threshold' => $a['threshold']]
                );
            }
        }

        return $imported;
    }

    private function touchLiveVisitor(AnalyticsEvent $event): void
    {
        $existing = AnalyticsLiveVisitor::query()->where('session_id', $event->session_id)->first();

        if ($existing) {
            $existing->timestamps = false;
            $existing->updateQuietly([
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
                'last_seen_at' => now(),
            ]);
        } else {
            AnalyticsLiveVisitor::query()->create([
                'session_id' => $event->session_id,
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
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'pageviews_count' => 1,
                'is_bounced' => true,
            ]);
        }
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

        if ($ttl === 0) {
            return $callback();
        }

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

        $period = (string) ($filters['period'] ?? 'day');

        return [
            'start' => $start,
            'end' => $end,
            'period' => in_array($period, ['day', 'week', 'month'], true) ? $period : 'day',
            'country' => strtoupper($this->limit((string) ($filters['country'] ?? ''), 8)),
            'device_type' => $this->limit((string) ($filters['device_type'] ?? ''), 40),
            'activity' => $this->limit((string) ($filters['activity'] ?? ''), 40),
            'search' => $this->limit((string) ($filters['search'] ?? ''), 190),
            'path' => $this->limit((string) ($filters['path'] ?? ''), 500),
            'goal_key' => $this->limit((string) ($filters['goal_key'] ?? ''), 80),
            'source' => $this->limit((string) ($filters['source'] ?? ''), 120),
            'browser' => $this->limit((string) ($filters['browser'] ?? ''), 120),
            'platform' => $this->limit((string) ($filters['platform'] ?? ''), 120),
            'campaign' => $this->limit((string) ($filters['campaign'] ?? ''), 120),
            'session_status' => $this->limit((string) ($filters['session_status'] ?? ''), 40),
            'page' => max(1, (int) ($filters['page'] ?? 1)),
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
            'path' => $filters['path'],
            'goal_key' => $filters['goal_key'],
            'source' => $filters['source'],
            'browser' => $filters['browser'],
            'platform' => $filters['platform'],
            'campaign' => $filters['campaign'],
            'session_status' => $filters['session_status'],
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
            || $filters['path'] !== ''
            || $filters['goal_key'] !== ''
            || $filters['source'] !== ''
            || $filters['browser'] !== ''
            || $filters['platform'] !== ''
            || $filters['campaign'] !== ''
            || $filters['session_status'] !== ''
            || $filters['period'] !== 'day';
    }

    private function filteredEventsQuery(array $filters, ?string $eventType = null)
    {
        $query = AnalyticsEvent::query()
            ->whereBetween('occurred_at', [$filters['start'], $filters['end']])
            ->where('event_type', '!=', 'heartbeat');

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

        if ($filters['path'] !== '') {
            $query->where('path', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['path']) . '%');
        }

        if ($filters['goal_key'] !== '') {
            $query->where('goal_key', $filters['goal_key']);
        }

        if ($filters['browser'] !== '') {
            $query->where('browser', $filters['browser']);
        }

        if ($filters['platform'] !== '') {
            $query->where('platform', $filters['platform']);
        }

        if ($filters['campaign'] !== '') {
            $query->where(function ($nested) use ($filters) {
                $nested->where('utm_campaign', $filters['campaign'])
                    ->orWhere('utm_source', $filters['campaign']);
            });
        }

        if ($filters['source'] !== '') {
            $query->where(function ($nested) use ($filters) {
                $nested->where('referrer_type', $filters['source'])
                    ->orWhere('search_engine', $filters['source'])
                    ->orWhere('utm_source', $filters['source'])
                    ->orWhere('referrer_host', 'like', '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['source']) . '%');
            });
        }

        if ($filters['session_status'] !== '') {
            match ($filters['session_status']) {
                'new' => $query->where('session_num', 1),
                'returning' => $query->where('session_num', '>', 1),
                'bounce' => $query->where('is_bounce', true),
                'long' => $query->where('session_duration', '>=', 300),
                default => null,
            };
        }

        if ($filters['search'] !== '') {
            $search = '%' . str_replace(['%', '_'], ['\%', '\_'], $filters['search']) . '%';
            $query->where(function ($nested) use ($search) {
                $nested->where('path', 'like', $search)
                    ->orWhere('title', 'like', $search)
                    ->orWhere('search_term', 'like', $search)
                    ->orWhere('goal_label', 'like', $search)
                    ->orWhere('country_name', 'like', $search)
                    ->orWhere('city', 'like', $search)
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

    private function cachedLiveData(): array
    {
        $cacheKey = 'internal_analytics:live_data';
        $ttl = 10;

        return Cache::remember($cacheKey, $ttl, function () {
            $users = $this->liveUsers();
            $activeSeconds = collect($users)->map(function ($user) {
                if (empty($user['first_seen_at'])) {
                    return 0;
                }

                $first = Carbon::parse($user['first_seen_at']);
                $last = !empty($user['last_seen_at']) ? Carbon::parse($user['last_seen_at']) : now();

                return max(0, $first->diffInSeconds($last));
            });

            $sources = collect($users)
                ->groupBy(fn ($user) => (string) ($user['source_label'] ?? 'direct'))
                ->map(fn ($items, $label) => [
                    'label' => $label ?: 'direct',
                    'count' => $items->count(),
                ])
                ->sortByDesc('count')
                ->take(8)
                ->values()
                ->all();

            $browsers = collect($users)
                ->groupBy(fn ($user) => trim((string) (($user['browser'] ?? '-') . '|' . ($user['platform'] ?? '-'))))
                ->map(function ($items, $key) {
                    [$browser, $platform] = array_pad(explode('|', $key, 2), 2, '-');
                    return [
                        'browser' => $browser ?: '-',
                        'platform' => $platform ?: '-',
                        'count' => $items->count(),
                    ];
                })
                ->sortByDesc('count')
                ->values();

            $goalsLast10m = (int) AnalyticsEvent::query()
                ->where('event_type', 'goal')
                ->where('occurred_at', '>=', now()->subMinutes(10))
                ->count();

            return [
                'count' => count($users),
                'users' => $users,
                'map' => $this->liveCountryMap(),
                'sources' => $sources,
                'stats' => [
                    'pageviews_now' => (int) collect($users)->sum(fn ($user) => (int) ($user['pageviews'] ?? 0)),
                    'avg_active_seconds' => (int) round($activeSeconds->avg() ?: 0),
                    'top_source' => $sources[0]['label'] ?? '-',
                    'top_browser' => $browsers->first() ?: ['browser' => '-', 'platform' => '-', 'count' => 0],
                    'goals_last_10m' => $goalsLast10m,
                ],
            ];
        });
    }

    public function liveUsersCount(): int
    {
        return AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '>=', now()->subMinutes((int) $this->settings()['live_user_window_minutes']))
            ->count();
    }

    private function liveUsers(): array
    {
        $visitors = AnalyticsLiveVisitor::query()
            ->where('last_seen_at', '>=', now()->subMinutes((int) $this->settings()['live_user_window_minutes']))
            ->latest('last_seen_at')
            ->limit(30)
            ->get();

        if ($visitors->isEmpty()) {
            return [];
        }

        $sessionIds = $visitors->pluck('session_id')->filter()->values()->all();
        $latestEvents = AnalyticsEvent::query()
            ->whereIn('session_id', $sessionIds)
            ->orderByDesc('occurred_at')
            ->get()
            ->unique('session_id')
            ->keyBy('session_id');

        $sessions = AnalyticsSession::query()
            ->whereIn('session_id', $sessionIds)
            ->get()
            ->keyBy('session_id');

        return $visitors
            ->map(function (AnalyticsLiveVisitor $visitor) use ($latestEvents, $sessions) {
                $event = $latestEvents->get($visitor->session_id);
                $session = $sessions->get($visitor->session_id);
                $searchEngine = $event?->search_engine;
                $utmSource = $event?->utm_source;
                $utmMedium = $event?->utm_medium;
                $utmCampaign = $event?->utm_campaign;
                $referrerType = $event?->referrer_type ?: $session?->referrer_type;
                $referrerHost = $event?->referrer_host;
                $sourceLabel = $searchEngine
                    ?: $utmSource
                    ?: $referrerHost
                    ?: ($session?->landing_source ?: ($referrerType && $referrerType !== 'direct' ? $referrerType : 'direct'));

                return [
                'session_id' => $visitor->session_id,
                'path' => $visitor->path,
                'title' => $visitor->title,
                'product_id' => $visitor->product_id,
                'user_id' => $visitor->user_id,
                'user' => $visitor->user_id ? $this->userLabel((int) $visitor->user_id) : 'مهمان',
                'ip' => $visitor->ip_address ?: '-',
                'country' => $event?->country_name ?: $visitor->country_name ?: $event?->country_code ?: $visitor->country_code ?: '-',
                'city' => $event?->city ?: '-',
                'device' => $visitor->device_type ?: $event?->device_type ?: $session?->device_type ?: '-',
                'device_brand' => $visitor->device_brand ?: '-',
                'user_agent' => $visitor->user_agent ?: '-',
                'last_seen' => $visitor->last_seen_at?->diffForHumans(),
                'last_seen_at' => $visitor->last_seen_at?->toISOString(),
                'first_seen_at' => $visitor->first_seen_at?->toISOString(),
                'pageviews' => $visitor->pageviews_count,
                'referrer_type' => $referrerType,
                'referrer_host' => $referrerHost,
                'search_engine' => $searchEngine,
                'browser' => $event?->browser ?: $session?->browser ?: '-',
                'platform' => $event?->platform ?: $session?->platform ?: '-',
                'utm_source' => $utmSource,
                'utm_medium' => $utmMedium,
                'utm_campaign' => $utmCampaign,
                'source_label' => $sourceLabel,
            ];
            })
            ->all();
    }

    private function addIncrement(array &$increments, string $date, string $metric, string $dimensionKey, ?string $label, ?int $explicitCount = null): void
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

        $increments[$key]['count'] += $explicitCount ?? 1;
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
            'heartbeat' => 'ضربان',
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

    private function deviceBrand(MobileDetect $detect, string $userAgent): string
    {
        if ($detect->is('iPhone') || $detect->is('iPad') || $detect->is('Mac')) return 'Apple';
        if ($detect->is('Samsung')) return 'Samsung';
        if ($detect->is('Huawei')) return 'Huawei';
        if ($detect->is('Xiaomi')) return 'Xiaomi';
        if ($detect->is('OPPO')) return 'OPPO';
        if ($detect->is('vivo')) return 'Vivo';
        if ($detect->is('Google')) return 'Google';
        if ($detect->is('OnePlus')) return 'OnePlus';
        if ($detect->is('Sony')) return 'Sony';
        if ($detect->is('LG')) return 'LG';
        if ($detect->is('Nokia')) return 'Nokia';
        if ($detect->is('HTC')) return 'HTC';
        if ($detect->is('Lenovo')) return 'Lenovo';
        if ($detect->is('Motorola')) return 'Motorola';
        if ($detect->is('Asus')) return 'Asus';
        if (preg_match('/Windows/i', $userAgent)) return 'Windows PC';
        if (preg_match('/Linux/i', $userAgent)) return 'Linux PC';
        return 'Other';
    }

    private function browser(MobileDetect $detect, string $userAgent): string
    {
        if ($detect->is('Edge') || str_contains($userAgent, 'Edg/')) return 'Edge';
        if ($detect->is('Firefox') || str_contains($userAgent, 'Firefox')) return 'Firefox';
        if ($detect->is('Opera') || str_contains($userAgent, 'Opera|OPR')) return 'Opera';
        if ($detect->is('Brave') || str_contains($userAgent, 'Brave')) return 'Brave';
        if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg/') && !str_contains($userAgent, 'OPR')) return 'Chrome';
        if ($detect->is('Safari') || str_contains($userAgent, 'Safari')) return 'Safari';
        if (str_contains($userAgent, 'UCBrowser')) return 'UC Browser';
        if (str_contains($userAgent, 'SamsungBrowser')) return 'Samsung Internet';
        return 'Other';
    }

    private function platform(MobileDetect $detect, string $userAgent): string
    {
        if ($detect->is('iOS') || preg_match('/iPhone|iPad|iOS/i', $userAgent)) return 'iOS';
        if ($detect->is('AndroidOS') || preg_match('/Android/i', $userAgent)) return 'Android';
        if ($detect->is('Windows') || preg_match('/Windows/i', $userAgent)) return 'Windows';
        if ($detect->is('macOS') || preg_match('/Macintosh|Mac OS/i', $userAgent)) return 'macOS';
        if ($detect->is('Linux') || preg_match('/Linux/i', $userAgent)) return 'Linux';
        if ($detect->is('ChromeOS')) return 'ChromeOS';
        return 'Other';
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
                'city' => $event->city,
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
