<?php

namespace App\Services\Communication;

use App\Repositories\SettingsRepository;

class ChannelSettingsResolver
{
    public function __construct(private readonly SettingsRepository $settingsRepository)
    {
    }

    public function applyTransactionalSmtp(): void
    {
        $transactional = $this->normalizeSmtpConfig($this->settingsRepository->get('smtp.transactional'));
        $general = $this->normalizeSmtpConfig($this->settingsRepository->get('smtp.general'));

        $this->applySmtp([
            'mailer' => $transactional['mailer'] ?: $general['mailer'] ?: 'smtp',
            'host' => $transactional['host'] ?: $general['host'] ?: config('mail.mailers.smtp.host'),
            'port' => $transactional['port'] ?: $general['port'] ?: config('mail.mailers.smtp.port'),
            'username' => $transactional['username'] ?: $general['username'] ?: config('mail.mailers.smtp.username'),
            'password' => $transactional['password'] ?: $general['password'] ?: config('mail.mailers.smtp.password'),
            'encryption' => $transactional['encryption'] ?: $general['encryption'] ?: config('mail.mailers.smtp.encryption'),
            'sender_email' => $transactional['sender_email'] ?: $general['sender_email'] ?: config('mail.from.address'),
            'sender_name' => $transactional['sender_name'] ?: $general['sender_name'] ?: config('mail.from.name'),
        ]);
    }

    public function applyGeneralSmtp(): void
    {
        $general = $this->normalizeSmtpConfig($this->settingsRepository->get('smtp.general'));

        $this->applySmtp([
            'mailer' => $general['mailer'] ?: 'smtp',
            'host' => $general['host'] ?: config('mail.mailers.smtp.host'),
            'port' => $general['port'] ?: config('mail.mailers.smtp.port'),
            'username' => $general['username'] ?: config('mail.mailers.smtp.username'),
            'password' => $general['password'] ?: config('mail.mailers.smtp.password'),
            'encryption' => $general['encryption'] ?: config('mail.mailers.smtp.encryption'),
            'sender_email' => $general['sender_email'] ?: config('mail.from.address'),
            'sender_name' => $general['sender_name'] ?: config('mail.from.name'),
        ]);
    }

    private function applySmtp(array $config): void
    {
        $mailer = $config['mailer'] ?? 'smtp';

        config(['mail.default' => $mailer]);

        if ($mailer === 'sendmail') {
            config([
                'mail.mailers.sendmail.transport' => 'sendmail',
                'mail.mailers.sendmail.path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
                'mail.from.address' => $config['sender_email'],
                'mail.from.name' => $config['sender_name'],
            ]);
            return;
        }

        config([
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $config['host'],
            'mail.mailers.smtp.port' => (int) $config['port'],
            'mail.mailers.smtp.username' => $config['username'],
            'mail.mailers.smtp.password' => $config['password'],
            'mail.mailers.smtp.encryption' => $config['encryption'],
            'mail.from.address' => $config['sender_email'],
            'mail.from.name' => $config['sender_name'],
        ]);
    }

    public function resolveSms(): array
    {
        $config = $this->settingsRepository->get('sms.melipayamak', []);

        return [
            'endpoint' => $config['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp',
            'api_token' => $config['api_token'] ?? config('services.melipayamak.key'),
            'sender_number' => $config['sender_number'] ?? null,
        ];
    }

    private function normalizeSmtpConfig(mixed $config): array
    {
        if (!is_array($config)) {
            return [
                'mailer' => null,
                'host' => null,
                'port' => null,
                'username' => null,
                'password' => null,
                'encryption' => null,
                'sender_email' => null,
                'sender_name' => null,
            ];
        }

        return [
            'mailer' => $config['mailer'] ?? null,
            'host' => $config['host'] ?? null,
            'port' => $config['port'] ?? null,
            'username' => $config['username'] ?? null,
            'password' => $config['password'] ?? null,
            'encryption' => $config['encryption'] ?? null,
            'sender_email' => $config['sender_email'] ?? null,
            'sender_name' => $config['sender_name'] ?? null,
        ];
    }
}
