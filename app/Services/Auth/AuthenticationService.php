<?php

namespace App\Services\Auth;

use App\Models\Admin;
use App\Models\User;

class AuthenticationService
{
    public function authenticate(string $identifier, string $password): ?array
    {
        $passwordHashService = app(PasswordHashService::class);
        $normalized = trim((string) enNumber($identifier));
        $type = isPhoneOrEmail($normalized);

        if ($type === 'phone') {
            $normalized = normalizePhoneNumber($normalized);
        }

        $user = $this->findUser($normalized, $type);
        if ($user && $user->password_hash && $user->password_salt) {
            if ($passwordHashService->verify($password, $user->password_salt, $user->password_hash)) {
                return [
                    'guard' => 'web',
                    'account' => $user,
                ];
            }
        }

        $admin = $this->findAdmin($normalized, $type);
        if ($admin && $admin->password_hash && $admin->password_salt) {
            if ($passwordHashService->verify($password, $admin->password_salt, $admin->password_hash)) {
                return [
                    'guard' => 'admin',
                    'account' => $admin,
                ];
            }
        }

        return null;
    }

    private function findUser(string $identifier, string|bool $type): ?User
    {
        if ($type === 'email') {
            return User::query()->where('email', $identifier)->first();
        }

        if ($type === 'phone') {
            return User::query()->where('phone', $identifier)->first();
        }

        return null;
    }

    private function findAdmin(string $identifier, string|bool $type): ?Admin
    {
        $query = Admin::query();
        if ($type === 'email') {
            return $query->where('email_address', $identifier)->first();
        }

        if ($type === 'phone') {
            return $query->where('mobile_number', $identifier)->first();
        }

        return $query->where('username', $identifier)->first();
    }
}
