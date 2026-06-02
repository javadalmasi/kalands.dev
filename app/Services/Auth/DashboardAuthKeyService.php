<?php

namespace App\Services\Auth;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cookie;

class DashboardAuthKeyService
{
    private const AUTHKEY_COOKIE = 'dashboard_authkey';
    private const GUARD_COOKIE = 'dashboard_authkey_guard';

    public function issue(Model&Authenticatable $model, string $guard, int $sessionMinutes): string
    {
        $authKey = generateDashboardAuthKey();
        $expiresAt = CarbonImmutable::now()->addMinutes($sessionMinutes);

        $model->forceFill([
            'dashboard_authkey' => $authKey,
            'dashboard_authkey_expires_at' => $expiresAt,
        ])->save();

        session()->put("dashboard.{$guard}.authkey", $authKey);
        session()->put("dashboard.{$guard}.expires_at", $expiresAt->toISOString());
        $this->queueAuthKeyCookies($authKey, $guard, $sessionMinutes);

        return $authKey;
    }

    public function currentKey(string $guard): ?string
    {
        return session()->get("dashboard.{$guard}.authkey");
    }

    public function syncCookie(string $authKey, string $guard): void
    {
        $expiresAt = session()->get("dashboard.{$guard}.expires_at");
        $expiresMinutes = 240;

        if (is_string($expiresAt)) {
            $expiry = CarbonImmutable::parse($expiresAt);
            if ($expiry->isFuture()) {
                $expiresMinutes = max(1, CarbonImmutable::now()->diffInMinutes($expiry));
            }
        }

        $this->queueAuthKeyCookies($authKey, $guard, $expiresMinutes);
    }

    public function forgetCookie(): void
    {
        Cookie::queue(Cookie::forget(self::AUTHKEY_COOKIE));
        Cookie::queue(Cookie::forget(self::GUARD_COOKIE));
    }

    public function isValid(Model&Authenticatable $model, string $authKey): bool
    {
        if (!$model->dashboard_authkey || $model->dashboard_authkey !== $authKey) {
            return false;
        }

        $expiresAt = $model->dashboard_authkey_expires_at;

        if (!$expiresAt) {
            return false;
        }

        if (is_string($expiresAt)) {
            $expiresAt = CarbonImmutable::parse($expiresAt);
        }

        return $expiresAt->isFuture();
    }

    private function queueAuthKeyCookies(string $authKey, string $guard, int $minutes): void
    {
        $secure = $this->isSecureCookie();
        $domain = config('session.domain');

        Cookie::queue(cookie(
            self::AUTHKEY_COOKIE,
            $authKey,
            $minutes,
            '/',
            $domain,
            $secure,
            true,
            false,
            'strict'
        ));

        Cookie::queue(cookie(
            self::GUARD_COOKIE,
            $guard,
            $minutes,
            '/',
            $domain,
            $secure,
            true,
            false,
            'strict'
        ));
    }

    private function isSecureCookie(): bool
    {
        $sessionSecure = config('session.secure');
        if (is_bool($sessionSecure)) {
            return $sessionSecure;
        }

        return request()->isSecure();
    }
}
