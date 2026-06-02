<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Verify2FA
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check() && !Auth::guard('admin')->check()) {
            return $next($request);
        }

        if (request()->is('auth/2fa')) {
            return $next($request);
        }

        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if (!$admin->two_factor_secret) {
                return $next($request);
            }

            if (session('2fa_checked_admin', false)) {
                return $next($request);
            }

            return redirect(route('auth.2fa'));
        }

        $user = Auth::guard('web')->user();
        if (!$user->two_factor_secret) {
            return $next($request);
        }

        if (session('2fa_checked_user', false)) {
            return $next($request);
        }

        return redirect(route('auth.2fa'));
    }
}
