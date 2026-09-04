<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgetPasswordRequest;
use App\Jobs\SendPasswordResetCodeJob;
use App\Models\Admin;
use App\Models\User;
use App\Services\Auth\JsChallengeService;
use App\Services\Auth\PasswordResetCodeService;
use Illuminate\Http\RedirectResponse;

class ForgetPasswordController extends Controller
{
    public function show(JsChallengeService $challengeService)
    {
        return view('auth.forget', [
            'challenge' => $challengeService->issue('auth_forget'),
        ]);
    }

    public function sendCode(
        ForgetPasswordRequest $request,
        PasswordResetCodeService $passwordResetCodeService
    ): RedirectResponse {
        $identifier = $request->string('identifier')->toString();
        $type = isPhoneOrEmail($identifier);

        $exists = $type === 'email'
            ? User::query()->where('email', $identifier)->exists() || Admin::query()->where('email_address', $identifier)->exists()
            : User::query()->where('phone', $identifier)->exists() || Admin::query()->where('mobile_number', $identifier)->exists();

        if (! $exists) {
            return back()->withErrors('حسابی با این اطلاعات پیدا نشد.')->withInput();
        }

        $code = $passwordResetCodeService->issue($identifier);
        $channel = $type === 'email' ? 'email' : 'sms';
        SendPasswordResetCodeJob::dispatch($channel, $identifier, $code);

        return back()->with('message', 'کد ۶ رقمی بازیابی با موفقیت ارسال شد. این کد تا ۱۰ دقیقه معتبر است.');
    }
}
