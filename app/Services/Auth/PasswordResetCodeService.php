<?php

namespace App\Services\Auth;

use App\Models\PasswordResetCode;
use Carbon\CarbonImmutable;

class PasswordResetCodeService
{
    public function issue(string $identifier): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PasswordResetCode::query()->updateOrCreate(
            ['identifier' => $identifier],
            [
                'code' => $code,
                'expires_at' => CarbonImmutable::now()->addMinutes(10),
            ]
        );

        return $code;
    }

    public function verify(string $identifier, string $code): bool
    {
        $normalized = trim((string) enNumber($code));

        $record = PasswordResetCode::query()
            ->where('identifier', $identifier)
            ->where('code', $normalized)
            ->where('expires_at', '>', now())
            ->first();

        return (bool) $record;
    }
}
