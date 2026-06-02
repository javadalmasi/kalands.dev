<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFAController extends Controller
{
    public function show()
    {
        return view('auth.2fa');
    }

    public function verify(Request $request, TwoFactorService $twoFactorService)
    {
        $code = trim((string) enNumber((string) $request->input('code')));

        if (Auth::guard('admin')->check()) {
            $admin = Auth::guard('admin')->user();
            if ($admin->two_factor_secret && $twoFactorService->verify($code, $admin->two_factor_secret)) {
                session(['2fa_checked_admin' => true]);
                return redirect()->route('dash.admin.index', ['authkey' => $admin->dashboard_authkey]);
            }
        }

        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            if ($user->two_factor_secret && $twoFactorService->verify($code, $user->two_factor_secret)) {
                session(['2fa_checked_user' => true]);
                return redirect()->route('dash.user.index', ['authkey' => $user->dashboard_authkey]);
            }
        }

        return back()->withErrors('کد تایید نامعتبر است');
    }
}
