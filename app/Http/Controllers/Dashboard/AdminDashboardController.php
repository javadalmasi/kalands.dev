<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTicketMessageRequest;
use App\Jobs\Sitemap\ProcessSitemapChunkJob;
use App\Mail\GenericTemplateMail;
use App\Models\Admin;
use App\Models\AffiliateDailyStat;
use App\Models\AffiliateLink;
use App\Models\AiUsageLog;
use App\Models\ArtisanExecutionLog;
use App\Models\Category;
use App\Models\CategoryMapping;
use App\Models\Comment;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Permission;
use App\Models\Product;
use App\Models\QueueExecutionLog;
use App\Models\Role;
use App\Models\SitemapRunLog;
use App\Models\Ticket;
use App\Models\TicketCategory;
use App\Models\User;
use App\Repositories\SettingsRepository;
use App\Services\ActivityLogger;
use App\Services\Admin\FileManagerStorageService;
use App\Services\Auth\PasswordHashService;
use App\Services\CategoryMappingService;
use App\Services\CategoryService;
use App\Services\CategoryVectorService;
use App\Services\Communication\ChannelSettingsResolver;
use App\Services\EmailTemplateService;
use App\Services\GeoIPService;
use App\Services\InternalAnalyticsService;
use App\Services\Slider\HomeCategoryBannerStorage;
use App\Services\Slider\HomeItemsPayloadStorage;
use App\Services\Slider\SliderStorage;
use App\Services\VisitorIntelligenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AdminDashboardController extends Controller
{
    public function visitorIntelligenceHub(VisitorIntelligenceService $service)
    {
        $config = $service->getConfig();

        return view('dash.admin.visitor-intelligence-hub', compact('config'));
    }

    public function testUserAgent(Request $request, VisitorIntelligenceService $service): JsonResponse
    {
        $data = $request->validate([
            'user_agent' => ['required', 'string'],
        ]);

        $config = $service->getConfig();
        $pattern = $config['robots_pattern'] ?? '';

        $isRobot = false;
        if ($pattern) {
            try {
                $isRobot = (bool) preg_match('/'.$pattern.'/i', $data['user_agent']);
            } catch (\Throwable $e) {
            }
        }

        return response()->json([
            'is_robot' => $isRobot,
            'pattern' => $pattern,
        ]);
    }

    public function saveVisitorIntelligence(Request $request, VisitorIntelligenceService $service, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'robots_pattern' => ['required', 'string'],
            'trusted_asns' => ['required', 'string'],
        ]);

        // Support both comma-separated and newline-separated
        $asns = preg_split('/[\n,]+/', $data['trusted_asns']);
        $asns = array_map('trim', $asns);
        $asns = array_filter($asns, 'is_numeric');
        $asns = array_values(array_map('intval', $asns));

        $service->saveConfig([
            'robots_pattern' => $data['robots_pattern'],
            'trusted_asns' => $asns,
        ]);

        $activityLogger->log('settings.visitor_intelligence.update', auth('admin')->user(), 'بروزرسانی تنظیمات هوشمندی بازدیدکنندگان');

        return back()->with('message', 'تنظیمات هوشمندی با موفقیت ذخیره شد.');
    }

    public function fetchAsnData(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $asn = $request->input('asn');
        abort_unless($asn && is_numeric($asn), 400);

        try {
            $response = Http::timeout(10)->get("https://api.ipapi.is/?q=AS{$asn}");
            if ($response->successful()) {
                $data = $response->json();

                $stored = $settingsRepository->get('visitor_intelligence.asn_data', []);
                $stored[$asn] = [
                    'asn' => $data['asn'] ?? $asn,
                    'abuser_score' => $data['abuser_score'] ?? 'Unknown',
                    'descr' => $data['descr'] ?? 'N/A',
                    'country' => $data['country'] ?? 'N/A',
                    'org' => $data['org'] ?? 'N/A',
                    'domain' => $data['domain'] ?? 'N/A',
                    'type' => $data['type'] ?? 'N/A',
                    'updated_at' => now()->toIso8601String(),
                ];
                $settingsRepository->set('visitor_intelligence.asn_data', $stored);

                return response()->json(['ok' => true, 'data' => $stored[$asn]]);
            }
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }

        return response()->json(['ok' => false, 'error' => 'Failed to fetch data'], 500);
    }

    public function bulkUpdateAsnData(Request $request, VisitorIntelligenceService $service, SettingsRepository $settingsRepository): JsonResponse
    {
        $config = $service->getConfig();
        $asns = $config['trusted_asns'] ?? [];

        if (empty($asns)) {
            return response()->json(['ok' => true, 'count' => 0]);
        }

        $results = [];
        $stored = $settingsRepository->get('visitor_intelligence.asn_data', []);

        foreach ($asns as $asn) {
            try {
                $response = Http::timeout(5)->get("https://api.ipapi.is/?q=AS{$asn}");
                if ($response->successful()) {
                    $data = $response->json();
                    $stored[$asn] = [
                        'asn' => $data['asn'] ?? $asn,
                        'abuser_score' => $data['abuser_score'] ?? 'Unknown',
                        'descr' => $data['descr'] ?? 'N/A',
                        'country' => $data['country'] ?? 'N/A',
                        'org' => $data['org'] ?? 'N/A',
                        'domain' => $data['domain'] ?? 'N/A',
                        'type' => $data['type'] ?? 'N/A',
                        'updated_at' => now()->toIso8601String(),
                    ];
                    $results[] = $asn;
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $settingsRepository->set('visitor_intelligence.asn_data', $stored);

        return response()->json(['ok' => true, 'updated' => $results, 'count' => count($results)]);
    }

    public function index()
    {
        Context::add('view', 'admin.dashboard');

        return view('dash.admin.index', [
            'stats' => [
                'users' => User::query()->count(),
                'admins' => Admin::query()->count(),
                'pending_comments' => Comment::query()->where('status', Comment::STATUS_PENDING)->count(),
                'open_tickets' => Ticket::query()->where('status', 'open')->count(),
            ],
        ]);
    }

    public function users(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $sort = (string) $request->input('sort', 'latest');
        $usersQuery = User::with('roles')
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('first_name', 'like', "%{$q}%")
                        ->orWhere('last_name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            });

        if ($request->ajax()) {
            $users = $usersQuery->latest()->limit(10)->get()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                ];
            });

            return response()->json(['data' => $users]);
        }

        $users = $usersQuery
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'name_asc', fn ($query) => $query->orderBy('first_name')->orderBy('last_name'))
            ->when($sort === 'name_desc', fn ($query) => $query->orderByDesc('first_name')->orderByDesc('last_name'))
            ->when(! in_array($sort, ['oldest', 'name_asc', 'name_desc'], true), fn ($query) => $query->latest())
            ->paginate(20);

        return view('dash.admin.users', compact('users', 'q', 'sort'));
    }

    public function createUser()
    {
        return view('dash.admin.users-create');
    }

    public function storeUser(Request $request, PasswordHashService $passwordHashService, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'max:120'],
            'last_name' => ['required', 'max:120'],
            'email' => ['nullable', 'email', 'max:120', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^09[0-9]{9}$/', 'unique:users,phone'],
            'password' => ['required', 'min:8'],
            'is_active' => ['required', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $password = $passwordHashService->make($data['password']);

        $user = User::query()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
            'theme_preference' => 'light',
            'is_active' => (bool) $data['is_active'],
        ]);

        if (! empty($data['roles'])) {
            $user->syncRoles($data['roles']);
        }

        $activityLogger->log('admin.user.create', auth('admin')->user(), 'ایجاد کاربر جدید', [
            'user_id' => $user->id,
        ]);

        return redirect()->route('dash.admin.users', ['authkey' => request()->route('authkey')])
            ->with('message', 'کاربر جدید ایجاد شد.');
    }

    public function updateUser(string $authkey, Request $request, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => ['required', 'max:120'],
            'last_name' => ['required', 'max:120'],
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'regex:/^09[0-9]{9}$/'],
            'is_active' => ['required', 'boolean'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
        ]);

        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'is_active' => (bool) $data['is_active'],
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $activityLogger->log('admin.user.update', auth('admin')->user(), 'ویرایش کاربر', [
            'user_id' => $user->id,
        ]);

        return back()->with('message', 'اطلاعات کاربر بروزرسانی شد.');
    }

    public function updateUserPassword(
        string $authkey,
        Request $request,
        User $user,
        PasswordHashService $passwordHashService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $data = $request->validate([
            'password' => ['required', 'min:8'],
        ]);

        $password = $passwordHashService->make($data['password']);
        $user->update([
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
        ]);

        $activityLogger->log('admin.user.password.reset', auth('admin')->user(), 'تغییر رمز کاربر', [
            'user_id' => $user->id,
        ]);

        return back()->with('message', 'رمز عبور کاربر تغییر یافت.');
    }

    public function deleteUser(string $authkey, User $user, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($user->tickets()->exists()) {
            return back()->withErrors('به دلیل وجود تیکت‌های مرتبط، حذف این کاربر مجاز نیست.');
        }

        $userId = $user->id;
        $user->delete();

        $activityLogger->log('admin.user.delete', auth('admin')->user(), 'حذف کاربر', [
            'user_id' => $userId,
        ]);

        return back()->with('message', 'کاربر حذف شد.');
    }

    public function bulkUserAction(string $authkey, Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'action' => ['required', 'in:activate,deactivate,delete,assign_role'],
            'role_id' => ['required_if:action,assign_role', 'nullable', 'exists:roles,id'],
        ]);

        $ids = $data['user_ids'];
        $count = count($ids);

        if ($data['action'] === 'activate') {
            User::query()->whereIn('id', $ids)->update(['is_active' => true]);
            $activityLogger->log('admin.user.bulk.activate', auth('admin')->user(), 'فعالسازی دسته‌ای کاربران', ['count' => $count]);
        } elseif ($data['action'] === 'deactivate') {
            User::query()->whereIn('id', $ids)->update(['is_active' => false]);
            $activityLogger->log('admin.user.bulk.deactivate', auth('admin')->user(), 'غیرفعالسازی دسته‌ای کاربران', ['count' => $count]);
        } elseif ($data['action'] === 'delete') {
            // Check for tickets before bulk delete
            $safeIds = User::query()->whereIn('id', $ids)
                ->whereDoesntHave('tickets')
                ->pluck('id')
                ->toArray();

            $deletedCount = count($safeIds);
            User::query()->whereIn('id', $safeIds)->delete();

            $activityLogger->log('admin.user.bulk.delete', auth('admin')->user(), 'حذف دسته‌ای کاربران', ['count' => $deletedCount]);

            if ($deletedCount < $count) {
                return back()->with('message', "تعداد {$deletedCount} کاربر حذف شدند. برخی کاربران به دلیل داشتن تیکت حذف نشدند.");
            }
        } elseif ($data['action'] === 'assign_role') {
            $users = User::query()->whereIn('id', $ids)->get();
            foreach ($users as $user) {
                $user->roles()->syncWithoutDetaching([$data['role_id']]);
            }
            $activityLogger->log('admin.user.bulk.assign_role', auth('admin')->user(), 'تخصیص دسته‌ای نقش به کاربران', ['count' => $count, 'role_id' => $data['role_id']]);
        }

        return back()->with('message', 'عملیات گروهی با موفقیت انجام شد.');
    }

    public function admins()
    {
        $admins = Admin::with('roles')->latest()->paginate(20);

        return view('dash.admin.admins', compact('admins'));
    }

    public function storeAdmin(
        Request $request,
        PasswordHashService $passwordHashService,
        ActivityLogger $activityLogger
    ): RedirectResponse {
        $data = $request->validate([
            'full_name' => ['required', 'max:150'],
            'username' => ['required', 'max:120', 'unique:admins,username'],
            'email_address' => ['nullable', 'email', 'unique:admins,email_address'],
            'mobile_number' => ['nullable', 'regex:/^09[0-9]{9}$/', 'unique:admins,mobile_number'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'password' => ['required', 'min:8'],
        ]);

        $password = $passwordHashService->make($data['password']);

        $admin = Admin::query()->create([
            'full_name' => $data['full_name'],
            'username' => $data['username'],
            'email_address' => $data['email_address'] ?? null,
            'mobile_number' => $data['mobile_number'] ?? null,
            'password_hash' => $password['hash'],
            'password_salt' => $password['salt'],
            'is_active' => true,
        ]);

        if (! empty($data['roles'])) {
            $admin->syncRoles($data['roles']);
        }

        $activityLogger->log('admin.create', auth('admin')->user(), 'ایجاد ادمین جدید');

        return back()->with('message', 'ادمین جدید ایجاد شد.');
    }

    public function updateAdmin(string $authkey, Request $request, Admin $admin, ActivityLogger $activityLogger): RedirectResponse
    {
        if (auth('admin')->id() === $admin->id && ! auth('admin')->user()->hasPermission('roles.full')) {
            return back()->withErrors('امکان ویرایش این بخش برای ادمین فعلی محدود شده است.');
        }

        $data = $request->validate([
            'full_name' => ['required', 'max:150'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,id'],
            'is_active' => ['required', 'boolean'],
        ]);

        $admin->update([
            'full_name' => $data['full_name'],
            'is_active' => (bool) $data['is_active'],
        ]);

        $admin->syncRoles($data['roles'] ?? []);

        $activityLogger->log('admin.update', auth('admin')->user(), 'ویرایش ادمین', ['admin_id' => $admin->id]);

        return back()->with('message', 'ادمین بروزرسانی شد.');
    }

    public function deleteAdmin(string $authkey, Admin $admin, ActivityLogger $activityLogger): RedirectResponse
    {
        if (auth('admin')->id() === $admin->id) {
            return back()->withErrors('امکان حذف اکانت لاگین‌شده وجود ندارد.');
        }

        $admin->delete();
        $activityLogger->log('admin.delete', auth('admin')->user(), 'حذف ادمین', ['admin_id' => $admin->id]);

        return back()->with('message', 'ادمین حذف شد.');
    }

    public function roles()
    {
        $roles = Role::withCount(['admins', 'users'])->get();

        return view('dash.admin.roles', compact('roles'));
    }

    public function createRole()
    {
        $permissions = Permission::all()->groupBy('module');

        return view('dash.admin.roles-edit', compact('permissions'));
    }

    public function storeRole(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:100', 'unique:roles,name'],
            'label' => ['required', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        if (! empty($data['permissions'])) {
            $role->permissions()->sync($data['permissions']);
        }

        $activityLogger->log('admin.role.create', auth('admin')->user(), 'ایجاد نقش جدید', ['role_id' => $role->id]);

        return redirect()->route('dash.admin.roles', ['authkey' => request()->route('authkey')])
            ->with('message', 'نقش جدید ایجاد شد.');
    }

    public function editRole(string $authkey, Role $role)
    {
        $permissions = Permission::all()->groupBy('module');
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('dash.admin.roles-edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function updateRole(string $authkey, Request $request, Role $role, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:100', 'unique:roles,name,'.$role->id],
            'label' => ['required', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $role->update([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        $role->permissions()->sync($data['permissions'] ?? []);

        $activityLogger->log('admin.role.update', auth('admin')->user(), 'ویرایش نقش', ['role_id' => $role->id]);

        return redirect()->route('dash.admin.roles', ['authkey' => $authkey])
            ->with('message', 'نقش بروزرسانی شد.');
    }

    public function deleteRole(string $authkey, Role $role, ActivityLogger $activityLogger): RedirectResponse
    {
        if ($role->admins()->exists() || $role->users()->exists()) {
            return back()->withErrors('به دلیل وجود کاربران مرتبط، حذف این نقش مجاز نیست.');
        }

        $roleId = $role->id;
        $role->delete();

        $activityLogger->log('admin.role.delete', auth('admin')->user(), 'حذف نقش', ['role_id' => $roleId]);

        return back()->with('message', 'نقش حذف شد.');
    }

    public function queues(SettingsRepository $settingsRepository)
    {
        $currentDriver = env('QUEUE_CONNECTION', 'sync');
        $drivers = [
            'sync' => 'همزمان (Sync)',
            'database' => 'دیتابیس (Database)',
            'redis' => 'Redis',
            'beanstalkd' => 'Beanstalkd',
            'sqs' => 'Amazon SQS',
        ];

        $settings = $settingsRepository->get('queue.settings', [
            'mode' => 'cron',
            'cron_token' => Str::random(32),
            'queue_log_retention_days' => 7,
            'laravel_log_retention_days' => 14,
            'driver' => $currentDriver,
            'webservice_enabled' => true,
        ]);

        $lastRuns = QueueExecutionLog::query()->latest('executed_at')->limit(100)->get();

        $driverStatus = [
            'database' => Schema::hasTable('jobs'),
            'redis' => extension_loaded('redis'),
        ];

        return view('dash.admin.queues', compact('settings', 'lastRuns', 'drivers', 'currentDriver', 'driverStatus'));
    }

    public function saveQueueSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'mode' => ['required', 'in:cron,artisan'],
            'driver' => ['nullable', 'string', 'in:sync,database,redis,beanstalkd,sqs'],
            'queue_log_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'laravel_log_retention_days' => ['required', 'integer', 'min:1', 'max:365'],
            'webservice_enabled' => ['nullable', 'in:0,1'],
        ]);

        $current = $settingsRepository->get('queue.settings', []);
        $settingsRepository->set('queue.settings', [
            'mode' => $data['mode'],
            'cron_token' => $current['cron_token'] ?? Str::random(32),
            'queue_log_retention_days' => (int) $data['queue_log_retention_days'],
            'laravel_log_retention_days' => (int) $data['laravel_log_retention_days'],
            'driver' => $data['driver'] ?? $current['driver'] ?? 'sync',
            'webservice_enabled' => (bool) ($data['webservice_enabled'] ?? false),
        ]);

        if ($request->filled('driver')) {
            $this->writeEnvFile('QUEUE_CONNECTION', $data['driver']);
            putenv("QUEUE_CONNECTION={$data['driver']}");
            $_ENV['QUEUE_CONNECTION'] = $data['driver'];
            Artisan::call('config:clear');
        }

        $activityLogger->log('settings.queue.update', auth('admin')->user(), 'بروزرسانی تنظیمات صف', $data);

        return back()->with('message', 'تنظیمات صف ذخیره شد.');
    }

    public function regenerateQueueToken(SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $current = $settingsRepository->get('queue.settings', []);
        $current['cron_token'] = Str::random(32);
        $settingsRepository->set('queue.settings', $current);

        $activityLogger->log('settings.queue.token.regenerate', auth('admin')->user(), 'تولید توکن جدید صف');

        return back()->with('message', 'توکن صف بازتولید شد.');
    }

    public function smtpGeneral(SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('smtp.general', []);

        return view('dash.admin.smtp-general', compact('settings'));
    }

    public function saveSmtpGeneral(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'host' => ['required'],
            'port' => ['required', 'integer'],
            'username' => ['nullable'],
            'password' => ['nullable'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'sender_email' => ['required', 'email'],
            'sender_name' => ['required'],
        ]);

        $settingsRepository->set('smtp.general', $data);
        $activityLogger->log('settings.smtp.general.update', auth('admin')->user(), 'بروزرسانی SMTP عمومی');

        return back()->with('message', 'تنظیمات SMTP عمومی ذخیره شد.');
    }

    public function smtpTransactional(SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('smtp.transactional', []);

        return view('dash.admin.smtp-transactional', compact('settings'));
    }

    public function saveSmtpTransactional(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'host' => ['required'],
            'port' => ['required', 'integer'],
            'username' => ['nullable'],
            'password' => ['nullable'],
            'encryption' => ['nullable', 'in:tls,ssl'],
            'sender_email' => ['required', 'email'],
            'sender_name' => ['required'],
        ]);

        $settingsRepository->set('smtp.transactional', $data, true);
        $activityLogger->log('settings.smtp.transactional.update', auth('admin')->user(), 'بروزرسانی SMTP تراکنشی');

        return back()->with('message', 'تنظیمات SMTP تراکنشی ذخیره شد.');
    }

    public function smsConfig(SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('sms.melipayamak', []);

        return view('dash.admin.sms-config', compact('settings'));
    }

    public function modules(Request $request, SettingsRepository $settingsRepository, SliderStorage $sliderStorage, HomeCategoryBannerStorage $homeCategoryBannerStorage)
    {
        $modules = [
            [
                'key' => 'communication_hub',
                'label' => 'ماژول جامع ارتباطی',
                'description' => 'مدیریت یکپارچه ایمیل (عمومی/تراکنشی) و پیامک (SMS)',
                'icon' => 'hub',
                'permission' => 'communication.view',
                'category' => 'communication',
            ],
            [
                'key' => 'contact',
                'label' => 'ماژول تماس با ما',
                'description' => 'مدیریت پیام‌های دریافتی و تنظیمات اطلاعات تماس',
                'icon' => 'contact_support',
                'permission' => 'contact.view',
                'category' => 'communication',
            ],
            [
                'key' => 'affiliate',
                'label' => 'سیستم افیلیت',
                'description' => 'تنظیمات لینک‌سازی و رهگیری افیلیت',
                'icon' => 'link',
                'permission' => 'affiliate.view',
                'category' => 'communication',
            ],
            [
                'key' => 'file_manager',
                'label' => 'مدیریت فایل‌ها',
                'description' => 'مدیریت فایل‌ها در صفحه اختصاصی فایل منیجر',
                'icon' => 'folder',
                'permission' => 'file_manager.view',
                'category' => 'data',
            ],
            [
                'key' => 'home_items_management',
                'label' => 'مدیریت آیتم‌های صفحه اصلی',
                'description' => 'مدیریت یکپارچه اسلایدرها، بنرها و دسته‌بندی‌های صفحه اصلی',
                'icon' => 'home_repair_service',
                'permission' => 'home_items.view',
                'category' => 'content',
            ],
            [
                'key' => 'email_templates',
                'label' => 'تمپلیت ایمیل‌ها',
                'description' => 'مدیریت تمپلیت، هدر/فوتر، پیش‌نمایش و متغیرها',
                'icon' => 'drafts',
                'permission' => 'email_templates.view',
                'category' => 'communication',
            ],
            [
                'key' => 'queues',
                'label' => 'مدیریت صف‌ها',
                'description' => 'حالت پردازش، توکن Cron و گزارش اجراها',
                'icon' => 'queue',
                'permission' => 'queues.view',
                'category' => 'technical',
            ],
            [
                'key' => 'comments',
                'label' => 'ماژول نظرات',
                'description' => 'مدیریت نظرات، گزارش‌گیری و تنظیمات ارسال نظر',
                'icon' => 'forum',
                'permission' => 'comments.view',
                'category' => 'content',
            ],
            [
                'key' => 'tickets',
                'label' => 'ماژول تیکت',
                'description' => 'مدیریت تیکت‌ها، دسته‌بندی‌ها و تنظیمات ارسال تیکت',
                'icon' => 'confirmation_number',
                'permission' => 'tickets.view',
                'category' => 'content',
            ],
            [
                'key' => 'faq',
                'label' => 'ماژول سوالات متداول',
                'description' => 'مدیریت سوالات متداول سایت و نمایش در صفحه FAQ',
                'icon' => 'quiz',
                'permission' => 'faq.view',
                'category' => 'content',
            ],
            [
                'key' => 'analytics',
                'label' => 'آنالیزور',
                'description' => 'آمار بازدید، کاربران زنده، اهداف، محتوا و محصولات پربازدید',
                'icon' => 'analytics',
                'permission' => 'analytics.view',
                'category' => 'analytics',
            ],
            [
                'key' => 'geoip',
                'label' => 'بروزرسانی GeoIP',
                'description' => 'مدیریت دیتابیس‌های مکان‌دهی IP و گزارش بروزرسانی‌ها',
                'icon' => 'language',
                'permission' => 'geoip.view',
                'category' => 'technical',
            ],
            [
                'key' => 'robots',
                'label' => 'فایل Robots.txt',
                'description' => 'ویرایش و تست فایل robots.txt برای مدیریت دسترسی ربات‌ها',
                'icon' => 'settings_suggest',
                'permission' => 'robots.view',
                'category' => 'technical',
            ],
            [
                'key' => 'search',
                'label' => 'جستجوی هوشمند',
                'description' => 'تنظیمات جستجوی سریع در کل سیستم (ماژول‌ها، کاربران، محصولات)',
                'icon' => 'search',
                'permission' => 'search.view',
                'category' => 'technical',
            ],
            [
                'key' => 'megamenu',
                'label' => 'مدیریت مگا منو',
                'description' => 'مدیریت ویژوال آیتم‌های مگا منو با قابلیت درگ اند دراپ',
                'icon' => 'menu',
                'permission' => 'megamenu.view',
                'category' => 'content',
            ],
            [
                'key' => 'error_pages',
                'label' => 'مدیریت صفحات خطا',
                'description' => 'مدیریت لینک‌های کمکی و آیکون‌های نمایش داده شده در صفحات خطا',
                'icon' => 'report_problem',
                'permission' => 'error_pages.view',
                'category' => 'content',
            ],
            [
                'key' => 'cache_management',
                'label' => 'مدیریت کش',
                'description' => 'تنظیمات هدرهای کش وب‌سرویس‌ها و بهینه‌سازی وب‌سرور',
                'icon' => 'bolt',
                'permission' => 'cache_management.view',
                'category' => 'technical',
            ],
            [
                'key' => 'object_cache',
                'label' => 'مدیریت Object Cache',
                'description' => 'تنظیمات درایور، پیشوند، تست اتصال، پاکسازی و مرور آیتم‌های کش لاراول',
                'icon' => 'memory',
                'permission' => 'object_cache.view',
                'category' => 'technical',
            ],
            [
                'key' => 'visitor_intelligence',
                'label' => 'هوشمندی بازدیدکنندگان',
                'description' => 'مدیریت الگوهای تشخیص ربات، خزنده‌ها و ASNهای معتبر',
                'icon' => 'psychology',
                'permission' => 'geoip.full',
                'category' => 'technical',
            ],
            [
                'key' => 'artisan_commands',
                'label' => 'دستورات Artisan',
                'description' => 'اجرای دستورات کاربردی Artisan مانند پاکسازی کش، اجرای migration و ...',
                'icon' => 'terminal',
                'permission' => 'dashboard.view',
                'category' => 'technical',
            ],
            [
                'key' => 'categories',
                'label' => 'مدیریت دسته‌بندی‌ها',
                'description' => 'مدیریت درخت دسته‌بندی محصولات و نگاشت هوشمند بین فروشگاه‌ها',
                'icon' => 'category',
                'permission' => 'dashboard.view',
                'category' => 'data',
            ],
            [
                'key' => 'sitemap',
                'label' => 'مدیریت سایت مپ',
                'description' => 'تولید خودکار sitemap.xml با پشتیبانی از ایندکس چندبخشی، فشرده‌سازی gzip و پردازش افزایشی',
                'icon' => 'map',
                'permission' => 'sitemap.view',
                'category' => 'technical',
            ],
            [
                'key' => 'indexnow',
                'label' => 'ایندکس‌سازی (IndexNow)',
                'description' => 'ارسال خودکار محصولات به بینگ و یاندکس با برنامه‌ریزی ساعتی و کنترل نرخ',
                'icon' => 'publish',
                'permission' => 'indexnow.view',
                'category' => 'technical',
            ],
        ];

        $settings = [
            'communication_hub' => [
                'smtp_general' => $settingsRepository->get('smtp.general', []),
                'smtp_transactional' => $settingsRepository->get('smtp.transactional', []),
                'sms' => $settingsRepository->get('sms.melipayamak', []),
                'test_defaults' => $settingsRepository->get('communication.test_defaults', ['email' => '', 'phone' => '']),
            ],
            'contact' => $settingsRepository->get('contact.page_info', []),
            'affiliate' => $settingsRepository->get('affiliate.basalam', []),
            'file_manager' => $settingsRepository->get('file_manager.storage', [
                'enabled' => true,
                'root_path' => 'uploads',
                'cdn_base_url' => '',
            ]),
            'home_items_management' => [
                'slider' => $sliderStorage->loadByModule('home_main_banners'),
                'banners_categories' => $homeCategoryBannerStorage->load(),
            ],
            'email_templates' => [],
            'queues' => $settingsRepository->get('queue.settings', []),
        ];

        $fileManager = new FileManagerStorageService($settings['file_manager']);
        if ($request->query('path') !== null) {
            $fileExplorer = $fileManager->browse((string) $request->query('path', ''));
        } else {
            $fileExplorer = $fileManager->browse('');
        }

        $modules = collect($modules)->filter(function ($m) {
            return auth('admin')->user()->hasPermission($m['permission']);
        })->values()->toArray();

        $groupLabels = [
            'communication' => 'ارتباطات',
            'content' => 'مدیریت محتوا',
            'data' => 'داده‌ها و فایل‌ها',
            'technical' => 'فنی و بهینه‌سازی',
            'analytics' => 'تحلیل و آمار',
        ];
        $groupIcons = [
            'communication' => 'hub',
            'content' => 'article',
            'data' => 'folder_open',
            'technical' => 'settings',
            'analytics' => 'analytics',
        ];
        $grouped = collect($modules)->groupBy('category');

        return view('dash.admin.modules', compact('modules', 'settings', 'fileExplorer', 'groupLabels', 'groupIcons', 'grouped'));
    }

    public function moduleSettings(string $authkey, string $moduleKey, Request $request, SettingsRepository $settingsRepository, EmailTemplateService $emailTemplateService, SliderStorage $sliderStorage, HomeCategoryBannerStorage $homeCategoryBannerStorage)
    {
        $modules = [
            'communication_hub' => ['label' => 'ماژول جامع ارتباطی', 'description' => 'تنظیمات یکپارچه ارسال ایمیل و پیامک', 'icon' => 'hub', 'permission' => 'communication.view'],
            'contact' => ['label' => 'ماژول تماس با ما', 'description' => 'مدیریت پیام‌های دریافتی و تنظیمات اطلاعات تماس', 'icon' => 'contact_support', 'permission' => 'contact.view'],
            'affiliate' => ['label' => 'سیستم افیلیت', 'description' => 'تنظیمات لینک‌سازی و رهگیری افیلیت', 'icon' => 'link', 'permission' => 'affiliate.view'],
            'file_manager' => ['label' => 'مدیریت فایل‌ها', 'description' => 'مدیریت فایل‌ها در صفحه اختصاصی فایل منیجر', 'icon' => 'folder', 'permission' => 'file_manager.view'],
            'home_items_management' => ['label' => 'مدیریت آیتم‌های صفحه اصلی', 'description' => 'مدیریت یکپارچه اسلایدرها، بنرها و دسته‌بندی‌های صفحه اصلی', 'icon' => 'home_repair_service', 'permission' => 'home_items.view'],
            'email_templates' => ['label' => 'تمپلیت ایمیل‌ها', 'description' => 'مدیریت تمپلیت، هدر/فوتر، پیش‌نمایش و متغیرها', 'icon' => 'drafts', 'permission' => 'email_templates.view'],
            'queues' => ['label' => 'مدیریت صف‌ها', 'description' => 'حالت پردازش، توکن Cron و گزارش اجراها', 'icon' => 'queue', 'permission' => 'queues.view'],
            'comments' => ['label' => 'ماژول نظرات', 'description' => 'مدیریت نظرات، گزارش‌گیری و تنظیمات ارسال نظر', 'icon' => 'forum', 'permission' => 'comments.view'],
            'tickets' => ['label' => 'ماژول تیکت', 'description' => 'مدیریت تیکت‌ها، دسته‌بندی‌ها و تنظیمات ارسال تیکت', 'icon' => 'confirmation_number', 'permission' => 'tickets.view'],
            'faq' => ['label' => 'ماژول سوالات متداول', 'description' => 'مدیریت سوالات متداول سایت و نمایش در صفحه FAQ', 'icon' => 'quiz', 'permission' => 'faq.view'],
            'analytics' => ['label' => 'آنالیزور', 'description' => 'آمار بازدید، کاربران زنده، اهداف، محتوا و محصولات پربازدید', 'icon' => 'analytics', 'permission' => 'analytics.view'],
            'geoip' => ['label' => 'بروزرسانی GeoIP', 'description' => 'مدیریت دیتابیس‌های مکان‌دهی IP و گزارش بروزرسانی‌ها', 'icon' => 'language', 'permission' => 'geoip.view'],
            'robots' => ['label' => 'فایل Robots.txt', 'description' => 'ویرایش و تست فایل robots.txt برای مدیریت دسترسی ربات‌ها', 'icon' => 'settings_suggest', 'permission' => 'robots.view'],
            'search' => ['label' => 'جستجوی هوشمند', 'description' => 'تنظیمات جستجوی سریع در کل سیستم (ماژول‌ها، کاربران، محصولات)', 'icon' => 'search', 'permission' => 'search.view'],
            'megamenu' => ['label' => 'مدیریت مگا منو', 'description' => 'مدیریت ویژوال آیتم‌های مگا منو با قابلیت درگ اند دراپ', 'icon' => 'menu', 'permission' => 'megamenu.view'],
            'error_pages' => ['label' => 'مدیریت صفحات خطا', 'description' => 'مدیریت لینک‌های کمکی و آیکون‌های نمایش داده شده در صفحات خطا', 'icon' => 'report_problem', 'permission' => 'error_pages.view'],
            'cache_management' => ['label' => 'مدیریت کش', 'description' => 'تنظیمات هدرهای کش وب‌سرویس‌ها و بهینه‌سازی وب‌سرور', 'icon' => 'bolt', 'permission' => 'cache_management.view'],
            'object_cache' => ['label' => 'مدیریت Object Cache', 'description' => 'تنظیمات درایور، پیشوند، تست اتصال، پاکسازی و مرور آیتم‌های کش لاراول', 'icon' => 'memory', 'permission' => 'object_cache.view'],
            'visitor_intelligence' => ['label' => 'هوشمندی بازدیدکنندگان', 'description' => 'مدیریت الگوهای تشخیص ربات، خزنده‌ها و ASNهای معتبر', 'icon' => 'psychology', 'permission' => 'geoip.full'],
            'artisan_commands' => ['label' => 'دستورات Artisan', 'description' => 'اجرای دستورات کاربردی Artisan مانند پاکسازی کش، اجرای migration و ...', 'icon' => 'terminal', 'permission' => 'dashboard.view'],
            'categories' => ['label' => 'مدیریت دسته‌بندی‌ها', 'description' => 'مدیریت درخت دسته‌بندی محصولات و نگاشت هوشمند', 'icon' => 'category', 'permission' => 'dashboard.view'],
            'sitemap' => ['label' => 'مدیریت سایت مپ', 'description' => 'تولید خودکار sitemap.xml با پشتیبانی از ایندکس چندبخشی، فشرده‌سازی gzip و پردازش افزایشی', 'icon' => 'map', 'permission' => 'sitemap.view'],
            'indexnow' => ['label' => 'ایندکس‌سازی (IndexNow)', 'description' => 'ارسال خودکار محصولات به بینگ و یاندکس با برنامه‌ریزی ساعتی و کنترل نرخ', 'icon' => 'publish', 'permission' => 'indexnow.view'],
        ];

        abort_unless(isset($modules[$moduleKey]), 404);

        if (isset($modules[$moduleKey]['permission'])) {
            if (! auth('admin')->user()->hasPermission($modules[$moduleKey]['permission'])) {
                abort(403, 'شما دسترسی لازم برای مشاهده این ماژول را ندارید.');
            }
        }

        $settings = [
            'communication_hub' => [
                'smtp_general' => $settingsRepository->get('smtp.general', []),
                'smtp_transactional' => $settingsRepository->get('smtp.transactional', []),
                'sms' => $settingsRepository->get('sms.melipayamak', []),
                'test_defaults' => $settingsRepository->get('communication.test_defaults', ['email' => '', 'phone' => '']),
            ],
            'contact' => $settingsRepository->get('contact.page_info', []),
            'affiliate' => $settingsRepository->get('affiliate.basalam', []),
            'file_manager' => $settingsRepository->get('file_manager.storage', [
                'enabled' => true, 'root_path' => 'uploads', 'cdn_base_url' => '',
            ]),
            'home_items_management' => [
                'slider' => $sliderStorage->loadByModule('home_main_banners'),
                'banners_categories' => $homeCategoryBannerStorage->load(),
            ],
            'email_templates' => [],
            'queues' => $settingsRepository->get('queue.settings', []),
        ];

        $fileManager = new FileManagerStorageService($settings['file_manager'] ?? [
            'enabled' => true,
            'root_path' => 'uploads',
            'cdn_base_url' => '',
        ]);
        $fileExplorer = $fileManager->browse((string) $request->query('path', ''));

        if ($moduleKey === 'email_templates') {
            return view('dash.admin.email-templates', [
                'templateCatalog' => $emailTemplateService->catalog(),
                'templates' => $settingsRepository->get('email.templates.items', []),
                'layout' => $settingsRepository->get('email.templates.layout', []),
                'fileManagerSettings' => $settingsRepository->get('file_manager.storage', ['root_path' => 'uploads']),
                'activeKey' => (string) $request->query('template', 'password_reset_code'),
            ]);
        }

        if ($moduleKey === 'communication_hub') {
            return view('dash.admin.communication', [
                'settings' => $settings['communication_hub'],
                'authkey' => $authkey,
            ]);
        }

        if ($moduleKey === 'home_items_management') {
            return view('dash.admin.home-items', [
                'settings' => $settings['home_items_management'],
                'fileManagerSettings' => $settings['file_manager'] ?? ['root_path' => 'uploads', 'cdn_base_url' => ''],
                'authkey' => $authkey,
            ]);
        }

        if ($moduleKey === 'affiliate') {
            return $this->affiliateSettings($request, $settingsRepository);
        }

        if ($moduleKey === 'queues') {
            return $this->queues($settingsRepository);
        }

        if ($moduleKey === 'contact') {
            return $this->contactHub($request, $settingsRepository);
        }

        if ($moduleKey === 'comments') {
            return $this->commentsHub($request, $settingsRepository);
        }

        if ($moduleKey === 'tickets') {
            return $this->ticketsHub($request, $settingsRepository);
        }

        if ($moduleKey === 'faq') {
            return $this->faqHub($request);
        }

        if ($moduleKey === 'analytics') {
            return $this->analyticsHub(app(InternalAnalyticsService::class));
        }

        if ($moduleKey === 'geoip') {
            return $this->geoipHub($request, $settingsRepository);
        }

        if ($moduleKey === 'robots') {
            return $this->robotsHub($request);
        }

        if ($moduleKey === 'search') {
            return app(AdminSearchController::class)->searchHub($request, $settingsRepository);
        }

        if ($moduleKey === 'file_manager') {
            return $this->fileManagerHub($request, $settingsRepository);
        }

        if ($moduleKey === 'megamenu') {
            return $this->megamenuHub($request, $settingsRepository);
        }

        if ($moduleKey === 'error_pages') {
            return $this->errorPagesHub($request, $settingsRepository);
        }

        if ($moduleKey === 'object_cache') {
            return $this->objectCacheHub($request, $settingsRepository);
        }

        if ($moduleKey === 'cache_management') {
            return $this->cacheManagementHub($request, $settingsRepository);
        }

        if ($moduleKey === 'visitor_intelligence') {
            return $this->visitorIntelligenceHub(app(VisitorIntelligenceService::class));
        }

        if ($moduleKey === 'artisan_commands') {
            return $this->artisanCommandsHub();
        }

        if ($moduleKey === 'categories') {
            return $this->categoriesHub($request);
        }

        if ($moduleKey === 'sitemap') {
            return $this->sitemapHub();
        }

        if ($moduleKey === 'indexnow') {
            return app(\App\Http\Controllers\Dashboard\IndexNowController::class)->hub($request);
        }

        return view('dash.admin.module-settings', [
            'moduleKey' => $moduleKey,
            'module' => $modules[$moduleKey],
            'settings' => $settings,
            'fileExplorer' => $fileExplorer,
        ]);
    }

    public function fileManagerExplore(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $settings = $settingsRepository->get('file_manager.storage', [
            'enabled' => true,
            'root_path' => 'uploads',
        ]);
        $service = new FileManagerStorageService($settings);

        return response()->json($service->browse((string) $request->input('path', '')));
    }

    public function fileManagerCreateFolder(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'path' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:120'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);
        $service->createDirectory((string) ($data['path'] ?? ''), (string) $data['name']);

        return response()->json(['ok' => true]);
    }

    public function fileManagerHub(Request $request, SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('file_manager.storage', [
            'enabled' => true,
            'root_path' => 'uploads',
            'cdn_base_url' => '',
        ]);

        return view('dash.admin.file-manager-hub', [
            'settings' => $settings,
        ]);
    }

    public function fileManagerSettingsSave(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['boolean'],
            'root_path' => ['required', 'string', 'max:100'],
            'cdn_base_url' => ['nullable', 'url', 'max:255'],
        ]);

        $settings = [
            'enabled' => (bool) ($data['enabled'] ?? false),
            'root_path' => $data['root_path'],
            'cdn_base_url' => $data['cdn_base_url'] ?? '',
        ];

        $settingsRepository->set('file_manager.storage', $settings);

        $activityLogger->log('admin.file_manager.settings', auth('admin')->user(), 'بروزرسانی تنظیمات مدیریت فایل');

        return back()->with('success', 'تنظیمات با موفقیت ذخیره شد.');
    }

    public function fileManagerUpload(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'path' => ['nullable', 'string', 'max:500'],
            'files' => ['required', 'array', 'min:1'],
            'files.*' => ['file', 'image', 'max:10240'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);

        foreach ($request->file('files', []) as $file) {
            if (! $file) {
                continue;
            }
            $service->storeUploadedImage($file, (string) ($data['path'] ?? ''));
        }

        return response()->json(['ok' => true]);
    }

    public function syncAllCategories(CategoryMappingService $mappingService): JsonResponse
    {
        $count = $mappingService->syncAll();

        return response()->json(['ok' => true, 'count' => $count]);
    }

    public function getLinkedCategories(): JsonResponse
    {
        $mappings = CategoryMapping::with(['digikalaCategory', 'sourceCategory'])->get();

        $linked = $mappings->groupBy('digikala_category_id')->map(function ($group) {
            $dk = $group->first()->digikalaCategory;

            return [
                'digikala' => $dk,
                'links' => $group->map(fn ($m) => [
                    'category' => $m->sourceCategory,
                    'confidence' => $m->confidence,
                    'is_manual' => $m->is_manual,
                ]),
            ];
        })->values();

        return response()->json(['linked' => $linked]);
    }

    public function fileManagerDelete(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'paths' => ['required', 'array', 'min:1'],
            'paths.*' => ['required', 'string', 'max:500'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);
        foreach ($data['paths'] as $path) {
            $service->deletePath((string) $path);
        }

        return response()->json(['ok' => true]);
    }

    public function fileManagerRename(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:500'],
            'new_name' => ['required', 'string', 'max:200'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);
        $newPath = $service->renamePath((string) $data['path'], (string) $data['new_name']);

        return response()->json(['ok' => true, 'path' => $newPath]);
    }

    public function fileManagerMove(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:500'],
            'target_directory' => ['nullable', 'string', 'max:500'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);
        $newPath = $service->movePath((string) $data['path'], (string) ($data['target_directory'] ?? ''));

        return response()->json(['ok' => true, 'path' => $newPath]);
    }

    public function fileManagerCopy(Request $request, SettingsRepository $settingsRepository): JsonResponse
    {
        $data = $request->validate([
            'path' => ['required', 'string', 'max:500'],
            'target_directory' => ['nullable', 'string', 'max:500'],
        ]);
        $settings = $settingsRepository->get('file_manager.storage', ['enabled' => true, 'root_path' => 'uploads']);
        $service = new FileManagerStorageService($settings);
        $newPath = $service->copyPath((string) $data['path'], (string) ($data['target_directory'] ?? ''));

        return response()->json(['ok' => true, 'path' => $newPath]);
    }

    public function saveSmsConfig(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url'],
            'api_token' => ['required'],
            'sender_number' => ['nullable'],
        ]);

        $settingsRepository->set('sms.melipayamak', $data, true);
        $activityLogger->log('settings.sms.update', auth('admin')->user(), 'بروزرسانی تنظیمات پیامک');

        return back()->with('message', 'تنظیمات پیامک ذخیره شد.');
    }

    public function saveHomeSliderSettings(Request $request, SliderStorage $sliderStorage, HomeCategoryBannerStorage $homeCategoryBannerStorage, HomeItemsPayloadStorage $homeItemsPayloadStorage, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:180'],
            'status' => ['nullable', 'boolean'],
            'config_json' => ['nullable', 'string'],
            'desktop_config_json' => ['required', 'string'],
            'mobile_config_json' => ['required', 'string'],
            'desktop_slides_json' => ['required', 'string'],
            'mobile_slides_json' => ['required', 'string'],
        ]);

        $config = json_decode((string) ($data['config_json'] ?? '[]'), true);
        $desktopConfig = json_decode((string) $data['desktop_config_json'], true);
        $mobileConfig = json_decode((string) $data['mobile_config_json'], true);
        $desktopSlides = json_decode((string) $data['desktop_slides_json'], true);
        $mobileSlides = json_decode((string) $data['mobile_slides_json'], true);
        if (! is_array($config) || ! is_array($desktopConfig) || ! is_array($mobileConfig) || ! is_array($desktopSlides) || ! is_array($mobileSlides)) {
            return back()->withErrors('ساختار تنظیمات اسلایدر معتبر نیست.');
        }

        try {
            $sliderStorage->saveModuleSlider('home_main_banners', [
                'title' => (string) ($data['title'] ?? 'Home Main Banners'),
                'status' => (bool) ($data['status'] ?? false),
                'config' => $config,
                'desktop_config' => $desktopConfig,
                'mobile_config' => $mobileConfig,
                'desktop_slides' => $desktopSlides,
                'mobile_slides' => $mobileSlides,
            ]);

            $slider = $sliderStorage->loadByModule('home_main_banners');
            $homeCategoryBanners = $homeCategoryBannerStorage->load();
            $oldPayloadUrl = (string) (($slider['config']['payload_url'] ?? null) ?: ($homeCategoryBanners['payload_url'] ?? null));
            $payloadUrl = $homeItemsPayloadStorage->publish($slider, $homeCategoryBanners, $oldPayloadUrl);
            $sliderStorage->setModulePayloadUrl('home_main_banners', $payloadUrl);
            $homeCategoryBannerStorage->setPayloadUrl($payloadUrl);
        } catch (\RuntimeException $exception) {
            return back()->withErrors($exception->getMessage());
        }

        $activityLogger->log('settings.home.slider.update', auth('admin')->user(), 'بروزرسانی اسلایدر صفحه اصلی');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'تنظیمات اسلایدر ذخیره شد.']);
        }

        return back()->with('message', 'تنظیمات اسلایدر ذخیره شد.');
    }

    public function saveHomeBannerCategorySettings(Request $request, HomeCategoryBannerStorage $homeCategoryBannerStorage, SliderStorage $sliderStorage, HomeItemsPayloadStorage $homeItemsPayloadStorage, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'banners_json' => ['nullable', 'string'],
            'categories_json' => ['nullable', 'string'],
            'categories_top' => ['nullable', 'array'],
            'categories_bottom' => ['nullable', 'array'],
            'banners_enabled' => ['nullable', 'boolean'],
        ]);

        $payload = [];
        $payload['banners_enabled'] = $request->boolean('banners_enabled');
        if ($request->filled('banners_json')) {
            $payload['banners'] = json_decode((string) $data['banners_json'], true);
        }
        if ($request->filled('categories_json')) {
            $payload['categories'] = json_decode((string) $data['categories_json'], true);
        }
        if ($request->has('categories_top')) {
            $payload['categories_top'] = $data['categories_top'];
        }
        if ($request->has('categories_bottom')) {
            $payload['categories_bottom'] = $data['categories_bottom'];
        }

        $homeCategoryBannerStorage->save($payload);

        $slider = $sliderStorage->loadByModule('home_main_banners');
        $homeCategoryBanners = $homeCategoryBannerStorage->load();
        $oldPayloadUrl = (string) (($homeCategoryBanners['payload_url'] ?? null) ?: ($slider['config']['payload_url'] ?? null));
        $payloadUrl = $homeItemsPayloadStorage->publish($slider, $homeCategoryBanners, $oldPayloadUrl);
        $sliderStorage->setModulePayloadUrl('home_main_banners', $payloadUrl);
        $homeCategoryBannerStorage->setPayloadUrl($payloadUrl);

        $activityLogger->log('settings.home.category_banners.update', auth('admin')->user(), 'بروزرسانی بنرها و دسته‌بندی‌های صفحه اصلی');

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'تنظیمات بنرها و دسته‌بندی‌ها ذخیره شد.']);
        }

        return back()->with('message', 'تنظیمات بنرها و دسته‌بندی‌ها ذخیره شد.');
    }

    public function saveCommunicationDefaults(Request $request, SettingsRepository $settingsRepository): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'email'],
            'phone' => ['nullable', 'regex:/^09[0-9]{9}$/'],
        ]);
        $settingsRepository->set('communication.test_defaults', $data);

        return back()->with('message', 'مقادیر پیش‌فرض تست ذخیره شد.');
    }

    public function sendSmtpGeneralTest(Request $request, ChannelSettingsResolver $channelSettingsResolver): JsonResponse
    {
        $data = $request->validate(['to' => ['required', 'email']]);
        try {
            $channelSettingsResolver->applyGeneralSmtp();
            $mailable = new GenericTemplateMail('تست SMTP عمومی', '<p>ارسال آزمایشی SMTP عمومی موفق بود.</p>');
            $mailable->shouldQueue = false;
            Mail::mailer('smtp')->to($data['to'])->send($mailable);

            return response()->json(['ok' => true, 'message' => 'ایمیل تست SMTP عمومی ارسال شد.']);
        } catch (\Throwable $e) {
            Log::error('SMTP General Test Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['ok' => false, 'error' => 'ارسال ایمیل تست با خطا مواجه شد.'], 500);
        }
    }

    public function sendSmtpTransactionalTest(Request $request, ChannelSettingsResolver $channelSettingsResolver): JsonResponse
    {
        $data = $request->validate(['to' => ['required', 'email']]);
        try {
            $channelSettingsResolver->applyTransactionalSmtp();
            $mailable = new GenericTemplateMail('تست SMTP تراکنشی', '<p>ارسال آزمایشی SMTP تراکنشی موفق بود.</p>');
            $mailable->shouldQueue = false;
            Mail::mailer('smtp')->to($data['to'])->send($mailable);

            return response()->json(['ok' => true, 'message' => 'ایمیل تست SMTP تراکنشی ارسال شد.']);
        } catch (\Throwable $e) {
            Log::error('SMTP Transactional Test Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['ok' => false, 'error' => 'ارسال ایمیل تست با خطا مواجه شد.'], 500);
        }
    }

    public function sendSmsTest(Request $request, ChannelSettingsResolver $channelSettingsResolver): JsonResponse
    {
        $data = $request->validate([
            'to' => ['required', 'regex:/^09[0-9]{9}$/'],
            'message' => ['nullable', 'max:200'],
        ]);
        try {
            $config = $channelSettingsResolver->resolveSms();
            $token = $config['api_token'] ?? null;
            if (! $token) {
                throw new \Exception('توکن پیامک تنظیم نشده است.');
            }
            $endpoint = rtrim((string) ($config['endpoint'] ?? 'https://console.melipayamak.com/api/send/otp'), '/');
            $response = Http::timeout(10)->post("{$endpoint}/{$token}", [
                'to' => $data['to'],
                'code' => $data['message'] ?? 'SMS test from admin panel',
                'from' => $config['sender_number'] ?? null,
            ]);

            if ($response->failed()) {
                throw new \Exception('خطا در فراخوانی وب‌سرویس: '.$response->body());
            }

            return response()->json(['ok' => true, 'message' => 'پیامک تست ارسال شد.']);
        } catch (\Throwable $e) {
            Log::error('SMS Test Error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json(['ok' => false, 'error' => 'ارسال پیامک تست با خطا مواجه شد.'], 500);
        }
    }

    public function emailTemplates(SettingsRepository $settingsRepository, EmailTemplateService $emailTemplateService)
    {
        return view('dash.admin.email-templates', [
            'templateCatalog' => $emailTemplateService->catalog(),
            'templates' => $settingsRepository->get('email.templates.items', []),
            'layout' => $settingsRepository->get('email.templates.layout', []),
            'fileManagerSettings' => $settingsRepository->get('file_manager.storage', [
                'root_path' => 'uploads',
            ]),
        ]);
    }

    public function saveEmailTemplateLayout(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'logo_path' => ['nullable', 'max:500'],
            'header_html' => ['nullable', 'string', 'max:5000'],
            'footer_html' => ['nullable', 'string', 'max:5000'],
            'useful_links' => ['nullable', 'array'],
            'useful_links.*.label' => ['nullable', 'max:120'],
            'useful_links.*.url' => ['nullable', 'max:500'],
        ]);
        $settingsRepository->set('email.templates.layout', $data);
        $activityLogger->log('settings.email.templates.layout.update', auth('admin')->user(), 'بروزرسانی هدر و فوتر تمپلیت ایمیل');

        return back()->with('message', 'هدر/فوتر ایمیل ذخیره شد.');
    }

    public function saveEmailTemplate(string $authkey, string $key, Request $request, SettingsRepository $settingsRepository, EmailTemplateService $emailTemplateService, ActivityLogger $activityLogger): RedirectResponse
    {
        abort_unless($emailTemplateService->has($key), 404);
        $data = $request->validate([
            'subject' => ['required', 'max:255'],
            'body_html' => ['required', 'string', 'max:20000'],
        ]);
        $templates = $settingsRepository->get('email.templates.items', []);
        $templates[$key] = $data;
        $settingsRepository->set('email.templates.items', $templates);
        $activityLogger->log('settings.email.templates.item.update', auth('admin')->user(), 'بروزرسانی تمپلیت ایمیل', ['key' => $key]);

        return back()->with('message', 'تمپلیت ایمیل ذخیره شد.');
    }

    public function previewEmailTemplate(string $authkey, string $key, EmailTemplateService $emailTemplateService)
    {
        abort_unless($emailTemplateService->has($key), 404);
        $preview = $emailTemplateService->render($key, $emailTemplateService->sampleVariables($key));

        return response($preview['html']);
    }

    public function comments(Request $request)
    {
        return redirect()->route('dash.admin.modules.show', [
            'authkey' => request()->route('authkey'),
            'moduleKey' => 'comments',
            'tab' => 'tab-moderation',
        ]);
    }

    public function setCommentStatus(string $authkey, Comment $comment, string $status, ActivityLogger $activityLogger): RedirectResponse
    {
        if (! in_array($status, [Comment::STATUS_APPROVED, Comment::STATUS_REJECTED, Comment::STATUS_SPAM], true)) {
            abort(422);
        }

        $comment->update(['status' => $status]);
        $activityLogger->log('admin.comment.status.update', auth('admin')->user(), 'تغییر وضعیت نظر', [
            'comment_id' => $comment->id,
            'status' => $status,
        ]);

        return back()->with('message', 'وضعیت نظر بروزرسانی شد.');
    }

    public function deleteComment(string $authkey, Comment $comment, ActivityLogger $activityLogger): RedirectResponse
    {
        $commentId = $comment->id;
        $comment->delete();
        $activityLogger->log('admin.comment.delete', auth('admin')->user(), 'حذف نظر', [
            'comment_id' => $commentId,
        ]);

        return back()->with('message', 'نظر حذف شد.');
    }

    public function bulkCommentAction(string $authkey, Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'comment_ids' => ['required', 'array', 'min:1'],
            'comment_ids.*' => ['integer', 'exists:comments,id'],
            'action' => ['required', 'in:approved,rejected,spam,delete'],
        ]);

        $ids = $data['comment_ids'];
        if ($data['action'] === 'delete') {
            Comment::query()->whereIn('id', $ids)->delete();
        } else {
            Comment::query()->whereIn('id', $ids)->update(['status' => $data['action']]);
        }

        $activityLogger->log('admin.comment.bulk', auth('admin')->user(), 'اقدام گروهی روی نظرات', [
            'action' => $data['action'],
            'count' => count($ids),
        ]);

        return back()->with('message', 'اقدام گروهی روی نظرات انجام شد.');
    }

    public function storeTicketCategory(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:100'],
            'slug' => ['required', 'max:100', 'unique:ticket_categories,slug'],
        ]);

        $category = TicketCategory::query()->create([
            'name' => $data['name'],
            'slug' => Str::slug($data['slug']),
            'is_active' => true,
        ]);

        $activityLogger->log('admin.ticket.category.create', auth('admin')->user(), 'ایجاد دسته‌بندی تیکت', [
            'category_id' => $category->id,
        ]);

        return back()->with('message', 'دسته‌بندی تیکت ایجاد شد.');
    }

    public function toggleTicketCategory(string $authkey, TicketCategory $category, ActivityLogger $activityLogger): RedirectResponse
    {
        $category->update(['is_active' => ! $category->is_active]);
        $activityLogger->log('admin.ticket.category.toggle', auth('admin')->user(), 'تغییر وضعیت دسته‌بندی تیکت', [
            'category_id' => $category->id,
            'is_active' => $category->is_active,
        ]);

        return back()->with('message', 'وضعیت دسته‌بندی تغییر کرد.');
    }

    public function showTicket(string $authkey, Ticket $ticket, SettingsRepository $settingsRepository)
    {
        $ticket->load(['user', 'category', 'messages.user', 'messages.admin']);
        $blockedUsers = $settingsRepository->get('tickets.blocked_users', []);
        $isUserBlocked = in_array($ticket->user_id, $blockedUsers);

        return view('dash.admin.ticket-show', compact('ticket', 'isUserBlocked'));
    }

    public function replyTicket(string $authkey, StoreTicketMessageRequest $request, Ticket $ticket, ActivityLogger $activityLogger): RedirectResponse
    {
        $ticket->messages()->create([
            'sender_type' => 'admin',
            'sender_id' => auth('admin')->id(),
            'message' => (string) $request->input('message'),
        ]);

        $ticket->update(['status' => 'answered']);
        $activityLogger->log('admin.ticket.reply', auth('admin')->user(), 'پاسخ‌دهی به تیکت', [
            'ticket_id' => $ticket->id,
        ]);

        return back()->with('message', 'پاسخ ارسال شد.');
    }

    public function updateTicketStatus(string $authkey, Request $request, Ticket $ticket, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,answered,closed'],
        ]);
        $ticket->update(['status' => $data['status']]);
        $activityLogger->log('admin.ticket.status.update', auth('admin')->user(), 'تغییر وضعیت تیکت', [
            'ticket_id' => $ticket->id,
            'status' => $data['status'],
        ]);

        return back()->with('message', 'وضعیت تیکت بروزرسانی شد.');
    }

    public function ticketsHub(Request $request, SettingsRepository $settingsRepository)
    {
        $sort = (string) $request->input('sort', 'oldest');
        $status = (string) $request->input('status', 'all');

        $tickets = Ticket::query()
            ->with(['user', 'category', 'latestMessage.user', 'latestMessage.admin'])
            ->when(in_array($status, ['open', 'answered', 'closed', 'spam'], true), fn ($query) => $query->where('status', $status))
            ->when($sort === 'latest', fn ($query) => $query->latest())
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when(! in_array($sort, ['latest', 'oldest'], true), fn ($query) => $query->oldest())
            ->paginate(20, ['*'], 'tickets_page')
            ->withQueryString();

        $categories = TicketCategory::query()
            ->withCount('tickets')
            ->latest()
            ->get();

        $settings = $settingsRepository->get('tickets.settings', [
            'enabled' => true,
            'disabled_message' => 'ارسال تیکت در این زمان ممکن نیست.',
            'hide_admin_name' => false,
        ]);

        $blockedUserIds = $settingsRepository->get('tickets.blocked_users', []);
        $blockedUsers = User::query()->whereIn('id', $blockedUserIds)->get();

        return view('dash.admin.tickets-hub', compact('tickets', 'categories', 'settings', 'blockedUsers', 'sort', 'status'));
    }

    public function saveTicketSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'disabled_message' => ['required', 'string', 'max:500'],
            'admin_email' => ['nullable', 'email', 'max:150'],
            'hide_admin_name' => ['nullable', 'boolean'],
        ]);

        $data['enabled'] = (bool) $request->boolean('enabled');
        $data['hide_admin_name'] = (bool) $request->boolean('hide_admin_name');

        $settingsRepository->set('tickets.settings', $data);
        $activityLogger->log('settings.tickets.update', auth('admin')->user(), 'بروزرسانی تنظیمات تیکت', $data);

        return back()->with('message', 'تنظیمات تیکت ذخیره شد.');
    }

    public function faqHub(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', 'all');

        $faqs = Faq::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($subQuery) use ($q) {
                    $subQuery->where('title', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        $nextSortOrder = ((int) Faq::query()->max('sort_order')) + 5;

        return view('dash.admin.faq-hub', compact('faqs', 'q', 'status', 'nextSortOrder'));
    }

    public function storeFaq(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $nextSortOrder = ((int) Faq::query()->max('sort_order')) + 5;

        $faq = Faq::query()->create([
            'title' => $data['title'],
            'description' => $data['description'],
            'sort_order' => $nextSortOrder,
            'is_active' => (bool) $request->boolean('is_active', true),
        ]);

        $activityLogger->log('admin.faq.create', auth('admin')->user(), 'ایجاد آیتم سوالات متداول', ['faq_id' => $faq->id]);

        return back()->with('message', 'سوال متداول جدید ایجاد شد.');
    }

    public function reorderFaq(Request $request, ActivityLogger $activityLogger)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:faqs,id'],
        ]);

        foreach ($data['ids'] as $index => $id) {
            Faq::query()->where('id', $id)->update(['sort_order' => ($index + 1) * 5]);
        }

        $activityLogger->log('admin.faq.reorder', auth('admin')->user(), 'مرتب‌سازی آیتم‌های سوالات متداول', [
            'count' => count($data['ids']),
        ]);

        return response()->json(['ok' => true]);
    }

    public function updateFaq(string $authkey, Request $request, Faq $faq, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:20000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $faq->update([
            'title' => $data['title'],
            'description' => $data['description'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_active' => (bool) $request->boolean('is_active'),
        ]);

        $activityLogger->log('admin.faq.update', auth('admin')->user(), 'ویرایش آیتم سوالات متداول', ['faq_id' => $faq->id]);

        return back()->with('message', 'سوال متداول بروزرسانی شد.');
    }

    public function bulkFaqAction(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'faq_ids' => ['required', 'array', 'min:1'],
            'faq_ids.*' => ['integer', 'exists:faqs,id'],
            'action' => ['required', 'in:activate,deactivate,delete'],
        ]);

        $ids = $data['faq_ids'];

        if ($data['action'] === 'delete') {
            Faq::query()->whereIn('id', $ids)->delete();
        } elseif ($data['action'] === 'activate') {
            Faq::query()->whereIn('id', $ids)->update(['is_active' => true]);
        } else {
            Faq::query()->whereIn('id', $ids)->update(['is_active' => false]);
        }

        $activityLogger->log('admin.faq.bulk', auth('admin')->user(), 'اقدام گروهی روی آیتم‌های سوالات متداول', [
            'action' => $data['action'],
            'count' => count($ids),
        ]);

        return back()->with('message', 'اقدام گروهی با موفقیت انجام شد.');
    }

    public function toggleFaq(string $authkey, Faq $faq, ActivityLogger $activityLogger): RedirectResponse
    {
        $faq->update(['is_active' => ! $faq->is_active]);
        $activityLogger->log('admin.faq.toggle', auth('admin')->user(), 'تغییر وضعیت آیتم سوالات متداول', ['faq_id' => $faq->id, 'is_active' => $faq->is_active]);

        return back()->with('message', 'وضعیت سوال متداول تغییر کرد.');
    }

    public function deleteFaq(string $authkey, Faq $faq, ActivityLogger $activityLogger): RedirectResponse
    {
        $faqId = $faq->id;
        $faq->delete();
        $activityLogger->log('admin.faq.delete', auth('admin')->user(), 'حذف آیتم سوالات متداول', ['faq_id' => $faqId]);

        return back()->with('message', 'سوال متداول حذف شد.');
    }

    public function toggleTicketUserBlock(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'action' => ['required', 'in:block,unblock'],
        ]);

        $blockedUsers = $settingsRepository->get('tickets.blocked_users', []);

        if ($data['action'] === 'block') {
            if (! in_array($data['user_id'], $blockedUsers)) {
                $blockedUsers[] = $data['user_id'];
            }
        } else {
            $blockedUsers = array_values(array_filter($blockedUsers, fn ($id) => $id != $data['user_id']));
        }

        $settingsRepository->set('tickets.blocked_users', $blockedUsers);

        $activityLogger->log('admin.ticket.user.'.$data['action'], auth('admin')->user(), ($data['action'] === 'block' ? 'مسدود' : 'آزاد').' کردن کاربر از ارسال تیکت', [
            'user_id' => $data['user_id'],
        ]);

        return back()->with('message', 'وضعیت کاربر تغییر یافت.');
    }

    public function bulkTicketAction(string $authkey, Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'ticket_ids' => ['required', 'array', 'min:1'],
            'ticket_ids.*' => ['integer', 'exists:tickets,id'],
            'action' => ['required', 'in:open,answered,closed,spam,delete'],
        ]);

        $ids = $data['ticket_ids'];
        if ($data['action'] === 'delete') {
            // Delete messages first or rely on cascade
            Ticket::query()->whereIn('id', $ids)->delete();
        } else {
            Ticket::query()->whereIn('id', $ids)->update(['status' => $data['action']]);
        }

        $activityLogger->log('admin.ticket.bulk', auth('admin')->user(), 'اقدام گروهی روی تیکت‌ها', [
            'action' => $data['action'],
            'count' => count($ids),
        ]);

        return back()->with('message', 'اقدام گروهی روی تیکت‌ها انجام شد.');
    }

    public function contactHub(Request $request, SettingsRepository $settingsRepository)
    {
        $sort = (string) $request->input('sort', 'latest');
        $read = (string) $request->input('read', 'all');

        $messages = ContactMessage::query()
            ->when($read === 'read', fn ($query) => $query->where('is_read', true))
            ->when($read === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'subject', fn ($query) => $query->orderBy('subject'))
            ->when(! in_array($sort, ['oldest', 'subject'], true), fn ($query) => $query->latest())
            ->paginate(15)
            ->withQueryString();

        $settings = $settingsRepository->get('contact.page_info', [
            'title' => 'اطلاعات تماس',
            'description' => '',
            'phone' => '',
            'email' => '',
            'address' => '',
            'work_hours' => '',
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
        ]);

        $toggles = [
            ['name' => 'show_title', 'label' => 'نمایش عنوان'],
            ['name' => 'show_description', 'label' => 'نمایش توضیحات'],
            ['name' => 'show_phone', 'label' => 'نمایش تلفن'],
            ['name' => 'show_email', 'label' => 'نمایش ایمیل'],
            ['name' => 'show_address', 'label' => 'نمایش آدرس'],
            ['name' => 'show_work_hours', 'label' => 'نمایش ساعت پاسخگویی'],
            ['name' => 'show_map', 'label' => 'نمایش نقشه'],
        ];

        return view('dash.admin.contact-hub', compact('messages', 'sort', 'read', 'settings', 'toggles'));
    }

    public function contactMessages(Request $request)
    {
        return redirect()->route('dash.admin.modules.show', [
            'authkey' => request()->route('authkey'),
            'moduleKey' => 'contact',
            'tab' => 'tab-messages',
        ]);
    }

    public function markContactRead(string $authkey, ContactMessage $contactMessage, ActivityLogger $activityLogger)
    {
        $contactMessage->update(['is_read' => true]);
        $activityLogger->log('admin.contact.read', auth('admin')->user(), 'علامت‌گذاری پیام تماس به عنوان خوانده‌شده', [
            'contact_message_id' => $contactMessage->id,
        ]);

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('message', 'پیام به عنوان خوانده‌شده علامت‌گذاری شد.');
    }

    public function editContactMessage(string $authkey, ContactMessage $contactMessage)
    {
        return view('dash.admin.contact-message-edit', compact('contactMessage'));
    }

    public function updateContactMessage(string $authkey, Request $request, ContactMessage $contactMessage, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'max:120'],
            'email' => ['required', 'email', 'max:120'],
            'subject' => ['required', 'max:150'],
            'message' => ['required', 'max:3000'],
            'is_read' => ['required', 'boolean'],
        ]);

        $contactMessage->update($data);
        $activityLogger->log('admin.contact.update', auth('admin')->user(), 'ویرایش پیام تماس', [
            'contact_message_id' => $contactMessage->id,
        ]);

        return redirect()->route('dash.admin.contact.messages', ['authkey' => $authkey])
            ->with('message', 'پیام تماس ویرایش شد.');
    }

    public function deleteContact(string $authkey, ContactMessage $contactMessage, ActivityLogger $activityLogger): RedirectResponse
    {
        $messageId = $contactMessage->id;
        $contactMessage->delete();
        $activityLogger->log('admin.contact.delete', auth('admin')->user(), 'حذف پیام تماس', [
            'contact_message_id' => $messageId,
        ]);

        return back()->with('message', 'پیام حذف شد.');
    }

    public function bulkContactAction(string $authkey, Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'message_ids' => ['required', 'array', 'min:1'],
            'message_ids.*' => ['integer', 'exists:contact_messages,id'],
            'action' => ['required', 'in:read,unread,delete'],
        ]);

        $ids = $data['message_ids'];
        if ($data['action'] === 'delete') {
            ContactMessage::query()->whereIn('id', $ids)->delete();
        } else {
            ContactMessage::query()->whereIn('id', $ids)->update(['is_read' => $data['action'] === 'read']);
        }

        $activityLogger->log('admin.contact.bulk', auth('admin')->user(), 'اقدام گروهی روی پیام‌های تماس', [
            'action' => $data['action'],
            'count' => count($ids),
        ]);

        return back()->with('message', 'اقدام گروهی روی پیام‌ها انجام شد.');
    }

    public function contactPageInfo(SettingsRepository $settingsRepository)
    {
        return redirect()->route('dash.admin.modules.show', [
            'authkey' => request()->route('authkey'),
            'moduleKey' => 'contact',
            'tab' => 'tab-settings',
        ]);
    }

    public function saveContactPageInfo(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'title' => ['required', 'max:120'],
            'description' => ['nullable', 'max:1000'],
            'phone' => ['nullable', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'max:255'],
            'work_hours' => ['nullable', 'max:120'],
            'map_iframe' => ['nullable', 'string', 'max:5000'],
            'show_title' => ['nullable', 'boolean'],
            'show_description' => ['nullable', 'boolean'],
            'show_phone' => ['nullable', 'boolean'],
            'show_email' => ['nullable', 'boolean'],
            'show_address' => ['nullable', 'boolean'],
            'show_work_hours' => ['nullable', 'boolean'],
            'show_map' => ['nullable', 'boolean'],
            'show_info_box' => ['nullable', 'boolean'],
        ]);

        $data['enabled'] = (bool) $request->boolean('enabled');
        $data['show_title'] = (bool) $request->boolean('show_title');
        $data['show_description'] = (bool) $request->boolean('show_description');
        $data['show_phone'] = (bool) $request->boolean('show_phone');
        $data['show_email'] = (bool) $request->boolean('show_email');
        $data['show_address'] = (bool) $request->boolean('show_address');
        $data['show_work_hours'] = (bool) $request->boolean('show_work_hours');
        $data['show_map'] = (bool) $request->boolean('show_map');
        $data['show_info_box'] = (bool) $request->boolean('show_info_box');

        $settingsRepository->set('contact.page_info', $data);
        $activityLogger->log('settings.contact.page_info.update', auth('admin')->user(), 'بروزرسانی اطلاعات ثابت صفحه تماس با ما');

        return back()->with('message', 'اطلاعات ثابت صفحه تماس با ما ذخیره شد.');
    }

    public function commentsHub(Request $request, SettingsRepository $settingsRepository)
    {
        $sort = (string) $request->input('sort', 'latest');
        $status = (string) $request->input('status', 'all');

        $comments = Comment::query()
            ->with(['user', 'product'])
            ->when(in_array($status, [Comment::STATUS_PENDING, Comment::STATUS_APPROVED, Comment::STATUS_REJECTED, Comment::STATUS_SPAM], true), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'status', fn ($query) => $query->orderBy('status')->latest())
            ->when(! in_array($sort, ['oldest', 'status'], true), fn ($query) => $query->latest())
            ->paginate(30, ['*'], 'comments_page')
            ->withQueryString();

        $settings = $settingsRepository->get('comments.settings', [
            'enabled' => true,
            'disabled_message' => 'ارسال نظر در این زمان ممکن نیست.',
        ]);

        // Report logic: count comments per product
        $reports = Comment::query()
            ->select('product_id', DB::raw('count(*) as comments_count'))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('comments_count')
            ->paginate(20, ['*'], 'reports_page')
            ->withQueryString();

        // Identify store for reports
        foreach ($reports as $report) {
            $report->store = $report->product?->store ?? 'digikala';
            $report->store_label = $report->product?->store_label ?? 'دیجی‌کالا';
        }

        return view('dash.admin.comments-hub', compact('comments', 'sort', 'status', 'settings', 'reports'));
    }

    public function saveCommentSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'disabled_message' => ['required', 'string', 'max:500'],
        ]);

        $data['enabled'] = (bool) $request->boolean('enabled');

        $settingsRepository->set('comments.settings', $data);
        $activityLogger->log('settings.comments.update', auth('admin')->user(), 'بروزرسانی تنظیمات نظرات', $data);

        return back()->with('message', 'تنظیمات نظرات ذخیره شد.');
    }

    public function affiliateSettings(Request $request, SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('affiliate.basalam', [
            'url_prefix' => 'https://a.bslm.ir/api/v1/tracking/click/',
            'cache_ttl_minutes' => 120,
        ]);

        $q = trim((string) $request->input('q'));
        $sort = (string) $request->input('sort', 'latest');
        $store = (string) $request->input('store', 'all');

        $links = AffiliateLink::query()
            ->when($q, function ($query) use ($q) {
                $query->where('product_id', 'like', "%{$q}%")
                    ->orWhere('slug', 'like', "%{$q}%");
            })
            ->when($store !== 'all', fn ($query) => $query->where('store', $store))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'clicks', fn ($query) => $query->orderByDesc('click_count'))
            ->when($sort === 'product_asc', fn ($query) => $query->orderBy('product_id'))
            ->when(! in_array($sort, ['oldest', 'clicks', 'product_asc'], true), fn ($query) => $query->latest())
            ->paginate(20)
            ->withQueryString();

        $startDate = $request->input('start_date', now()->subDays(30)->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $groupBy = $request->input('group_by', 'day'); // day, week, month

        $statsQuery = AffiliateDailyStat::query()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date');

        if ($groupBy === 'month') {
            $dailyStats = $statsQuery->get()
                ->groupBy(fn ($s) => $s->date->format('Y-m'))
                ->map(fn ($group) => [
                    'date' => $group->first()->date->format('Y-m'),
                    'basalam' => $group->where('store', 'basalam')->sum('clicks'),
                    'digikala' => $group->where('store', 'digikala')->sum('clicks'),
                ]);
        } elseif ($groupBy === 'week') {
            $dailyStats = $statsQuery->get()
                ->groupBy(fn ($s) => $s->date->format('Y-W'))
                ->map(fn ($group) => [
                    'date' => 'هفته '.$group->first()->date->format('W').' ('.$group->first()->date->format('Y').')',
                    'basalam' => $group->where('store', 'basalam')->sum('clicks'),
                    'digikala' => $group->where('store', 'digikala')->sum('clicks'),
                ]);
        } else {
            $dailyStats = $statsQuery->get()
                ->groupBy(fn ($s) => $s->date->toDateString())
                ->map(fn ($group) => [
                    'date' => $group->first()->date->toDateString(),
                    'basalam' => $group->where('store', 'basalam')->sum('clicks'),
                    'digikala' => $group->where('store', 'digikala')->sum('clicks'),
                ])->reverse();
        }

        return view('dash.admin.affiliate-settings', compact('settings', 'links', 'q', 'sort', 'store', 'dailyStats', 'startDate', 'endDate', 'groupBy'));
    }

    public function saveAffiliateSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'merchant_id' => ['nullable', 'max:120'],
            'access_token' => ['required'],
            'url_prefix' => ['required', 'url'],
        ]);

        $settingsRepository->set('affiliate.basalam', $data, true);
        $activityLogger->log('settings.affiliate.update', auth('admin')->user(), 'بروزرسانی تنظیمات افیلیت');

        return back()->with('message', 'تنظیمات افیلیت بروزرسانی شد.');
    }

    public function exportAffiliateSettings(string $authkey, string $type, SettingsRepository $settingsRepository): JsonResponse
    {
        $key = ($type === 'basalam') ? 'affiliate.basalam' : 'affiliate.general';
        $settings = $settingsRepository->get($key, []);

        return response()->json($settings, 200, [
            'Content-Disposition' => 'attachment; filename="affiliate-'.$type.'-settings.json"',
        ]);
    }

    public function importAffiliateSettings(string $authkey, string $type, Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $data = json_decode($content, true);

        if (! is_array($data)) {
            return back()->withErrors('فایل JSON معتبر نیست.');
        }

        $key = ($type === 'basalam') ? 'affiliate.basalam' : 'affiliate.general';
        $settingsRepository->set($key, $data, $type === 'basalam');

        $activityLogger->log('settings.affiliate.import', auth('admin')->user(), 'وارد کردن تنظیمات افیلیت '.$type);

        return back()->with('message', 'تنظیمات با موفقیت وارد شد.');
    }

    public function deleteAffiliateLink(string $authkey, AffiliateLink $link, ActivityLogger $activityLogger): RedirectResponse
    {
        $productId = $link->product_id;
        $link->delete();

        $activityLogger->log('admin.affiliate.link.delete', auth('admin')->user(), 'حذف لینک افیلیت کش شده', ['product_id' => $productId]);

        return back()->with('message', 'لینک کش شده حذف شد.');
    }

    public function toggleAffiliateLinkStatus(string $authkey, AffiliateLink $link, ActivityLogger $activityLogger): RedirectResponse
    {
        $newStatus = $link->status === 'active' ? 'disabled' : 'active';
        $link->update(['status' => $newStatus]);

        $activityLogger->log('admin.affiliate.link.toggle', auth('admin')->user(), 'تغییر وضعیت لینک افیلیت', [
            'product_id' => $link->product_id,
            'status' => $newStatus,
        ]);

        return back()->with('message', 'وضعیت لینک تغییر یافت.');
    }

    public function exportAffiliateLinks(string $authkey)
    {
        $links = AffiliateLink::all();
        $stats = AffiliateDailyStat::all();

        return response()->json([
            'links' => $links,
            'daily_stats' => $stats,
        ], 200, [
            'Content-Disposition' => 'attachment; filename="affiliate-links-and-stats-backup.json"',
        ]);
    }

    public function importAffiliateLinks(string $authkey, Request $request, ActivityLogger $activityLogger)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimetypes:application/json,text/plain'],
        ]);

        $content = file_get_contents($request->file('file')->getRealPath());
        $payload = json_decode($content, true);

        if (! is_array($payload)) {
            return back()->withErrors('فایل JSON معتبر نیست.');
        }

        $links = $payload['links'] ?? $payload; // support old format too
        $stats = $payload['daily_stats'] ?? [];

        foreach ($links as $item) {
            if (! isset($item['product_id'])) {
                continue;
            }

            AffiliateLink::query()->updateOrCreate(
                ['product_id' => $item['product_id'], 'store' => $item['store'] ?? 'basalam'],
                [
                    'slug' => $item['slug'] ?? ('b'.base_convert($item['product_id'], 10, 36)),
                    'link' => $item['link'] ?? '',
                    'click_count' => $item['click_count'] ?? 0,
                    'status' => $item['status'] ?? 'active',
                ]
            );
        }

        foreach ($stats as $stat) {
            if (! isset($stat['date']) || ! isset($stat['store'])) {
                continue;
            }
            AffiliateDailyStat::query()->updateOrCreate(
                ['date' => $stat['date'], 'store' => $stat['store']],
                ['clicks' => $stat['clicks'] ?? 0]
            );
        }

        $activityLogger->log('admin.affiliate.links.import', auth('admin')->user(), 'وارد کردن دسته‌ای لینک‌ها و آمار افیلیت');

        return back()->with('message', 'داده‌ها با موفقیت وارد شدند.');
    }

    public function products(string $authkey, Request $request)
    {
        $q = trim((string) $request->input('q'));
        $sort = (string) $request->input('sort', 'latest');
        $store = (string) $request->input('store', 'all');
        $status = (string) $request->input('status', 'all');

        $products = Product::query()
            ->when($q, function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('id', 'like', "%{$q}%")
                        ->orWhere('title', 'like', "%{$q}%");
                });
            })
            ->when($store !== 'all', fn ($query) => $query->where('store', $store))
            ->when($status !== 'all', fn ($query) => $query->where('is_active', $status === 'active'))
            ->when($sort === 'oldest', fn ($query) => $query->oldest())
            ->when($sort === 'id_asc', fn ($query) => $query->orderBy('id'))
            ->when($sort === 'id_desc', fn ($query) => $query->orderByDesc('id'))
            ->when($sort === 'api_inactive', function ($query) {
                $query->orderBy('api_status->data->product->is_inactive', 'desc');
            })
            ->when(! in_array($sort, ['oldest', 'id_asc', 'id_desc', 'api_inactive'], true), fn ($query) => $query->latest())
            ->paginate(30)
            ->withQueryString();

        return view('dash.admin.products.index', compact('products', 'q', 'sort', 'store', 'status', 'authkey'));
    }

    public function productsChecker(string $authkey, Request $request)
    {
        return view('dash.admin.products.checker', compact('authkey'));
    }

    public function toggleProductStatus(string $authkey, Request $request, Product $product, ActivityLogger $activityLogger): RedirectResponse|JsonResponse
    {
        $product->update(['is_active' => ! $product->is_active]);
        $activityLogger->log('admin.product.toggle', auth('admin')->user(), 'تغییر وضعیت فعال‌بودن محصول', [
            'product_id' => $product->id,
            'is_active' => $product->is_active,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'is_active' => $product->is_active,
                'message' => 'وضعیت محصول تغییر یافت.',
            ]);
        }

        return back()->with('message', 'وضعیت محصول تغییر یافت.');
    }

    public function bulkProductAction(string $authkey, Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['string', 'exists:products,id'],
            'action' => ['required', 'in:activate,deactivate,delete'],
        ]);

        $ids = $data['product_ids'];
        if ($data['action'] === 'delete') {
            Product::query()->whereIn('id', $ids)->delete();
        } else {
            Product::query()->whereIn('id', $ids)->update(['is_active' => $data['action'] === 'activate']);
        }

        $activityLogger->log('admin.product.bulk', auth('admin')->user(), 'اقدام گروهی روی محصولات', [
            'action' => $data['action'],
            'count' => count($ids),
        ]);

        return back()->with('message', 'اقدام گروهی روی محصولات انجام شد.');
    }

    public function getDigikalaIdsForCheck(string $authkey): JsonResponse
    {
        $ids = Product::where('store', 'digikala')->pluck('id');

        return response()->json(['ok' => true, 'ids' => $ids]);
    }

    public function checkProductsApiStatus(string $authkey, Request $request): JsonResponse
    {
        $data = $request->validate([
            'product_ids' => ['required', 'array', 'max:10'],
            'product_ids.*' => ['string', 'exists:products,id'],
        ]);

        $ids = $data['product_ids'];
        $results = [];

        $multiCurl = curl_multi_init();
        $curlHandles = [];

        foreach ($ids as $id) {
            $product = Product::find($id);
            if ($product->store !== 'digikala') {
                continue;
            }

            $ch = curl_init();
            // Using the same backend as in ProductController::DigikalaApi
            $backend = '89.42.44.25/api/3600';
            $url = 'http://'.$backend.'/v2/product/'.$id.'/';

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Host: bws.kalands.ir',
                'User-Agent: '.$request->header('user-agent'),
            ]);

            curl_multi_add_handle($multiCurl, $ch);
            $curlHandles[$id] = $ch;
        }

        if (empty($curlHandles)) {
            return response()->json(['ok' => true, 'results' => []]);
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($multiCurl, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multiCurl) != -1) {
                do {
                    $mrc = curl_multi_exec($multiCurl, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($curlHandles as $id => $ch) {
            $responseBody = curl_multi_getcontent($ch);
            $decoded = json_decode($responseBody, true);

            if ($decoded) {
                $product = Product::find($id);
                $isInactive = (bool) ($decoded['data']['product']['is_inactive'] ?? false);

                $updateData = [
                    'api_status' => $decoded,
                    'last_checked_at' => now(),
                ];

                if ($isInactive) {
                    $updateData['is_active'] = false;
                }

                $product->update($updateData);

                $results[$id] = [
                    'ok' => true,
                    'is_inactive' => $isInactive,
                ];
            } else {
                $results[$id] = ['ok' => false];
            }

            curl_multi_remove_handle($multiCurl, $ch);
            curl_close($ch);
        }

        curl_multi_close($multiCurl);

        return response()->json(['ok' => true, 'results' => $results]);
    }

    public function analyticsHub(InternalAnalyticsService $analytics)
    {
        $settings = $analytics->settings();
        $authkey = request()->route('authkey');

        return view('dash.admin.analytics-hub', compact('settings', 'authkey'));
    }

    public function geoipHub(Request $request, SettingsRepository $settingsRepository)
    {
        $logs = $settingsRepository->get('geoip.logs', []);
        $lastRun = $settingsRepository->get('geoip.last_run');

        $filesInfo = [];
        $geoipPath = storage_path('app/geoip');
        $dbFiles = ['GeoLite2-ASN.mmdb', 'GeoLite2-Country.mmdb'];

        foreach ($dbFiles as $file) {
            $path = $geoipPath.'/'.$file;
            if (file_exists($path)) {
                $filesInfo[$file] = [
                    'exists' => true,
                    'size' => round(filesize($path) / (1024 * 1024), 2).' MB',
                    'updated_at' => date('Y-m-d H:i:s', filemtime($path)),
                ];
            } else {
                $filesInfo[$file] = ['exists' => false];
            }
        }

        return view('dash.admin.geoip-hub', compact('logs', 'lastRun', 'filesInfo'));
    }

    public function updateGeoIp(string $authkey, GeoIPService $geoIPService, ActivityLogger $activityLogger): RedirectResponse
    {
        $result = $geoIPService->updateDatabases();

        $activityLogger->log('admin.geoip.update', auth('admin')->user(), 'بروزرسانی دستی دیتابیس GeoIP', [
            'success' => $result['success'],
        ]);

        if ($result['success']) {
            return back()->with('message', 'دیتابیس‌های GeoIP با موفقیت بروزرسانی شدند.');
        } else {
            return back()->withErrors('برخی دیتابیس‌ها بروزرسانی نشدند. جزئیات را در بخش لاگ‌ها ببینید.');
        }
    }

    public function robotsHub(Request $request)
    {
        $robotsPath = public_path('robots.txt');
        $content = '';
        if (file_exists($robotsPath)) {
            $content = file_get_contents($robotsPath);
        }

        return view('dash.admin.robots-hub', compact('content'));
    }

    public function saveRobots(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'content' => ['nullable', 'string', 'max:50000'],
        ]);

        $robotsPath = public_path('robots.txt');

        try {
            file_put_contents($robotsPath, $data['content'] ?? '');

            $activityLogger->log('admin.robots.update', auth('admin')->user(), 'بروزرسانی فایل robots.txt');

            return back()->with('message', 'فایل robots.txt با موفقیت ذخیره شد.');
        } catch (\Throwable $e) {
            return back()->withErrors('خطا در ذخیره فایل: '.$e->getMessage());
        }
    }

    public function testRobots(Request $request): JsonResponse
    {
        $data = $request->validate([
            'agent' => ['required', 'string'],
            'path' => ['required', 'string'],
        ]);

        $robotsPath = public_path('robots.txt');
        $content = file_exists($robotsPath) ? file_get_contents($robotsPath) : '';
        $lines = explode("\n", $content);

        $results = [];
        $currentAgents = [];
        $relevantDirectives = [];

        foreach ($lines as $index => $line) {
            $line = trim($line);
            if (empty($line) || str_starts_with($line, '#')) {
                $results[] = ['line' => $line, 'status' => 'neutral'];

                continue;
            }

            if (stripos($line, 'User-agent:') === 0) {
                $agentName = trim(substr($line, 11));
                $currentAgents[] = $agentName;
                $results[] = ['line' => $line, 'status' => 'neutral'];

                continue;
            }

            $isDirective = false;
            $type = null;
            $value = '';
            if (stripos($line, 'Disallow:') === 0) {
                $isDirective = true;
                $type = 'disallow';
                $value = trim(substr($line, 9));
            } elseif (stripos($line, 'Allow:') === 0) {
                $isDirective = true;
                $type = 'allow';
                $value = trim(substr($line, 6));
            }

            if ($isDirective) {
                $appliesToThisAgent = false;
                foreach ($currentAgents as $ca) {
                    if ($ca === '*' || stripos($data['agent'], $ca) !== false) {
                        $appliesToThisAgent = true;
                        break;
                    }
                }

                if ($appliesToThisAgent) {
                    $relevantDirectives[] = [
                        'type' => $type,
                        'pathPattern' => $value,
                        'lineIndex' => $index,
                    ];
                    $results[] = ['line' => $line, 'status' => 'pending'];
                } else {
                    $results[] = ['line' => $line, 'status' => 'neutral'];
                }
            } else {
                $results[] = ['line' => $line, 'status' => 'neutral'];
            }

            if (isset($lines[$index + 1]) && stripos(trim($lines[$index + 1]), 'User-agent:') === 0) {
                $currentAgents = [];
            }
        }

        $testPath = '/'.ltrim($data['path'], '/');
        $finalStatus = 'allowed';

        usort($relevantDirectives, function ($a, $b) {
            return strlen($b['pathPattern']) - strlen($a['pathPattern']);
        });

        $matchedLineIndex = -1;
        foreach ($relevantDirectives as $directive) {
            $pattern = $directive['pathPattern'];
            $regex = str_replace(['/', '*', '$'], ['\/', '.*', '$'], $pattern);
            if (! str_contains($pattern, '*')) {
                $regex = '^'.$regex;
            }

            if (preg_match('/'.$regex.'/', $testPath)) {
                if ($matchedLineIndex === -1) {
                    $matchedLineIndex = $directive['lineIndex'];
                    $finalStatus = $directive['type'] === 'allow' ? 'allowed' : 'disallowed';
                }
                $results[$directive['lineIndex']]['status'] = ($directive['type'] === 'allow' ? 'match-allow' : 'match-disallow');
            } else {
                $results[$directive['lineIndex']]['status'] = 'neutral';
            }
        }

        return response()->json([
            'ok' => true,
            'final_status' => $finalStatus,
            'matched_line' => $matchedLineIndex,
            'results' => $results,
        ]);
    }

    public function megamenuHub(Request $request, SettingsRepository $settingsRepository)
    {
        $menuConfig = $settingsRepository->get('megamenu.config');

        if (! $menuConfig) {
            $menuConfig = $this->getInitialMegamenuConfig();
            $settingsRepository->set('megamenu.config', $menuConfig);
        }

        return view('dash.admin.megamenu-hub', compact('menuConfig'));
    }

    public function saveMegamenu(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): JsonResponse
    {
        $data = $request->validate([
            'config' => ['required', 'array'],
        ]);

        $settingsRepository->set('megamenu.config', $data['config']);

        $activityLogger->log('admin.megamenu.update', auth('admin')->user(), 'بروزرسانی مگا منو');

        return response()->json(['ok' => true, 'message' => 'تغییرات با موفقیت ذخیره شد.']);
    }

    public function testMegamenuLinks(Request $request): JsonResponse
    {
        $urls = $request->validate([
            'urls' => ['required', 'array'],
            'urls.*' => ['string'],
        ])['urls'];

        $results = [];
        $multiCurl = curl_multi_init();
        $curlHandles = [];

        foreach ($urls as $index => $url) {
            $fullUrl = $url;
            if (str_starts_with($url, '/')) {
                $fullUrl = config('app.url').$url;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $fullUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            curl_multi_add_handle($multiCurl, $ch);
            $curlHandles[$index] = ['handle' => $ch, 'url' => $url];
        }

        $active = null;
        do {
            $mrc = curl_multi_exec($multiCurl, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multiCurl) != -1) {
                do {
                    $mrc = curl_multi_exec($multiCurl, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }

        foreach ($curlHandles as $index => $item) {
            $httpCode = curl_getinfo($item['handle'], CURLINFO_HTTP_CODE);
            $results[] = [
                'url' => $item['url'],
                'status' => $httpCode,
                'ok' => ($httpCode >= 200 && $httpCode < 400),
            ];
            curl_multi_remove_handle($multiCurl, $item['handle']);
            curl_close($item['handle']);
        }
        curl_multi_close($multiCurl);

        return response()->json(['ok' => true, 'results' => $results]);
    }

    public function errorPagesHub(Request $request, SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('error_pages.settings', [
            'icons_per_row' => 4,
        ]);

        $targetCount = (int) ($settings['icons_per_row'] ?? 4);

        $links = $settingsRepository->get('error_pages.links', [
            ['title' => 'گوشی موبایل', 'url' => '/result/mobile-phone', 'icon' => 'smartphone'],
            ['title' => 'لپ‌تاپ', 'url' => '/result/laptop', 'icon' => 'laptop'],
            ['title' => 'تبلت', 'url' => '/result/tablet', 'icon' => 'tablet_mac'],
            ['title' => 'تماس با ما', 'url' => '/contact', 'icon' => 'support_agent'],
        ]);

        // Ensure we have exactly $targetCount links
        if (count($links) < $targetCount) {
            for ($i = count($links); $i < $targetCount; $i++) {
                $links[] = ['title' => '', 'url' => '', 'icon' => 'link'];
            }
        } elseif (count($links) > $targetCount) {
            $links = array_slice($links, 0, $targetCount);
        }

        return view('dash.admin.error-pages-hub', compact('links', 'settings'));
    }

    public function saveErrorPagesSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'links' => ['required', 'array', 'min:1'],
            'links.*.title' => ['required', 'string', 'max:100'],
            'links.*.url' => ['required', 'string', 'max:255'],
            'links.*.icon' => ['required', 'string', 'max:50'],
            'settings' => ['required', 'array'],
            'settings.icons_per_row' => ['required', 'integer', 'in:4,5,6'],
        ]);

        $settingsRepository->set('error_pages.links', $data['links']);
        $settingsRepository->set('error_pages.settings', $data['settings']);

        // Publish to JSON for frontend
        try {
            $dir = public_path('assets/error-pages');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            file_put_contents(
                $dir.'/config.json',
                json_encode([
                    'links' => $data['links'],
                    'settings' => $data['settings'],
                    'updated_at' => now()->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            );
        } catch (\Throwable $e) {
            Log::error('Error publishing error_pages config: '.$e->getMessage());
        }

        $activityLogger->log('admin.error_pages.update', auth('admin')->user(), 'بروزرسانی تنظیمات صفحات خطا');

        return back()->with('message', 'تنظیمات صفحات خطا ذخیره شد.');
    }

    private function getInitialMegamenuConfig()
    {
        $jsPath = resource_path('js/menuData.js');
        if (! file_exists($jsPath)) {
            return [];
        }

        $content = file_get_contents($jsPath);

        // Extract the main object
        $start = strpos($content, '{');
        $end = strrpos($content, '}');
        if ($start === false || $end === false) {
            return [];
        }

        $jsonStr = substr($content, $start, $end - $start + 1);

        // JS to JSON cleanup (basic)
        // 1. Remove 'export const menuData = ' is already done by finding first '{'
        // 2. Ensure keys are quoted (they are in menuData.js)
        // 3. Remove trailing commas before closing braces/brackets
        $jsonStr = preg_replace('/,\s*([\]\}])/', '$1', $jsonStr);

        $data = json_decode($jsonStr, true);
        if (! $data) {
            // Fallback to manual extraction if json_decode fails
            preg_match('/"parent_groups":\s*(\[.*?\])\s*,\s*"id_to_title"/s', $content, $groupsMatch);
            $parentGroups = json_decode(preg_replace('/,\s*([\]\}])/', '$1', $groupsMatch[1] ?? '[]'), true);

            preg_match('/"id_to_title":\s*({.*?})\s*,\s*"menuData"/s', $content, $titlesMatch);
            $idToTitle = json_decode(preg_replace('/,\s*([\]\}])/', '$1', $titlesMatch[1] ?? '{}'), true);

            $menuDataStart = strpos($content, '"menuData":');
            $menuDataContent = substr($content, $menuDataStart + 11);
            $lastBrace = strrpos($menuDataContent, '}');
            $menuDataJson = substr($menuDataContent, 0, $lastBrace);
            $menuData = json_decode(preg_replace('/,\s*([\]\}])/', '$1', $menuDataJson.'}'), true);
        } else {
            $parentGroups = $data['parent_groups'] ?? [];
            $idToTitle = $data['id_to_title'] ?? [];
            $menuData = $data['menuData'] ?? [];
        }

        $finalConfig = [];

        foreach ($parentGroups as $group) {
            $categories = [];
            foreach (($group['ids'] ?? []) as $catId) {
                $subSections = [];
                $sections = $menuData[$catId] ?? [];
                foreach ($sections as $section) {
                    $items = [];
                    foreach (($section['items'] ?? []) as $item) {
                        $items[] = [
                            'title' => $item['title'] ?? 'بدون عنوان',
                            'href' => $item['href'] ?? '#',
                            'show_desktop' => true,
                            'show_mobile' => true,
                        ];
                    }
                    $subSections[] = [
                        'title' => $section['header'] ?? 'بخش جدید',
                        'show_desktop' => true,
                        'show_mobile' => true,
                        'items' => $items,
                    ];
                }

                $categories[] = [
                    'id' => $catId,
                    'title' => $idToTitle[$catId] ?? $catId,
                    'show_desktop' => true,
                    'show_mobile' => true,
                    'sub_sections' => $subSections,
                ];
            }

            $finalConfig[] = [
                'title' => $group['header'] ?? 'گروه جدید',
                'show_desktop' => true,
                'show_mobile' => true,
                'categories' => $categories,
            ];
        }

        return $finalConfig;
    }

    public function objectCacheHub(Request $request, SettingsRepository $settingsRepository)
    {
        $currentDriver = env('CACHE_DRIVER', 'file');
        $drivers = [
            'file' => 'فایل (File)',
            'redis' => 'Redis',
            'memcached' => 'Memcached',
            'database' => 'دیتابیس (Database)',
            'apc' => 'APC',
            'array' => 'Array',
            'dynamodb' => 'DynamoDB',
            'octane' => 'Octane',
        ];

        $currentPrefix = Config::get('cache.prefix');

        $settings = $settingsRepository->get('object_cache.settings', [
            'driver' => $currentDriver,
            'prefix' => $currentPrefix,
        ]);

        $selectedDriver = $settings['driver'] ?? $currentDriver;

        $redisConfig = [
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', '6379'),
            'socket' => env('REDIS_SOCKET', ''),
            'password' => env('REDIS_PASSWORD', ''),
            'database' => env('REDIS_CACHE_DB', '1'),
        ];

        $memcachedConfig = [
            'host' => env('MEMCACHED_HOST', '127.0.0.1'),
            'port' => env('MEMCACHED_PORT', '11211'),
            'socket' => env('MEMCACHED_SOCKET', ''),
        ];

        $cacheItems = [];
        $search = (string) $request->input('search', '');
        $canList = in_array($selectedDriver, ['redis', 'database']);

        if ($canList && $search !== '') {
            $cacheItems = $this->searchCacheItems($selectedDriver, $search);
        } elseif ($canList && $request->input('tab') === 'tab-items') {
            $cacheItems = $this->listCacheItems($selectedDriver);
        }

        $driverStatus = $this->getCacheDriverStatus();

        return view('dash.admin.object-cache-hub', compact(
            'currentDriver', 'drivers', 'currentPrefix', 'settings', 'selectedDriver',
            'redisConfig', 'memcachedConfig', 'cacheItems', 'search', 'canList', 'driverStatus'
        ));
    }

    private function getCacheDriverStatus(): array
    {
        $status = [];

        // Redis
        $redisExt = extension_loaded('redis');
        $redisConn = false;
        if ($redisExt) {
            try {
                $redisConn = Redis::connection()->ping() ? true : false;
            } catch (\Throwable) {
                $redisConn = false;
            }
        }
        $status['redis'] = ['installed' => $redisExt, 'connected' => $redisConn, 'label' => 'Redis'];

        // Memcached
        $memcachedExt = extension_loaded('memcached');
        $status['memcached'] = ['installed' => $memcachedExt, 'connected' => false, 'label' => 'Memcached'];

        // APCu
        $apcExt = extension_loaded('apcu');
        $status['apc'] = ['installed' => $apcExt, 'connected' => $apcExt, 'label' => 'APC/APCu'];

        // Database
        $table = Config::get('cache.stores.database.table', 'cache');
        $dbStatus = Schema::hasTable($table);
        $status['database'] = ['installed' => true, 'connected' => $dbStatus, 'label' => 'Database Table ('.$table.')'];

        return $status;
    }

    public function saveObjectCacheSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'driver' => ['required', 'string', 'in:file,redis,memcached,database,apc,array,dynamodb,octane'],
            'prefix' => ['required', 'string', 'max:100'],
            'redis_scheme' => ['nullable', 'string', 'in:tcp,unix'],
            'redis_host' => ['nullable', 'string', 'max:200'],
            'redis_port' => ['nullable', 'string', 'max:10'],
            'redis_socket' => ['nullable', 'string', 'max:300'],
            'redis_password' => ['nullable', 'string', 'max:200'],
            'redis_database' => ['nullable', 'string', 'max:10'],
            'memcached_host' => ['nullable', 'string', 'max:200'],
            'memcached_port' => ['nullable', 'string', 'max:10'],
            'memcached_socket' => ['nullable', 'string', 'max:300'],
        ]);

        $settingsRepository->set('object_cache.settings', [
            'driver' => $data['driver'],
            'prefix' => $data['prefix'],
        ]);

        $envMappings = [
            'CACHE_STORE' => $data['driver'],
            'CACHE_DRIVER' => $data['driver'],
            'CACHE_PREFIX' => $data['prefix'],
            'REDIS_SCHEME' => $data['redis_scheme'] ?? 'tcp',
            'REDIS_HOST' => $data['redis_host'] ?? '127.0.0.1',
            'REDIS_PORT' => $data['redis_port'] ?? '6379',
            'REDIS_SOCKET' => $data['redis_socket'] ?? '',
            'REDIS_PASSWORD' => $data['redis_password'] ?? '',
            'REDIS_CACHE_DB' => $data['redis_database'] ?? '1',
            'MEMCACHED_HOST' => $data['memcached_host'] ?? '127.0.0.1',
            'MEMCACHED_PORT' => $data['memcached_port'] ?? '11211',
            'MEMCACHED_SOCKET' => $data['memcached_socket'] ?? '',
        ];

        foreach ($envMappings as $key => $value) {
            $this->writeEnvFile($key, (string) $value);
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
        }

        $this->rebuildEnvSection();

        $activityLogger->log('settings.object_cache.update', auth('admin')->user(), 'بروزرسانی تنظیمات object cache', $data);

        Artisan::call('config:clear');

        return back()->with('message', 'تنظیمات Object Cache با موفقیت ذخیره و در فایل .env اعمال شد.');
    }

    private function writeEnvFile(string $key, string $value): void
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        $escapedValue = str_replace('"', '\\"', $value);
        if (preg_match('/[\s#]/', $value) || $value === '' || preg_match('/^[0-9]/', $value)) {
            $escapedValue = '"'.$escapedValue.'"';
        }

        $pattern = "/^{$key}=.*/m";
        $replacement = "{$key}={$escapedValue}";

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $replacement, $content);
        } else {
            $content .= "\n{$key}={$escapedValue}";
        }

        file_put_contents($path, $content);
    }

    private function rebuildEnvSection(): void
    {
        $path = base_path('.env');
        if (! file_exists($path)) {
            return;
        }

        $content = file_get_contents($path);

        $sectionHeader = '# ============================================';
        $sectionTitle = '# DON\'T DELETE - Object Cache Configuration';

        $startMarker = $sectionHeader."\n".$sectionTitle."\n";
        $endMarker = "\n".$sectionHeader;

        $sectionVars = [
            'CACHE_STORE', 'CACHE_DRIVER', 'CACHE_PREFIX',
            'REDIS_SCHEME', 'REDIS_HOST', 'REDIS_PORT', 'REDIS_SOCKET', 'REDIS_PASSWORD', 'REDIS_CACHE_DB',
            'MEMCACHED_HOST', 'MEMCACHED_PORT', 'MEMCACHED_SOCKET',
        ];

        $existingValues = [];
        preg_match_all('/^([A-Z_]+)=(.*)$/m', $content, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $existingValues[$match[1]] = trim($match[2], '"\'');
        }

        $newSection = $startMarker;
        foreach ($sectionVars as $var) {
            $value = $existingValues[$var] ?? '';
            $escapedValue = str_replace('"', '\\"', $value);
            if (preg_match('/[\s#]/', $value) || $value === '' || preg_match('/^[0-9]/', $value)) {
                $escapedValue = '"'.$escapedValue.'"';
            }
            $newSection .= "{$var}={$escapedValue}\n";
        }
        $newSection .= $endMarker;

        $startPos = strpos($content, $startMarker);
        if ($startPos !== false) {
            $endPos = strpos($content, $sectionHeader, $startPos + 1);
            if ($endPos !== false) {
                $endPos += strlen($sectionHeader);
            }
            $before = substr($content, 0, $startPos);
            $after = substr($content, $endPos ?? $startPos);
        } else {
            $before = $content;
            $after = '';
        }

        $content = $before."\n\n".$newSection."\n";

        $headerPattern = '/^'.preg_quote($sectionHeader, '/').'$/m';
        $after = preg_replace($headerPattern, '', $after);
        $varPattern = '/^('.implode('|', $sectionVars).')=.*$/m';
        $after = preg_replace($varPattern, '', $after);

        $content .= trim(preg_replace('/\n{3,}/', "\n\n", $after));

        file_put_contents($path, $content);
    }

    public function testObjectCacheConnection(): JsonResponse
    {
        $testKey = '_opencode_cache_test_'.Str::random(8);
        $testValue = 'ok_'.time();

        $start = microtime(true);

        try {
            Cache::put($testKey, $testValue, 1);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            $latency = round((microtime(true) - $start) * 1000, 2);

            if ($retrieved === $testValue) {
                return response()->json(['ok' => true, 'latency_ms' => $latency]);
            }

            return response()->json(['ok' => false, 'message' => 'داده‌های کش به درستی ذخیره/بازیابی نشدند.']);
        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => 'خطا: '.$e->getMessage()]);
        }
    }

    public function purgeObjectCache(ActivityLogger $activityLogger): RedirectResponse
    {
        Cache::flush();

        $activityLogger->log('actions.object_cache.purge', auth('admin')->user(), 'پاکسازی کامل object cache');

        return back()->with('message', 'Object Cache با موفقیت پاکسازی شد.');
    }

    public function deleteObjectCacheItem(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'key' => ['required', 'string', 'max:500'],
        ]);

        Cache::forget($data['key']);

        return back()->with('message', 'آیتم "'.$data['key'].'" با موفقیت حذف شد.');
    }

    private function listCacheItems(string $driver): array
    {
        if ($driver === 'redis') {
            return $this->listRedisCacheItems();
        }

        if ($driver === 'database') {
            return $this->listDatabaseCacheItems();
        }

        return [];
    }

    private function searchCacheItems(string $driver, string $search): array
    {
        if ($driver === 'redis') {
            return $this->listRedisCacheItems($search);
        }

        if ($driver === 'database') {
            return $this->listDatabaseCacheItems($search);
        }

        return [];
    }

    private function listRedisCacheItems(string $pattern = '*'): array
    {
        try {
            $prefix = Config::get('cache.prefix', '');
            $searchPattern = $prefix.$pattern;

            $keys = Redis::connection()->keys($searchPattern);

            $items = [];
            foreach ($keys as $key) {
                $displayKey = $key;
                if ($prefix && str_starts_with($key, $prefix)) {
                    $displayKey = substr($key, strlen($prefix));
                }
                $items[] = $displayKey;
            }

            sort($items);

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function listDatabaseCacheItems(string $search = ''): array
    {
        try {
            $table = Config::get('cache.stores.database.table', 'cache');
            $prefix = Config::get('cache.prefix', '');

            $query = DB::table($table);

            if ($search) {
                $query->where('key', 'like', '%'.$search.'%');
            }

            $rows = $query->pluck('key');

            $items = [];
            foreach ($rows as $key) {
                $displayKey = $key;
                if ($prefix && str_starts_with($key, $prefix)) {
                    $displayKey = substr($key, strlen($prefix));
                }
                $items[] = $displayKey;
            }

            sort($items);

            return $items;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function artisanCommandsList(): array
    {
        return [
            [
                'command' => 'cache:clear',
                'label' => 'پاکسازی کش سیستم',
                'description' => 'تمام کش‌های ذخیره شده در برنامه پاک می‌شوند. این شامل کش config، route، view و سایر داده‌های موقت است.',
                'icon' => 'cached',
            ],
            [
                'command' => 'view:clear',
                'label' => 'پاکسازی کش موتور Blade',
                'description' => 'فایل‌های کامپایل شده قالب‌های Blade پاک می‌شوند. درخواست بعدی، قالب‌ها را مجدداً کامپایل می‌کند.',
                'icon' => 'visibility_off',
            ],
            [
                'command' => 'view:cache',
                'label' => 'بازسازی کش موتور Blade',
                'description' => 'همه قالب‌های Blade کامپایل شده و در حافظه کش ذخیره می‌شوند. سرعت بارگذاری صفحات افزایش می‌یابد.',
                'icon' => 'visibility',
            ],
            [
                'command' => 'config:clear',
                'label' => 'پاکسازی کش تنظیمات',
                'description' => 'فایل کش شده تنظیمات (config) پاک می‌شود. پس از این، تنظیمات مستقیماً از فایل‌های config لود می‌شوند.',
                'icon' => 'settings_backup_restore',
            ],
            [
                'command' => 'config:cache',
                'label' => 'بازسازی کش تنظیمات',
                'description' => 'همه فایل‌های تنظیمات در یک فایل کش ادغام می‌شوند. این کار سرعت برنامه را افزایش می‌دهد.',
                'icon' => 'settings_applications',
            ],
            [
                'command' => 'route:clear',
                'label' => 'پاکسازی کش مسیرها',
                'description' => 'کش مسیرهای (routes) برنامه پاک می‌شود. پس از این مسیرها از فایل routes بارگذاری می‌شوند.',
                'icon' => 'route',
            ],
            [
                'command' => 'route:cache',
                'label' => 'بازسازی کش مسیرها',
                'description' => 'همه مسیرهای برنامه در یک فایل کش ذخیره می‌شوند. سرعت مسیریابی را افزایش می‌دهد.',
                'icon' => 'alt_route',
            ],
            [
                'command' => 'optimize:clear',
                'label' => 'پاکسازی همه کش‌های بهینه‌سازی',
                'description' => 'تمامی کش‌های بهینه‌سازی شامل config، route، view و event cache به صورت یکجا پاک می‌شوند.',
                'icon' => 'cleaning_services',
            ],
            [
                'command' => 'optimize',
                'label' => 'بازسازی همه کش‌های بهینه‌سازی',
                'description' => 'تمامی کش‌های بهینه‌سازی (config، route، view، event) به صورت یکجا بازسازی می‌شوند.',
                'icon' => 'rocket_launch',
            ],
            [
                'command' => 'queue:restart',
                'label' => 'راه‌اندازی مجدد صف‌ها',
                'description' => 'به تمام workerهای صف دستور توقف پس از اتمام کار فعلی داده می‌شود. worker جدید شروع به کار می‌کند.',
                'icon' => 'restart_alt',
            ],
            [
                'command' => 'storage:link',
                'label' => 'ایجاد لینک نمادین استوریج',
                'description' => 'لینک نمادین (symlink) از storage/app/public به public/storage ایجاد می‌کند. برای دسترسی به فایل‌های آپلودی ضروری است.',
                'icon' => 'link',
            ],
            [
                'command' => 'migrate --force',
                'label' => 'اجرای migration‌ها',
                'description' => 'تمامی migrationهای اجرا نشده به ترتیب بر روی دیتابیس اعمال می‌شوند.',
                'icon' => 'storage',
                'warning' => true,
                'warning_message' => 'تغییرات در دیتابیس اعمال می‌شود. توصیه می‌شود قبل از اجرا از دیتابیس پشتیبان تهیه کنید.',
            ],
            [
                'command' => 'migrate:fresh --force',
                'label' => 'ریست و اجرای مجدد migrationها',
                'description' => 'تمامی جداول دیتابیس حذف و سپس همه migrationها از ابتدا اجرا می‌شوند. تمام داده‌ها از بین می‌روند!',
                'icon' => 'dangerous',
                'danger' => true,
                'warning_message' => '⚠️ تمام داده‌های دیتابیس برای همیشه حذف می‌شوند! این عملیات غیرقابل بازگشت است.',
            ],
            [
                'command' => 'clear_logs',
                'label' => 'حذف لاگ‌ها',
                'description' => 'تمام فایل‌های لاگ ذخیره شده در مسیر storage/logs پاک می‌شوند. فضای دیسک آزاد می‌شود.',
                'icon' => 'delete_sweep',
                'warning' => true,
                'warning_message' => 'تمامی فایل‌های لاگ برای همیشه حذف می‌شوند. در صورت نیاز ابتدا از آن‌ها پشتیبان بگیرید.',
            ],
            [
                'command' => 'queue:work --stop-when-empty',
                'label' => 'پردازش یکباره صف',
                'description' => 'یک بار صف‌های در انتظار پردازش شده و worker پس از خالی شدن صف متوقف می‌شود.',
                'icon' => 'play_circle',
            ],
        ];
    }

    public function artisanCommandsHub()
    {
        $commands = $this->artisanCommandsList();
        $recentLogs = ArtisanExecutionLog::query()
            ->latest('executed_at')
            ->take(15)
            ->get();

        return view('dash.admin.artisan-commands-hub', compact('commands', 'recentLogs'));
    }

    public function executeArtisanCommand(Request $request, PasswordHashService $passwordHashService): JsonResponse
    {
        $commandKey = $request->input('command');
        $commands = collect($this->artisanCommandsList());

        $matched = $commands->firstWhere('command', $commandKey);
        if (! $matched) {
            return response()->json([
                'ok' => false,
                'message' => 'دستور مورد نظر یافت نشد.',
            ]);
        }

        if ($matched['danger'] ?? false) {
            $password = $request->input('password');
            if (! $password) {
                return response()->json([
                    'ok' => false,
                    'message' => 'برای اجرای این دستور، رمز عبور الزامی است.',
                ]);
            }

            $admin = auth('admin')->user();
            if (! $admin || ! $admin->password_hash || ! $admin->password_salt) {
                return response()->json([
                    'ok' => false,
                    'message' => 'اطلاعات حساب کاربری یافت نشد.',
                ]);
            }

            if (! $passwordHashService->verify($password, $admin->password_salt, $admin->password_hash)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'رمز عبور وارد شده اشتباه است.',
                ]);
            }
        }

        $admin = auth('admin')->user();
        $status = 'success';
        $output = '';

        try {
            if ($commandKey === 'clear_logs') {
                $logPath = storage_path('logs');
                $files = File::glob($logPath.'/*.log');
                $count = 0;
                foreach ($files as $file) {
                    if (File::isFile($file)) {
                        File::delete($file);
                        $count++;
                    }
                }
                $output = "تعداد {$count} فایل لاگ با موفقیت حذف شد.";
            } else {
                Artisan::call($commandKey);
                $output = Artisan::output();
            }
        } catch (\Throwable $e) {
            $status = 'failed';
            $output = $e->getMessage();
        }

        ArtisanExecutionLog::create([
            'admin_id' => $admin?->id,
            'admin_name' => $admin?->full_name ?? $admin?->username ?? 'سیستم',
            'command' => $commandKey,
            'label' => $matched['label'],
            'status' => $status,
            'output' => trim($output),
            'executed_at' => now(),
        ]);

        $this->pruneExecutionLogs();

        $ok = $status === 'success';

        return response()->json([
            'ok' => $ok,
            'output' => trim($output),
            'message' => $ok ? 'دستور با موفقیت اجرا شد.' : 'خطا در اجرای دستور.',
        ]);
    }

    private function pruneExecutionLogs(): void
    {
        $count = ArtisanExecutionLog::count();
        if ($count > 15) {
            $ids = ArtisanExecutionLog::query()
                ->orderBy('executed_at', 'desc')
                ->skip(15)
                ->take($count - 15)
                ->pluck('id');
            ArtisanExecutionLog::whereIn('id', $ids)->delete();
        }
    }

    public function verifyArtisanPassword(Request $request, PasswordHashService $passwordHashService): JsonResponse
    {
        $password = $request->input('password');
        if (! $password) {
            return response()->json([
                'ok' => false,
                'message' => 'رمز عبور را وارد کنید.',
            ]);
        }

        $admin = auth('admin')->user();
        if (! $admin || ! $admin->password_hash || ! $admin->password_salt) {
            return response()->json([
                'ok' => false,
                'message' => 'اطلاعات حساب کاربری یافت نشد.',
            ]);
        }

        if (! $passwordHashService->verify($password, $admin->password_salt, $admin->password_hash)) {
            return response()->json([
                'ok' => false,
                'message' => 'رمز عبور وارد شده اشتباه است.',
            ]);
        }

        return response()->json([
            'ok' => true,
            'message' => 'رمز عبور با موفقیت تأیید شد.',
        ]);
    }

    public function cacheManagementHub(Request $request, SettingsRepository $settingsRepository)
    {
        $settings = $settingsRepository->get('cache.webservices', [
            'autocomplete_ttl' => 31536000,
            'autocomplete_litespeed' => true,
        ]);

        $htaccessPath = public_path('.htaccess');
        $htaccessContent = file_exists($htaccessPath) ? file_get_contents($htaccessPath) : '';

        $litespeedConfig = [
            'cache_lookup' => str_contains($htaccessContent, 'CacheLookup on'),
            'cache_lookup_esi' => str_contains($htaccessContent, 'esi'),
            'cache_lookup_crawler' => str_contains($htaccessContent, 'crawler'),
            'process_group' => str_contains($htaccessContent, 'LSPHP_ProcessGroup on'),
            'workers' => 100,
            'quic' => str_contains($htaccessContent, 'QuicEnable on'),
            'spdy' => 'off',
        ];

        if (str_contains($htaccessContent, 'SpdyEnabled http3 http2') || str_contains($htaccessContent, 'SpdyEnabled http2 http3')) {
            $litespeedConfig['spdy'] = 'http3_http2';
        } elseif (str_contains($htaccessContent, 'SpdyEnabled http3')) {
            $litespeedConfig['spdy'] = 'http3';
        } elseif (str_contains($htaccessContent, 'SpdyEnabled http2')) {
            $litespeedConfig['spdy'] = 'http2';
        }

        if (preg_match('/LSPHP_Workers\s+(\d+)/', $htaccessContent, $matches)) {
            $litespeedConfig['workers'] = (int) $matches[1];
        }

        return view('dash.admin.cache-management-hub', compact('settings', 'litespeedConfig'));
    }

    public function saveCacheManagementSettings(Request $request, SettingsRepository $settingsRepository, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'autocomplete_ttl' => ['required', 'integer', 'min:0', 'max:31536000'],
            'autocomplete_litespeed' => ['nullable', 'boolean'],
            'autocomplete_cache_type' => ['required', 'in:public,private'],
            'autocomplete_custom_enabled' => ['nullable', 'boolean'],
            'autocomplete_custom_cc' => ['nullable', 'string', 'max:255'],
            'autocomplete_custom_lsc' => ['nullable', 'string', 'max:255'],
            'autocomplete_custom_cdn' => ['nullable', 'string', 'max:255'],
            'autocomplete_custom_cf' => ['nullable', 'string', 'max:255'],
            'visitor_info_ttl' => ['required', 'integer', 'min:0', 'max:31536000'],
            'visitor_info_litespeed' => ['nullable', 'boolean'],
            'visitor_info_cache_type' => ['required', 'in:public,private'],
            'visitor_info_custom_enabled' => ['nullable', 'boolean'],
            'visitor_info_custom_cc' => ['nullable', 'string', 'max:255'],
            'visitor_info_custom_lsc' => ['nullable', 'string', 'max:255'],
            'visitor_info_custom_cdn' => ['nullable', 'string', 'max:255'],
            'visitor_info_custom_cf' => ['nullable', 'string', 'max:255'],
        ]);

        $settingsRepository->set('cache.webservices', [
            'autocomplete_ttl' => (int) $data['autocomplete_ttl'],
            'autocomplete_litespeed' => (bool) ($data['autocomplete_litespeed'] ?? false),
            'autocomplete_cache_type' => $data['autocomplete_cache_type'],
            'autocomplete_custom_enabled' => (bool) ($data['autocomplete_custom_enabled'] ?? false),
            'autocomplete_custom_cc' => $data['autocomplete_custom_cc'],
            'autocomplete_custom_lsc' => $data['autocomplete_custom_lsc'],
            'autocomplete_custom_cdn' => $data['autocomplete_custom_cdn'],
            'autocomplete_custom_cf' => $data['autocomplete_custom_cf'],
            'visitor_info_ttl' => (int) $data['visitor_info_ttl'],
            'visitor_info_litespeed' => (bool) ($data['visitor_info_litespeed'] ?? false),
            'visitor_info_cache_type' => $data['visitor_info_cache_type'],
            'visitor_info_custom_enabled' => (bool) ($data['visitor_info_custom_enabled'] ?? false),
            'visitor_info_custom_cc' => $data['visitor_info_custom_cc'],
            'visitor_info_custom_lsc' => $data['visitor_info_custom_lsc'],
            'visitor_info_custom_cdn' => $data['visitor_info_custom_cdn'],
            'visitor_info_custom_cf' => $data['visitor_info_custom_cf'],
        ]);

        $activityLogger->log('settings.cache_management.update', auth('admin')->user(), 'بروزرسانی تنظیمات کش', $data);

        return back()->with('message', 'تنظیمات کش با موفقیت ذخیره شد.');
    }

    public function saveHtaccessSettings(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $data = $request->validate([
            'cache_lookup' => ['nullable', 'boolean'],
            'cache_lookup_esi' => ['nullable', 'boolean'],
            'cache_lookup_crawler' => ['nullable', 'boolean'],
            'process_group' => ['nullable', 'boolean'],
            'workers' => ['required', 'integer', 'min:1', 'max:1000'],
            'quic' => ['nullable', 'boolean'],
            'spdy' => ['required', 'string', 'in:off,http2,http3,http3_http2'],
        ]);

        $htaccessPath = public_path('.htaccess');
        if (! file_exists($htaccessPath)) {
            return back()->withErrors('فایل .htaccess یافت نشد.');
        }

        // Backup
        $backupDir = storage_path('app/backups/htaccess');
        if (! file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        copy($htaccessPath, $backupDir.'/.htaccess_backup_'.date('Ymd_His'));

        $content = file_get_contents($htaccessPath);

        $newLines = [];

        $cacheLookup = $request->boolean('cache_lookup') ? 'on' : 'off';
        if ($request->boolean('cache_lookup')) {
            if ($request->boolean('cache_lookup_esi')) {
                $cacheLookup .= ' esi';
            }
            if ($request->boolean('cache_lookup_crawler')) {
                $cacheLookup .= ' crawler';
            }
        }
        $newLines[] = '    CacheLookup '.$cacheLookup;

        $newLines[] = '    LSPHP_ProcessGroup '.($request->boolean('process_group') ? 'on' : 'off');
        $newLines[] = '    LSPHP_Workers '.$data['workers'];
        $newLines[] = '    QuicEnable '.($request->boolean('quic') ? 'on' : 'off');

        $spdy = 'off';
        if ($data['spdy'] === 'http2') {
            $spdy = 'http2';
        } elseif ($data['spdy'] === 'http3') {
            $spdy = 'http3';
        } elseif ($data['spdy'] === 'http3_http2') {
            $spdy = 'http3 http2';
        }
        $newLines[] = '    SpdyEnabled '.$spdy;

        $configBlock = "# --- LITESPEED OPTIMIZATION START ---\n".implode("\n", $newLines)."\n# --- LITESPEED OPTIMIZATION END ---";

        // Check if our block already exists
        if (str_contains($content, '# --- LITESPEED OPTIMIZATION START ---')) {
            $content = preg_replace('/# --- LITESPEED OPTIMIZATION START ---.*?# --- LITESPEED OPTIMIZATION END ---/s', $configBlock, $content);
        } else {
            // Try to insert after <IfModule litespeed> or at the beginning
            if (str_contains($content, '<IfModule litespeed>')) {
                $content = preg_replace('/<IfModule litespeed>\s*/', "<IfModule litespeed>\n".$configBlock."\n", $content);
            } else {
                $content = "<IfModule litespeed>\n".$configBlock."\n</IfModule>\n\n".$content;
            }
        }

        file_put_contents($htaccessPath, $content);

        $activityLogger->log('settings.htaccess.update', auth('admin')->user(), 'بروزرسانی تنظیمات .htaccess');

        return back()->with('message', 'تنظیمات .htaccess با موفقیت اعمال شد. نسخه پشتیبان تهیه شد.');
    }

    public function downloadHtaccessBackup(Request $request)
    {
        $backupDir = storage_path('app/backups/htaccess');
        $files = glob($backupDir.'/*');
        if (empty($files)) {
            return back()->withErrors('بکاپی یافت نشد.');
        }

        usort($files, fn ($a, $b) => filemtime($b) - filemtime($a));

        return response()->download($files[0]);
    }

    public function categoriesHub(Request $request)
    {
        $authkey = request()->route('authkey');
        $settings = app(SettingsRepository::class)->get('categories.settings', [
            'vector_engine' => 'local',
            'external_model' => 'gemma-4',
            'api_endpoint' => '',
            'api_key' => '',
        ]);

        $aiUsage = AiUsageLog::query()
            ->selectRaw('DATE(created_at) as date, SUM(tokens_used) as tokens, COUNT(*) as requests')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(7)
            ->get();

        return view('dash.admin.categories-hub', compact('authkey', 'settings', 'aiUsage'));
    }

    public function saveCategorySettings(Request $request, SettingsRepository $settingsRepository): RedirectResponse
    {
        $data = $request->validate([
            'vector_engine' => ['required', 'in:local,external'],
            'external_model' => ['required', 'string'],
            'api_endpoint' => ['nullable', 'url'],
            'api_key' => ['nullable', 'string'],
        ]);

        $settingsRepository->set('categories.settings', $data);

        return back()->with('message', 'تنظیمات با موفقیت ذخیره شد.');
    }

    public function getCategoryTree(CategoryService $categoryService): JsonResponse
    {
        // One-time cleanup for redundant roots
        $categoryService->cleanupRoots();

        $digikala = $categoryService->getTree('digikala');
        $basalam = $categoryService->getTree('basalam');
        $snappshop = $categoryService->getTree('snappshop');
        $mappings = CategoryMapping::all();

        // Flatten digikala for select
        $digikalaFlat = Category::where('store', 'digikala')->get(['id', 'title']);

        return response()->json([
            'digikala' => $digikala,
            'basalam' => $basalam,
            'snappshop' => $snappshop,
            'digikala_flat' => $digikalaFlat,
            'mappings' => $mappings,
        ]);
    }

    public function getCategoryDetails(string $authkey, Category $category, CategoryVectorService $vectorService): JsonResponse
    {
        try {
            $similar = $vectorService->findSimilar($category);

            return response()->json([
                'category' => $category,
                'similar' => $similar,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function importSnappShopCategories(Request $request, CategoryService $categoryService): JsonResponse
    {
        $payload = $request->validate([
            'data' => ['required', 'array'],
            'data.menus' => ['required', 'array'],
        ]);

        $categoryService->importSnappShop($payload['data']['menus']);

        return response()->json(['ok' => true]);
    }

    public function testAiEmbedding(Request $request, CategoryVectorService $vectorService): JsonResponse
    {
        $data = $request->validate(['text' => ['required', 'string', 'max:500']]);

        return response()->json($vectorService->testEmbedding($data['text']));
    }

    public function saveCategoryMapping(Request $request): JsonResponse
    {
        $data = $request->validate([
            'basalam_category_id' => ['required', 'exists:categories,id'],
            'digikala_category_id' => ['required', 'exists:categories,id'],
        ]);

        CategoryMapping::updateOrCreate(
            ['source_category_id' => $data['basalam_category_id']],
            [
                'digikala_category_id' => $data['digikala_category_id'],
                'confidence' => 1.0,
                'is_manual' => true,
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function updateCategory(string $authkey, Category $category, Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $category->update(['title' => $data['title']]);

        return response()->json(['ok' => true]);
    }

    public function sitemapHub()
    {
        $isRunning = (bool) Cache::get('sitemap:running', false);
        $currentRun = SitemapRunLog::query()->where('status', 'running')->latest('id')->first();
        $lastRuns = SitemapRunLog::query()->latest('id')->limit(50)->get();
        $lastCompletedRun = SitemapRunLog::query()->where('status', 'completed')->latest('id')->first();
        $lastFailedRun = SitemapRunLog::query()->where('status', 'failed')->latest('id')->first();
        $totalRuns = SitemapRunLog::query()->count();
        $totalRunsCompleted = SitemapRunLog::query()->where('status', 'completed')->count();

        $lastCompletedForce = SitemapRunLog::query()
            ->where('status', 'completed')
            ->where('force_mode', true)
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();
        $lastCompletedIncremental = SitemapRunLog::query()
            ->where('status', 'completed')
            ->where('force_mode', false)
            ->whereNotNull('completed_at')
            ->latest('id')
            ->first();

        $forceDuration = null;
        if ($lastCompletedForce && $lastCompletedForce->started_at && $lastCompletedForce->completed_at) {
            $forceDuration = $lastCompletedForce->started_at->diffInSeconds($lastCompletedForce->completed_at);
        }
        $incrementalDuration = null;
        if ($lastCompletedIncremental && $lastCompletedIncremental->started_at && $lastCompletedIncremental->completed_at) {
            $incrementalDuration = $lastCompletedIncremental->started_at->diffInSeconds($lastCompletedIncremental->completed_at);
        }

        $completedRuns = SitemapRunLog::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('processed_products', '>', 0)
            ->latest('id')
            ->limit(10)
            ->get();

        $speeds = [];
        foreach ($completedRuns as $run) {
            $secs = $run->started_at?->diffInSeconds($run->completed_at);
            if ($secs && $secs > 0 && $run->processed_products > 0) {
                $speeds[] = $run->processed_products / $secs;
            }
        }
        $avgSpeed = !empty($speeds) ? round(array_sum($speeds) / count($speeds), 2) : null;

        $totalActive = Product::query()->where('is_active', true)->count();
        $pendingProducts = Product::query()
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('sitemapped_at')
                  ->orWhereColumn('updated_at', '>', 'sitemapped_at');
            })
            ->count();

        $estForceSeconds = $avgSpeed && $avgSpeed > 0 ? (int) round($totalActive / $avgSpeed) : null;
        $estIncrementalSeconds = $avgSpeed && $avgSpeed > 0 ? (int) round($pendingProducts / $avgSpeed) : null;

        $settings = app(\App\Repositories\SettingsRepository::class);
        $scheduleStart = (int) $settings->get('sitemap.schedule_start', 1);
        $scheduleEnd = (int) $settings->get('sitemap.schedule_end', 5);
        $scheduleEnabled = (bool) $settings->get('sitemap.schedule_enabled', true);
        $separateStores = (bool) $settings->get('sitemap.separate_stores', false);

        $nowTehran = now()->timezone('Asia/Tehran');
        $currentTehranHour = (int) $nowTehran->format('G');

        if ($scheduleEnabled) {
            if ($scheduleStart <= $scheduleEnd) {
                $inScheduleWindow = $currentTehranHour >= $scheduleStart && $currentTehranHour < $scheduleEnd;
            } else {
                $inScheduleWindow = $currentTehranHour >= $scheduleStart || $currentTehranHour < $scheduleEnd;
            }
        } else {
            $inScheduleWindow = true;
        }

        $chunkFiles = glob(public_path('sitemaps/sitemap-*.xml.gz'));
        $chunkCount = count($chunkFiles);

        $dkChunkFiles = glob(public_path('sitemaps/sitemap-*-dk-*.xml.gz'));
        $bsChunkFiles = glob(public_path('sitemaps/sitemap-*-bs-*.xml.gz'));
        $mixedChunkFiles = array_filter($chunkFiles, fn($f) => !preg_match('/-(dk|bs)-\d+\.xml\.gz$/', basename($f)));
        $dkChunkCount = count($dkChunkFiles);
        $bsChunkCount = count($bsChunkFiles);
        $mixedChunkCount = count($mixedChunkFiles);

        $sitemapIndexPath = public_path('sitemap.xml');
        $sitemapIndexExists = file_exists($sitemapIndexPath);
        $appUrl = rtrim(config('app.url'), '/');
        $sitemapIndexUrl = $sitemapIndexExists ? $appUrl.'/sitemap.xml' : null;

        $chunkFileUrls = [];
        if ($chunkCount > 0) {
            sort($chunkFiles);
            foreach ($chunkFiles as $file) {
                $chunkFileUrls[] = $appUrl.'/sitemaps/'.basename($file);
            }
        }

        $totalSize = '—';
        if ($chunkCount > 0) {
            $bytes = array_sum(array_map('filesize', $chunkFiles));
            $totalSize = $bytes > 1048576
                ? round($bytes / 1048576, 1).' MB'
                : round($bytes / 1024, 1).' KB';
        }

        return view('dash.admin.sitemap-hub', compact(
            'isRunning',
            'currentRun',
            'lastRuns',
            'lastCompletedRun',
            'lastFailedRun',
            'lastCompletedForce',
            'lastCompletedIncremental',
            'forceDuration',
            'incrementalDuration',
            'totalRuns',
            'totalRunsCompleted',
            'chunkCount',
            'dkChunkCount',
            'bsChunkCount',
            'mixedChunkCount',
            'sitemapIndexPath',
            'sitemapIndexExists',
            'sitemapIndexUrl',
            'chunkFileUrls',
            'totalSize',
            'totalActive',
            'pendingProducts',
            'estForceSeconds',
            'estIncrementalSeconds',
            'scheduleStart',
            'scheduleEnd',
            'scheduleEnabled',
            'separateStores',
            'nowTehran',
            'inScheduleWindow',
        ));
    }

    public function triggerSitemap(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $mode = $request->input('mode', 'incremental');

        if (Cache::get('sitemap:running')) {
            return back()->withErrors(['message' => 'فرآیند سایت مپ در حال اجراست. لطفاً پس از اتمام آن مجدداً تلاش کنید.']);
        }

        $separateStores = (bool) app(\App\Repositories\SettingsRepository::class)->get('sitemap.separate_stores', false);

        $force = $mode === 'force';
        $runId = now()->format('Ymd_His');

        SitemapRunLog::query()->create([
            'run_id' => $runId,
            'status' => 'running',
            'force_mode' => $force,
            'started_at' => now(),
            'total_products' => Product::query()->where('is_active', true)->count(),
        ]);

        Cache::put('sitemap:running', true, 86400);

        ProcessSitemapChunkJob::dispatch(
            $runId,
            lastId: null,
            force: $force,
            store: $separateStores ? 'dk' : '',
            separateStores: $separateStores,
        );

        $activityLogger->log(
            'sitemap.generate',
            auth('admin')->user(),
            $force ? 'شروع بازسازی کامل سایت مپ' : 'شروع پردازش افزایشی سایت مپ',
        );

        return back()->with('message', 'فرآیند تولید سایت مپ با موفقیت در صف پردازش قرار گرفت.');
    }

    public function saveSitemapSettings(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $request->validate([
            'schedule_start' => 'required|integer|min:0|max:23',
            'schedule_end' => 'required|integer|min:0|max:23',
            'schedule_enabled' => 'nullable|boolean',
            'separate_stores' => 'nullable|boolean',
        ]);

        $settings = app(\App\Repositories\SettingsRepository::class);
        $settings->set('sitemap.schedule_start', (int) $request->input('schedule_start'));
        $settings->set('sitemap.schedule_end', (int) $request->input('schedule_end'));
        $settings->set('sitemap.schedule_enabled', (bool) $request->input('schedule_enabled', false));
        $settings->set('sitemap.separate_stores', (bool) $request->input('separate_stores', false));

        $activityLogger->log(
            'sitemap.settings',
            auth('admin')->user(),
            'تنظیمات زمان‌بندی و جداسازی فروشگاه‌های سایت مپ به‌روزرسانی شد',
        );

        return back()->with('message', 'تنظیمات سایت مپ ذخیره شد.');
    }
}
