<?php

namespace App\Rules;

use App\Services\Auth\JsChallengeService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidJsChallenge implements ValidationRule
{
    public function __construct(private readonly string $context)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $key = request()->input('js_challenge_key');

        if (!$key) {
            $fail('چالش امنیتی معتبر نیست.');
            return;
        }

        $service = app(JsChallengeService::class);
        $isValid = $service->validate($key, (string) $value, $this->context);
        if (!$isValid) {
            $fail('پاسخ چالش امنیتی صحیح نیست.');
        }
    }
}
