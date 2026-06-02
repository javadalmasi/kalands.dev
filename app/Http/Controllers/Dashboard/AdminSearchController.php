<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Repositories\SettingsRepository;
use App\Services\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class AdminSearchController extends Controller
{
    public function search(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $q = trim((string) $request->input('q'));
        $filter = (string) $request->input('filter', 'all'); // all, modules, users, products, sections

        if (strlen($q) < 2) {
            return response()->json(['results' => []]);
        }

        $settings = $settingsRepository->get('search.settings', [
            'modules' => true,
            'users' => true,
            'products' => true,
            'sections' => true,
        ]);

        $results = [];

        // 1. Modules
        if (($filter === 'all' || $filter === 'modules') && ($settings['modules'] ?? true)) {
            $modules = $this->getModulesList();
            $matchedModules = collect($modules)->filter(function($m) use ($q) {
                return mb_stripos($m['label'], $q) !== false || mb_stripos($m['description'], $q) !== false;
            })->map(function($m) {
                return [
                    'type' => 'module',
                    'title' => $m['label'],
                    'description' => $m['description'],
                    'icon' => $m['icon'],
                    'url' => route('dash.admin.modules.show', ['authkey' => request()->route('authkey'), 'moduleKey' => $m['key']]),
                ];
            })->values()->toArray();

            if (!empty($matchedModules)) {
                $results['modules'] = [
                    'label' => 'ماژول‌ها',
                    'items' => $matchedModules
                ];
            }
        }

        // 2. Users
        if (($filter === 'all' || $filter === 'users') && ($settings['users'] ?? true)) {
            $users = User::query()
                ->where(function($query) use ($q) {
                    $query->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                })
                ->limit(10)
                ->get()
                ->map(function($u) {
                    return [
                        'type' => 'user',
                        'title' => $u->name,
                        'description' => $u->email ?? $u->phone,
                        'icon' => 'person',
                        'url' => route('dash.admin.users', ['authkey' => request()->route('authkey'), 'q' => $u->email ?? $u->phone]),
                    ];
                })->toArray();

            if (!empty($users)) {
                $results['users'] = [
                    'label' => 'کاربران',
                    'items' => $users
                ];
            }
        }

        // 3. Products
        if (($filter === 'all' || $filter === 'products') && ($settings['products'] ?? true)) {
            $products = Product::query()
                ->where('title', 'like', "%{$q}%")
                ->orWhere('id', 'like', "%{$q}%")
                ->limit(10)
                ->get()
                ->map(function($p) {
                    return [
                        'type' => 'product',
                        'title' => $p->title,
                        'description' => "شناسه: {$p->id} | فروشگاه: " . ($p->store === 'digikala' ? 'دیجی‌کالا' : 'باسلام'),
                        'icon' => 'inventory_2',
                        'url' => route('dash.admin.products', ['authkey' => request()->route('authkey'), 'q' => $p->id]),
                    ];
                })->toArray();

            if (!empty($products)) {
                $results['products'] = [
                    'label' => 'محصولات',
                    'items' => $products
                ];
            }
        }

        // 4. Sections (Static routes or pages)
        if (($filter === 'all' || $filter === 'sections') && ($settings['sections'] ?? true)) {
            $sections = [
                ['label' => 'مدیریت کاربران', 'description' => 'لیست و ویرایش کاربران سیستم', 'icon' => 'group', 'route' => 'dash.admin.users'],
                ['label' => 'مدیریت ادمین‌ها', 'description' => 'لیست و ویرایش ادمین‌های سیستم', 'icon' => 'admin_panel_settings', 'route' => 'dash.admin.admins'],
                ['label' => 'نقش‌ها و دسترسی‌ها', 'description' => 'مدیریت سطوح دسترسی', 'icon' => 'security', 'route' => 'dash.admin.roles'],
                ['label' => 'مدیریت محصولات', 'description' => 'لیست و وضعیت محصولات', 'icon' => 'inventory', 'route' => 'dash.admin.products'],
                ['label' => 'تیکت‌های پشتیبانی', 'description' => 'مرکز پاسخگویی به کاربران', 'icon' => 'confirmation_number', 'route' => 'dash.admin.tickets'],
                ['label' => 'نظرات کاربران', 'description' => 'تایید و مدیریت نظرات', 'icon' => 'forum', 'route' => 'dash.admin.comments'],
                ['label' => 'تنظیمات صف', 'description' => 'مدیریت تسک‌های پس‌زمینه', 'icon' => 'queue', 'route' => 'dash.admin.queues'],
            ];

            $matchedSections = collect($sections)->filter(function($s) use ($q) {
                return mb_stripos($s['label'], $q) !== false || mb_stripos($s['description'], $q) !== false;
            })->map(function($s) {
                return [
                    'type' => 'section',
                    'title' => $s['label'],
                    'description' => $s['description'],
                    'icon' => $s['icon'],
                    'url' => route($s['route'], ['authkey' => request()->route('authkey')]),
                ];
            })->values()->toArray();

            if (!empty($matchedSections)) {
                $results['sections'] = [
                    'label' => 'بخش‌های سایت',
                    'items' => $matchedSections
                ];
            }
        }

        return response()->json(['results' => $results]);
    }

    public function searchHub(Request $request, SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('search.settings', [
            'modules' => true,
            'users' => true,
            'products' => true,
            'sections' => true,
        ]);

        return view('dash.admin.search-hub', compact('settings'));
    }

    public function saveSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger)
    {
        $data = $request->validate([
            'modules' => ['nullable', 'boolean'],
            'users' => ['nullable', 'boolean'],
            'products' => ['nullable', 'boolean'],
            'sections' => ['nullable', 'boolean'],
        ]);

        $settings = [
            'modules' => (bool) $request->boolean('modules'),
            'users' => (bool) $request->boolean('users'),
            'products' => (bool) $request->boolean('products'),
            'sections' => (bool) $request->boolean('sections'),
        ];

        $settingsRepository->set('search.settings', $settings);
        $activityLogger->log('settings.search.update', auth('admin')->user(), 'بروزرسانی تنظیمات جستجو', $settings);

        return back()->with('message', 'تنظیمات جستجو با موفقیت ذخیره شد.');
    }

    private function getModulesList(): array
    {
        return [
            ['key' => 'communication_hub', 'label' => 'ماژول جامع ارتباطی', 'description' => 'مدیریت یکپارچه ایمیل و پیامک', 'icon' => 'settings_input_component'],
            ['key' => 'contact', 'label' => 'ماژول تماس با ما', 'description' => 'مدیریت پیام‌های دریافتی و تنظیمات اطلاعات تماس', 'icon' => 'contact_support'],
            ['key' => 'affiliate', 'label' => 'سیستم افیلیت', 'description' => 'تنظیمات لینک‌سازی و رهگیری افیلیت', 'icon' => 'link'],
            ['key' => 'file_manager', 'label' => 'مدیریت فایل‌ها', 'description' => 'مدیریت فایل‌ها در صفحه اختصاصی فایل منیجر', 'icon' => 'folder'],
            ['key' => 'home_items_management', 'label' => 'مدیریت آیتم‌های صفحه اصلی', 'description' => 'مدیریت یکپارچه اسلایدرها، بنرها و دسته‌بندی‌های صفحه اصلی', 'icon' => 'home_repair_service'],
            ['key' => 'email_templates', 'label' => 'تمپلیت ایمیل‌ها', 'description' => 'مدیریت تمپلیت، هدر/فوتر، پیش‌نمایش و متغیرها', 'icon' => 'drafts'],
            ['key' => 'queues', 'label' => 'مدیریت صف‌ها', 'description' => 'حالت پردازش، توکن Cron و گزارش اجراها', 'icon' => 'queue'],
            ['key' => 'comments', 'label' => 'ماژول نظرات', 'description' => 'مدیریت نظرات، گزارش‌گیری و تنظیمات ارسال نظر', 'icon' => 'forum'],
            ['key' => 'tickets', 'label' => 'ماژول تیکت', 'description' => 'مدیریت تیکت‌ها، دسته‌بندی‌ها و تنظیمات ارسال تیکت', 'icon' => 'confirmation_number'],
            ['key' => 'faq', 'label' => 'ماژول سوالات متداول', 'description' => 'مدیریت سوالات متداول سایت و نمایش در صفحه FAQ', 'icon' => 'quiz'],
            ['key' => 'analytics', 'label' => 'آنالیزور', 'description' => 'آمار بازدید، کاربران زنده، اهداف و محصولات پربازدید', 'icon' => 'analytics'],
            ['key' => 'geoip', 'label' => 'بروزرسانی GeoIP', 'description' => 'مدیریت دیتابیس‌های مکان‌دهی IP و گزارش بروزرسانی‌ها', 'icon' => 'public'],
            ['key' => 'robots', 'label' => 'فایل Robots.txt', 'description' => 'ویرایش و تست فایل robots.txt برای مدیریت دسترسی ربات‌ها', 'icon' => 'settings'],
            ['key' => 'object_cache', 'label' => 'مدیریت Object Cache', 'description' => 'تنظیمات درایور، پیشوند، تست اتصال، پاکسازی و مرور آیتم‌های کش لاراول', 'icon' => 'memory'],
        ];
    }
}
