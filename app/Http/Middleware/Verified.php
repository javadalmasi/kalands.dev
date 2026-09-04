<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Verified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::guard('web')->check()) {
            return $next($request);
        }

        $user = Auth::guard('web')->user();
        if (! $user->is_active) {
            Auth::guard('web')->logout();

            return redirect()->route('auth.login')->withErrors('حساب کاربری شما غیرفعال است.');
        }

        return $next($request);
    }
}
