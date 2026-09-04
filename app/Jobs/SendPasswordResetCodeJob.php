<?php

namespace App\Jobs;

use App\Mail\PasswordResetCodeMail;
use App\Services\Communication\ChannelSettingsResolver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetCodeJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $replaceExisting = true;

    public function __construct(
        private readonly string $channel,
        private readonly string $destination,
        private readonly string $code
    ) {}

    public function uniqueId(): string
    {
        return $this->destination;
    }

    public function handle(ChannelSettingsResolver $channelSettingsResolver): void
    {
        if ($this->channel === 'email') {
            $channelSettingsResolver->applyTransactionalSmtp();
            Mail::to($this->destination)->send(new PasswordResetCodeMail($this->code));

            return;
        }

        $config = $channelSettingsResolver->resolveSms();
        $token = $config['api_token'] ?? null;

        if (! $token) {
            return;
        }

        $endpoint = rtrim((string) ($config['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp'), '/');

        Http::timeout(10)->post("{$endpoint}/{$token}", [
            'to' => $this->destination,
            'code' => $this->code,
            'from' => $config['sender_number'] ?? null,
        ]);
    }
}
