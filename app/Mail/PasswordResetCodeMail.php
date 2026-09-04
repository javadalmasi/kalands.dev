<?php

namespace App\Mail;

use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(private readonly string $code) {}

    public function envelope(): Envelope
    {
        $rendered = app(EmailTemplateService::class)->render('password_reset_code', [
            'code' => $this->code,
            'app_name' => (string) config('app.name', 'Kalands'),
            'support_email' => (string) config('mail.from.address', 'support@example.com'),
        ]);

        return new Envelope(
            subject: $rendered['subject'],
        );
    }

    public function build(): static
    {
        $rendered = app(EmailTemplateService::class)->render('password_reset_code', [
            'code' => $this->code,
            'app_name' => (string) config('app.name', 'Kalands'),
            'support_email' => (string) config('mail.from.address', 'support@example.com'),
        ]);

        return $this->html($rendered['html']);
    }
}
