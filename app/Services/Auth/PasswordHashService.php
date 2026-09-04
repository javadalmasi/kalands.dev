<?php

namespace App\Services\Auth;

use Illuminate\Support\Facades\Hash;

class PasswordHashService
{
    public function make(string $plainPassword): array
    {
        $salt = bin2hex(random_bytes(16));
        $hash = Hash::make($plainPassword.$salt);

        return [
            'salt' => $salt,
            'hash' => $hash,
        ];
    }

    public function verify(string $plainPassword, string $salt, string $hash): bool
    {
        return Hash::check($plainPassword.$salt, $hash);
    }
}
