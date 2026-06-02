<?php

namespace App\Services\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class JsChallengeService
{
    public function issue(string $context): array
    {
        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $answer = $left + $right;
        $key = $context . ':' . Str::lower(Str::random(20));

        session()->put('js_challenge.' . $key, [
            'answer' => $answer,
            'context' => $context,
            'expires_at' => CarbonImmutable::now()->addMinutes(15)->toISOString(),
        ]);

        return [
            'key' => $key,
            'question' => "{$left} + {$right} = ?",
        ];
    }

    public function validate(string $key, string $answer, ?string $context = null): bool
    {
        $payload = session()->get('js_challenge.' . $key);
        if (!$payload) {
            return false;
        }

        $expiresAt = CarbonImmutable::parse($payload['expires_at']);
        if ($expiresAt->isPast()) {
            session()->forget('js_challenge.' . $key);
            return false;
        }

        if ($context && ($payload['context'] ?? null) !== $context) {
            return false;
        }

        $normalized = trim((string) enNumber($answer));
        $isValid = ctype_digit($normalized) && (int) $normalized === (int) $payload['answer'];

        if ($isValid) {
            session()->forget('js_challenge.' . $key);
        }

        return $isValid;
    }
}
