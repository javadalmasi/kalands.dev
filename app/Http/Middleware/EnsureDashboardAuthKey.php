<?php

namespace App\Http\Middleware;

use App\Services\Auth\DashboardAuthKeyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureDashboardAuthKey
{
    public function handle(Request $request, Closure $next, string $guard = 'web'): Response
    {
        if (!Auth::guard($guard)->check()) {
            return redirect()->route('auth.login');
        }

        $authkey = (string) $request->route('authkey');
        $user = Auth::guard($guard)->user();
        $service = app(DashboardAuthKeyService::class);

        $sessionGuard = $guard === 'admin' ? 'admin' : 'user';
        $sessionKey = $service->currentKey($sessionGuard);
        $cookieAuthkey = (string) $request->cookie('dashboard_authkey');
        $cookieGuard = (string) $request->cookie('dashboard_authkey_guard');

        if (($cookieAuthkey !== '' && !hash_equals($cookieAuthkey, $authkey))
            || ($cookieGuard !== '' && $cookieGuard !== $sessionGuard)) {
            Auth::guard($guard)->logout();
            $service->forgetCookie();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors('نشست شما منقضی شده است. لطفا دوباره وارد شوید.');
        }

        if (!$service->isValid($user, $authkey)) {
            Auth::guard($guard)->logout();
            $service->forgetCookie();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors('نشست شما منقضی شده است. لطفا دوباره وارد شوید.');
        }

        if (!$sessionKey) {
            session()->put("dashboard.{$sessionGuard}.authkey", $authkey);
            session()->put("dashboard.{$sessionGuard}.expires_at", optional($user->dashboard_authkey_expires_at)?->toISOString());
            $service->syncCookie($authkey, $sessionGuard);
            return $next($request);
        }

        if ($sessionKey !== $authkey) {
            Auth::guard($guard)->logout();
            $service->forgetCookie();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')
                ->withErrors('نشست شما منقضی شده است. لطفا دوباره وارد شوید.');
        }

        $service->syncCookie($authkey, $sessionGuard);

        return $next($request);
    }
}
