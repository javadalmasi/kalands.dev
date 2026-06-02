<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Services\ActivityLogger;
use App\Services\Auth\AuthenticationService;
use App\Services\Auth\DashboardAuthKeyService;
use App\Services\Auth\JsChallengeService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function show(JsChallengeService $challengeService)
    {
        return view('auth.login', [
            'challenge' => $challengeService->issue('auth_login'),
        ]);
    }

    public function verify(
        LoginRequest $request,
        AuthenticationService $authenticationService,
        DashboardAuthKeyService $dashboardAuthKeyService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $result = $authenticationService->authenticate(
            $request->string('identifier')->toString(),
            $request->string('password')->toString(),
        );

        if (!$result) {
            return back()
                ->withErrors('اطلاعات ورود صحیح نیست.')
                ->withInput($request->except('password'));
        }

        $minutes = $request->boolean('remember') ? 1440 : 240;
        config(['session.lifetime' => $minutes]);

        $guard = $result['guard'];
        $guardName = $guard === 'admin' ? 'admin' : 'user';
        $account = $result['account'];

        Auth::guard($guard)->login($account, $request->boolean('remember'));
        $request->session()->regenerate();

        $authKey = $dashboardAuthKeyService->issue($account, $guardName, $minutes);

        if ($guardName === 'admin') {
            session()->put('2fa_checked_admin', false);
        } else {
            session()->put('2fa_checked_user', false);
        }

        $activityLogger->log('auth.login', $account, 'ورود موفق', [
            'guard' => $guard,
            'remember' => $request->boolean('remember'),
        ]);

        if (!empty($account->two_factor_secret)) {
            return redirect()->route('auth.2fa');
        }

        if ($guardName === 'admin') {
            return redirect()->route('dash.admin.index', ['authkey' => $authKey]);
        }

        return redirect()->route('dash.user.index', ['authkey' => $authKey]);
    }

    public function logout(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $dashboardAuthKeyService = app(DashboardAuthKeyService::class);

        if (Auth::guard('admin')->check()) {
            $activityLogger->log('auth.logout', Auth::guard('admin')->user(), 'خروج ادمین');
            Auth::guard('admin')->logout();
        }

        if (Auth::guard('web')->check()) {
            $activityLogger->log('auth.logout', Auth::guard('web')->user(), 'خروج کاربر');
            Auth::guard('web')->logout();
        }

        $dashboardAuthKeyService->forgetCookie();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('auth.login');
    }
}
