<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use App\Repositories\SettingsRepository;
use App\Services\Auth\DashboardAuthKeyService;
use App\Services\Auth\JsChallengeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function show(
        JsChallengeService $challengeService,
        DashboardAuthKeyService $dashboardAuthKeyService,
        SettingsRepository $settingsRepository
    ) {
        if (Auth::guard('web')->check()) {
            $authkey = $dashboardAuthKeyService->currentKey('user') ?? Auth::guard('web')->user()->dashboard_authkey;

            return redirect()->route('dash.user.tickets.create', [
                'authkey' => $authkey,
            ]);
        }

        $contactInfo = array_merge([
            'title' => 'اطلاعات تماس',
            'description' => 'برای ارتباط سریع‌تر با تیم پشتیبانی می‌توانید از فرم کنار صفحه استفاده کنید یا از راه‌های زیر با ما در تماس باشید.',
            'phone' => '021-00000000',
            'email' => 'support@example.com',
            'address' => 'تهران، ایران',
            'work_hours' => 'شنبه تا پنجشنبه، ۹ تا ۱۸',
            'map_iframe' => '',
            'show_title' => true,
            'show_description' => true,
            'show_phone' => true,
            'show_email' => true,
            'show_address' => true,
            'show_work_hours' => true,
            'show_map' => false,
            'show_info_box' => true,
            'enabled' => true,
        ], (array) $settingsRepository->get('contact.page_info', []));

        return view('contact.index', [
            'challenge' => $challengeService->issue('contact_form'),
            'contactInfo' => $contactInfo,
        ]);
    }

    public function store(StoreContactMessageRequest $request): RedirectResponse
    {
        ContactMessage::query()->create([
            'name' => strip_tags((string) $request->input('name')),
            'email' => $request->input('email'),
            'subject' => strip_tags((string) $request->input('subject')),
            'message' => strip_tags((string) $request->input('message')),
            'ip_address' => $request->ip(),
            'is_read' => false,
        ]);

        return back()->with('message', 'پیام شما با موفقیت ثبت شد.');
    }
}
