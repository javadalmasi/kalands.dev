<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\Auth\TwoFactorService;
use Illuminate\Http\Request;

class TwoFAController extends Controller
{
    public function show(TwoFactorService $twoFactorService)
    {
        $user = auth()->user();
        $secret = $twoFactorService->generateSecret();

        $qr_code = $twoFactorService->qrInline(
            (string) config('app.name', 'Kalands'),
            (string) ($user->email ?? $user->phone ?? $user->name ?? 'user'),
            $secret
        );

        session(["2fa_secret" => $secret]);

        return view('profile.2fa', [
            'qr_code' => $qr_code,
            'setup_secret' => $secret,
        ]);
    }

    public function enable(Request $request, TwoFactorService $twoFactorService, ActivityLogger $activityLogger)
    {
        $secret = session("2fa_secret");
        $user = auth()->user();

        if ($secret && $twoFactorService->verify((string) $request->input('otp'), $secret)) {
            $user->update([
                'two_factor_secret' => $secret,
                'two_factor_recovery_codes' => $twoFactorService->generateRecoveryCodes(),
            ]);

            session(["2fa_checked_user" => true]);
            $activityLogger->log('2fa.enabled', $user, 'فعال‌سازی احراز دو مرحله‌ای');

            return redirect()->route('dash.user.2fa.show', ['authkey' => $user->dashboard_authkey]);
        }

        return back()->withErrors('کد وارد شده نادرست است');
    }

    public function disable(ActivityLogger $activityLogger)
    {
        $user = auth()->user();
        $user->update([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);
        session()->forget('2fa_checked_user');
        $activityLogger->log('2fa.disabled', $user, 'غیرفعال‌سازی احراز دو مرحله‌ای');

        return back();
    }
}
