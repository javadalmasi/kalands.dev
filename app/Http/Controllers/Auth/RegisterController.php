<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterStepOneRequest;
use App\Http\Requests\Auth\RegisterStepTwoRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\Auth\DashboardAuthKeyService;
use App\Services\Auth\JsChallengeService;
use App\Services\Auth\PasswordHashService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showStep1(JsChallengeService $challengeService)
    {
        return view('auth.register-step-1', [
            'challenge' => $challengeService->issue('auth_register_step1'),
            'stepOne' => session('register.step1', []),
        ]);
    }

    public function storeStep1(
        RegisterStepOneRequest $request,
        PasswordHashService $passwordHashService
    ): RedirectResponse {
        $password = $passwordHashService->make($request->string('password')->toString());
        session()->put('register.step1', [
            'first_name' => $request->string('first_name')->toString(),
            'last_name' => $request->string('last_name')->toString(),
            'identifier' => $request->string('identifier')->toString(),
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
        ]);

        return redirect()->route('auth.register.step2');
    }

    public function showStep2(JsChallengeService $challengeService)
    {
        if (! session()->has('register.step1')) {
            return redirect()->route('auth.register.step1');
        }

        return view('auth.register-step-2', [
            'challenge' => $challengeService->issue('auth_register_step2'),
            'stepOne' => session('register.step1'),
            'stepTwo' => session('register.step2', []),
        ]);
    }

    public function storeStep2(
        RegisterStepTwoRequest $request,
        DashboardAuthKeyService $dashboardAuthKeyService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        if (! session()->has('register.step1')) {
            return redirect()->route('auth.register.step1');
        }

        $stepOne = session('register.step1');

        $payload = [
            'first_name' => $stepOne['first_name'],
            'last_name' => $stepOne['last_name'],
            'password_hash' => $stepOne['password_hash'],
            'password_salt' => $stepOne['password_salt'],
            'theme_preference' => $request->input('theme_preference', 'light'),
            'profile_bio' => $request->input('profile_bio'),
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
            'is_active' => true,
        ];

        $identifier = $stepOne['identifier'];
        $identifierType = isPhoneOrEmail($identifier);
        if ($identifierType === 'email') {
            $payload['email'] = $identifier;
            $payload['email_verified_at'] = now();
        } else {
            $payload['phone'] = $identifier;
            $payload['phone_verified_at'] = now();
        }

        $user = User::query()->create($payload);

        Auth::guard('web')->login($user);
        session()->regenerate();
        $authKey = $dashboardAuthKeyService->issue($user, 'user', 240);

        session()->forget('register.step1');
        session()->forget('register.step2');

        $activityLogger->log('auth.register', $user, 'ثبت‌نام کاربر جدید');

        return redirect()->route('dash.user.index', ['authkey' => $authKey]);
    }

    public function backToStep1(): RedirectResponse
    {
        if (request()->hasAny(['theme_preference', 'profile_bio', 'marketing_opt_in'])) {
            session()->put('register.step2', request()->only(['theme_preference', 'profile_bio', 'marketing_opt_in']));
        }

        return redirect()->route('auth.register.step1');
    }
}
