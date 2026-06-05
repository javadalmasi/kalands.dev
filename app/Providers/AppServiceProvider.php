<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use GeoIp2\Database\Reader;
use Livewire\Livewire;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use App\Models\ContactMessage;
use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        View::composer('components.layouts.admin-dashboard', function ($view) {
            $authkey = request()->route('authkey') ?? (auth('admin')->check() ? auth('admin')->user()->dashboard_authkey : '');

            $groups = [];
            $totalCount = 0;

            // Tickets
            $openTicketsCount = Ticket::query()->where('status', 'open')->count();
            if ($openTicketsCount > 0) {
                $totalCount += $openTicketsCount;
                $groups['tickets'] = [
                    'label' => 'تیکت‌های باز',
                    'count' => $openTicketsCount,
                    'icon' => 'confirmation_number',
                    'route' => route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'tickets', 'tab' => 'tab-moderation']),
                    'items' => Ticket::query()->where('status', 'open')->with('user')->latest()->limit(5)->get()->map(fn($t) => [
                        'title' => $t->subject,
                        'meta' => $t->user?->name ?? 'کاربر مهمان',
                        'time' => persianTimeAgo($t->created_at),
                        'route' => route('dash.admin.tickets.show', ['authkey' => $authkey, 'ticket' => $t->id])
                    ])
                ];
            }

            // Comments
            $pendingCommentsCount = Comment::query()->where('status', Comment::STATUS_PENDING)->count();
            if ($pendingCommentsCount > 0) {
                $totalCount += $pendingCommentsCount;
                $groups['comments'] = [
                    'label' => 'نظرات در انتظار بررسی',
                    'count' => $pendingCommentsCount,
                    'icon' => 'forum',
                    'route' => route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'comments', 'tab' => 'tab-moderation']),
                    'items' => Comment::query()->where('status', Comment::STATUS_PENDING)->with('user')->latest()->limit(5)->get()->map(fn($c) => [
                        'title' => Str::limit($c->content, 50),
                        'meta' => $c->user?->name ?? $c->name ?? 'ناشناس',
                        'time' => persianTimeAgo($c->created_at),
                        'route' => route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'comments', 'tab' => 'tab-moderation'])
                    ])
                ];
            }

            // Contact Messages
            $unreadContactCount = ContactMessage::query()->where('is_read', false)->count();
            if ($unreadContactCount > 0) {
                $totalCount += $unreadContactCount;
                $groups['contact'] = [
                    'label' => 'پیام‌های تماس جدید',
                    'count' => $unreadContactCount,
                    'icon' => 'contact_support',
                    'route' => route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'contact', 'tab' => 'tab-messages']),
                    'items' => ContactMessage::query()->where('is_read', false)->latest()->limit(5)->get()->map(fn($m) => [
                        'title' => $m->subject,
                        'meta' => $m->name,
                        'time' => persianTimeAgo($m->created_at),
                        'route' => route('dash.admin.modules.show', ['authkey' => $authkey, 'moduleKey' => 'contact', 'tab' => 'tab-messages'])
                    ])
                ];
            }

            $view->with([
                'adminNotificationCount' => $totalCount,
                'adminNotificationGroups' => $groups
            ]);
        });

        // Customize Livewire update route to avoid using 'livewire' in URL
        Livewire::setUpdateRoute(function ($handle) {
            return \Illuminate\Support\Facades\Route::post('/api/services/update', $handle)->middleware('web');
        });
        
        // Configure Livewire to handle errors silently
        Livewire::listen('component.dehydrate', function ($component, $response) {
            // Add error handling configuration to all Livewire components
            if (!isset($response->effects['html'])) {
                return;
            }
            
            // This is where we could add error handling logic if needed
        });

        Request::macro('isRobot', function () {
            // Skip GeoIP checks in local/development environments
            if (app()->environment('local', 'development', 'testing')) {
                // In development, return false to avoid GeoIP errors
                return false;
            }

            $intelligence = app(\App\Services\VisitorIntelligenceService::class)->getConfig();

            $userAgent = $this->header('User-Agent');
            $clientIp = $this->ip();

            // الگوی User-Agent برای ربات‌ها
			$robotsPattern = '/' . $intelligence['robots_pattern'] . '/i';

            if ($userAgent && preg_match($robotsPattern, $userAgent)) {
                return true;
            }

            // لیست ASN
            $trustedAsNumbers = $intelligence['trusted_asns'];

            try {
                $asnDatabasePath = storage_path('app/geoip/GeoLite2-ASN.mmdb');
                $readerAsn = new Reader($asnDatabasePath);

                if ($readerAsn->asn($clientIp)) {
                    $asn = $readerAsn->asn($clientIp)->autonomousSystemNumber;
                    if (in_array($asn, $trustedAsNumbers)) {
                        $readerAsn->close();
                        return true;
                    }
                }

                $readerAsn->close();
            } catch (\Exception $e) {
                \Log::warning('GeoLite2 ASN lookup failed: ' . $e->getMessage());
            }

            // بررسی کشور
            try {
                $countryDatabasePath = storage_path('app/geoip/GeoLite2-Country.mmdb');
                $readerCountry = new Reader($countryDatabasePath);

                $record = $readerCountry->country($clientIp);
                $countryIsoCode = $record->country->isoCode ?? null;

                $readerCountry->close();

                if ($countryIsoCode !== 'IR') {
                    return true;
                }
            } catch (\Exception $e) {
                \Log::warning('GeoLite2 Country lookup failed: ' . $e->getMessage());
                return true; // اگر خطا شد، به عنوان ربات در نظر بگیریم
            }

            return false;
        });

    }
}
