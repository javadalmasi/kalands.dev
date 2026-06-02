<?php

namespace App\Http\Middleware;

use App\Services\Auth\DashboardAuthKeyService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;
        $dashboardAuthKeyService = app(DashboardAuthKeyService::class);

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                if ($guard === 'admin') {
                    $key = $dashboardAuthKeyService->currentKey('admin') ?? Auth::guard('admin')->user()->dashboard_authkey;
                    if ($key) {
                        return redirect()->route('dash.admin.index', ['authkey' => $key]);
                    }
                } else {
                    $key = $dashboardAuthKeyService->currentKey('user') ?? Auth::guard('web')->user()->dashboard_authkey;
                    if ($key) {
                        return redirect()->route('dash.user.index', ['authkey' => $key]);
                    }
                }

                return redirect('/');
            }
        }

        return $next($request);
    }
}
