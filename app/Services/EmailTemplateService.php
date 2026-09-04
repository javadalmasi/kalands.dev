<?php

namespace App\Services;

use App\Repositories\SettingsRepository;

class EmailTemplateService
{
    public function __construct(private readonly SettingsRepository $settingsRepository) {}

    public function catalog(): array
    {
        return [
            'password_reset_code' => [
                'label' => 'کد بازیابی رمز عبور',
                'description' => 'ارسال کد یکبارمصرف برای بازیابی رمز عبور',
                'variables' => ['code', 'app_name', 'support_email'],
                'default_subject' => 'کد بازیابی رمز عبور',
                'default_body_html' => '<h2>کد بازیابی رمز عبور</h2><p>برای ادامه فرایند بازیابی رمز عبور، این کد ۶ رقمی را وارد کنید:</p><p style="font-size:22px;font-weight:bold">{{code}}</p><p>این کد تا ۱۰ دقیقه معتبر است.</p>',
            ],
            'otp_code' => [
                'label' => 'ایمیل OTP',
                'description' => 'ارسال کد یکبارمصرف ورود/تایید',
                'variables' => ['code', 'app_name', 'support_email'],
                'default_subject' => 'کد تایید ورود',
                'default_body_html' => '<h2>کد تایید ورود</h2><p>کد تایید شما:</p><p style="font-size:22px;font-weight:bold">{{code}}</p>',
            ],
            'welcome_user' => [
                'label' => 'ایمیل خوش‌آمدگویی',
                'description' => 'ارسال پس از ثبت‌نام کاربر در سایت',
                'variables' => ['full_name', 'app_name', 'support_email'],
                'default_subject' => 'به {{app_name}} خوش آمدید',
                'default_body_html' => '<h2>سلام {{full_name}} عزیز</h2><p>به خانواده {{app_name}} خوش آمدید. حساب کاربری شما با موفقیت ایجاد شد.</p><p>از این پس می‌توانید از خدمات سایت ما استفاده نمایید.</p>',
            ],
            'ticket_created' => [
                'label' => 'ایمیل ثبت تیکت جدید',
                'description' => 'ارسال پس از ایجاد تیکت',
                'variables' => ['ticket_id', 'ticket_subject', 'app_name', 'user_name'],
                'default_subject' => 'تیکت جدید ثبت شد #{{ticket_id}}',
                'default_body_html' => '<h2>تیکت جدید ثبت شد</h2><p>کاربر: {{user_name}}</p><p>موضوع: {{ticket_subject}}</p>',
            ],
            'contact_form_received' => [
                'label' => 'ایمیل فرم تماس با ما',
                'description' => 'اعلان ثبت پیام تماس',
                'variables' => ['full_name', 'subject', 'app_name'],
                'default_subject' => 'پیام تماس جدید',
                'default_body_html' => '<h2>پیام تماس جدید</h2><p>از طرف: {{full_name}}</p><p>موضوع: {{subject}}</p>',
            ],
            'ticket_replied' => [
                'label' => 'پاسخ به تیکت',
                'description' => 'ارسال پس از پاسخ ادمین به تیکت کاربر',
                'variables' => ['ticket_id', 'ticket_subject', 'app_name', 'support_email'],
                'default_subject' => 'پاسخ جدید برای تیکت #{{ticket_id}}',
                'default_body_html' => '<h2>پاسخ جدید دریافت شد</h2><p>برای تیکت با موضوع "{{ticket_subject}}" پاسخ جدیدی ثبت شده است.</p><p>لطفا برای مشاهده پاسخ به پنل کاربری خود مراجعه کنید.</p>',
            ],
            'ticket_closed' => [
                'label' => 'بسته شدن تیکت',
                'description' => 'اطلاع‌رسانی بسته شدن تیکت',
                'variables' => ['ticket_id', 'ticket_subject', 'app_name'],
                'default_subject' => 'تیکت #{{ticket_id}} بسته شد',
                'default_body_html' => '<h2>تیکت شما بسته شد</h2><p>تیکت با موضوع "{{ticket_subject}}" به وضعیت بسته تغییر یافت.</p>',
            ],
            'contact_form_replied' => [
                'label' => 'پاسخ به پیام تماس',
                'description' => 'ارسال پاسخ برای پیام‌های بخش تماس با ما',
                'variables' => ['full_name', 'subject', 'reply_message', 'app_name'],
                'default_subject' => 'پاسخ به پیام شما: {{subject}}',
                'default_body_html' => '<h2>سلام {{full_name}}</h2><p>در پاسخ به پیام شما با موضوع "{{subject}}":</p><div style="background:#f1f5f9;padding:15px;border-radius:8px">{{reply_message}}</div>',
            ],
        ];
    }

    public function has(string $key): bool
    {
        return isset($this->catalog()[$key]);
    }

