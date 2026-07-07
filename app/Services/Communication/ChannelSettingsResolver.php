<?php

namespace App\Services\Communication;

use App\Repositories\SettingsRepository;

/**
 * Applies mail / SMS configuration to Laravel's runtime config.
 *
 * Data is stored in SettingsRepository under the key "mail.config".
 * For backward-compat the old "smtp.general" key is used as a fallback.
 *
 * Supported drivers: smtp | mailgun | sendmail | log
 */
class ChannelSettingsResolver
{
    public function __construct(private readonly SettingsRepository $settingsRepository) {}

    // ─────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────

    /** Primary entry-point — applies the unified mail.config to Laravel. */
    public function applyMailConfig(): void
    {
        $raw = $this->settingsRepository->get('mail.config');

        // Backward-compat: fall back to the old smtp.general key.
        if (empty($raw) || empty($raw['mailer'])) {
            $raw = $this->settingsRepository->get('smtp.general', []);
        }

        $this->applyDriver($this->normalize($raw));
    }

    /**
     * Backward-compat aliases — both now delegate to applyMailConfig().
     * Kept so that existing Jobs (e.g. SendPasswordResetCodeJob) still compile.
     */
    public function applyTransactionalSmtp(): void { $this->applyMailConfig(); }
    public function applyGeneralSmtp(): void       { $this->applyMailConfig(); }

    /** Returns SMS config, falling back to env/services config. */
    public function resolveSms(): array
    {
        $cfg = $this->settingsRepository->get('sms.melipayamak', []);

        return [
            'endpoint'      => $cfg['endpoint']      ?? 'https://console.melipayamak.com/api/send/otp',
            'api_token'     => $cfg['api_token']      ?? config('services.melipayamak.key'),
            'sender_number' => $cfg['sender_number']  ?? null,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────

    private function applyDriver(array $cfg): void
    {
        // Always set the global From address & name.
        config([
            'mail.from.address' => $cfg['sender_email'] ?: config('mail.from.address'),
            'mail.from.name'    => $cfg['sender_name']  ?: config('mail.from.name'),
        ]);

        match ($cfg['mailer']) {
            'sendmail' => config([
                'mail.default'                    => 'sendmail',
                'mail.mailers.sendmail.transport' => 'sendmail',
                'mail.mailers.sendmail.path'      => $cfg['sendmail_path']
                    ?: env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
            ]),

            'log' => config([
                'mail.default' => 'log',
            ]),

            default => config([   // smtp
                'mail.default'                   => 'smtp',
                'mail.mailers.smtp.transport'    => 'smtp',
                'mail.mailers.smtp.host'         => $cfg['host'],
                'mail.mailers.smtp.port'         => (int) ($cfg['port'] ?? 587),
                'mail.mailers.smtp.username'     => $cfg['username'],
                'mail.mailers.smtp.password'     => $cfg['password'],
                'mail.mailers.smtp.encryption'   => $cfg['encryption'] ?: null,
                'mail.mailers.smtp.verify_peer'  => (bool) ($cfg['verify_peer'] ?? true),
                'mail.mailers.smtp.timeout'      => 30,
            ]),
        };
    }

    private function normalize(mixed $raw): array
    {
        if (! is_array($raw)) {
            $raw = [];
        }

        return [
            'mailer'        => in_array($raw['mailer'] ?? '', ['smtp', 'sendmail', 'log']) ? $raw['mailer'] : 'smtp',
            // SMTP
            'host'          => $raw['host']          ?? null,
            'port'          => $raw['port']          ?? 587,
            'username'      => $raw['username']      ?? null,
            'password'      => $raw['password']      ?? null,
            'encryption'    => $raw['encryption']    ?? null,
            'verify_peer'   => $raw['verify_peer']   ?? true,
            // Sendmail
            'sendmail_path' => $raw['sendmail_path'] ?? null,
            // Shared
            'sender_email'  => $raw['sender_email']  ?? null,
            'sender_name'   => $raw['sender_name']   ?? null,
        ];
    }
}
