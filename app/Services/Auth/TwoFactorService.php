<?php

namespace App\Services\Auth;

use PragmaRX\Google2FAQRCode\Google2FA;

class TwoFactorService
{
    public function generateSecret(): string
    {
        return (new Google2FA())->generateSecretKey();
    }

    public function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(bin2hex(random_bytes(4)));
        }

        return $codes;
    }

    public function qrInline(string $issuer, string $label, string $secret): string
    {
        $qr = (new Google2FA())->getQRCodeInline($issuer, $label, $secret);

        if (!str_starts_with($qr, 'data:image') && str_contains($qr, '<svg')) {
            return 'data:image/svg+xml;base64,' . base64_encode($qr);
        }

        return $qr;
    }

    public function verify(string $code, string $secret): bool
    {
        return (new Google2FA())->verifyKey($secret, trim((string) enNumber($code)));
    }
}