    public function sampleVariables(string $key): array
    {
        $base = [
            'app_name' => (string) config('app.name', 'Kalands'),
            'support_email' => (string) config('mail.from.address', 'support@example.com'),
        ];

        if ($key === 'password_reset_code') {
            return $base + ['code' => '123456'];
        }
        if ($key === 'otp_code') {
            return $base + ['code' => '654321'];
        }
        if ($key === 'welcome_user') {
            return $base + ['full_name' => 'کاربر نمونه'];
        }
        if ($key === 'ticket_created') {
            return $base + ['ticket_id' => '1427', 'ticket_subject' => 'مشکل ورود', 'user_name' => 'کاربر نمونه'];
        }
        if ($key === 'contact_form_received') {
            return $base + ['full_name' => 'کاربر نمونه', 'subject' => 'درخواست همکاری'];
        }
        if ($key === 'ticket_replied') {
            return $base + ['ticket_id' => '1427', 'ticket_subject' => 'مشکل در پرداخت'];
        }
        if ($key === 'ticket_closed') {
            return $base + ['ticket_id' => '1427', 'ticket_subject' => 'مشکل در پرداخت'];
        }
        if ($key === 'contact_form_replied') {
            return $base + ['full_name' => 'کاربر نمونه', 'subject' => 'درخواست همکاری', 'reply_message' => 'با سلام، درخواست شما بررسی شد و به زودی با شما تماس خواهیم گرفت.'];
        }

        return $base;
    }

    public function render(string $key, array $variables): array
    {
        $catalog = $this->catalog();
        abort_unless(isset($catalog[$key]), 404);

        $layout = $this->layout();
        $item = $this->item($key);
        $subject = $this->replaceVars($item['subject'] ?: $catalog[$key]['default_subject'], $variables);
        $body = $this->replaceVars($item['body_html'] ?: $catalog[$key]['default_body_html'], $variables);

        $headerHtml = $this->replaceVars((string) ($layout['header_html'] ?? ''), $variables);
        $footerHtml = $this->replaceVars((string) ($layout['footer_html'] ?? ''), $variables);
        $logoPath = trim((string) ($layout['logo_path'] ?? ''));
        $usefulLinks = is_array($layout['useful_links'] ?? null) ? $layout['useful_links'] : [];

        $logoHtml = '';
        if ($logoPath !== '') {
            $logoUrl = $this->resolvePublicPath($logoPath);
            $logoHtml = '<div style="margin-bottom:18px"><img src="'.e($logoUrl).'" alt="logo" style="max-height:48px"></div>';
        }

        $linksHtml = '';
        if ($usefulLinks !== []) {
            $links = [];
            foreach ($usefulLinks as $link) {
                $label = trim((string) ($link['label'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));
                if ($label === '' || $url === '') {
                    continue;
                }
                $links[] = '<a href="'.e($url).'" style="color:#2563eb;text-decoration:none;margin-inline:8px">'.e($label).'</a>';
            }
            if ($links !== []) {
                $linksHtml = '<div style="margin-top:12px">'.implode('', $links).'</div>';
            }
        }

        $html = '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($subject).'</title></head><body style="font-family:sans-serif;line-height:1.8;background:#f8fafc;padding:24px"><div style="max-width:720px;margin:0 auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:24px;color:#0f172a">'
            .$logoHtml
            .$headerHtml
            .'<div>'.$body.'</div>'
            .'<hr style="margin:24px 0;border:none;border-top:1px solid #e2e8f0">'
            .$footerHtml
            .$linksHtml
            .'</div></body></html>';

        return ['subject' => $subject, 'html' => $html];
    }

    public function item(string $key): array
    {
        $items = $this->settingsRepository->get('email.templates.items', []);
        $item = is_array($items[$key] ?? null) ? $items[$key] : [];

        return [
            'subject' => (string) ($item['subject'] ?? ''),
            'body_html' => (string) ($item['body_html'] ?? ''),
        ];
    }

    public function layout(): array
    {
        $layout = $this->settingsRepository->get('email.templates.layout', []);
        if (! is_array($layout)) {
            $layout = [];
        }

        return [
            'logo_path' => (string) ($layout['logo_path'] ?? ''),
            'header_html' => (string) ($layout['header_html'] ?? ''),
            'footer_html' => (string) ($layout['footer_html'] ?? ''),
            'useful_links' => is_array($layout['useful_links'] ?? null) ? $layout['useful_links'] : [],
        ];
    }

    private function replaceVars(string $template, array $variables): string
    {
        $replacements = [];
        foreach ($variables as $key => $value) {
            $replacements['{{'.$key.'}}'] = (string) $value;
        }

        return strtr($template, $replacements);
    }

    private function resolvePublicPath(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url('/'.ltrim($path, '/'));
    }
}
