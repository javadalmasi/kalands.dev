<x-layouts.admin-dashboard title="مدیریت کاربران">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="مدیریت کاربران">
        <x-slot:actions>
            <a href="{{ route('dash.admin.users.create', ['authkey' => $authkey]) }}" class="admin-btn">
                <span class="material-icons">person_add</span>
                افزودن کاربر جدید
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.filter-bar cols="4">
        <x-admin.filter-field label="جستجو (نام، ایمیل، موبایل)">
            <input type="text" name="q" value="{{ $q }}" placeholder="جستجو..." class="admin-input">
        </x-admin.filter-field>
        <x-admin.filter-field label="مرتب‌سازی">
            <select name="sort" class="admin-input">
                <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                <option value="name_asc" @selected(($sort ?? '') === 'name_asc')>نام (صعودی)</option>
                <option value="name_desc" @selected(($sort ?? '') === 'name_desc')>نام (نزولی)</option>
            </select>
        </x-admin.filter-field>
        <x-admin.filter-field label="" span="2">
            <div class="flex items-end h-full">
                <button class="admin-btn justify-center w-full h-[38px]"><span class="material-icons">search</span>جستجو</button>
            </div>
        </x-admin.filter-field>
    </x-admin.filter-bar>

    <x-admin.bulk-bar
        action="{{ route('dash.admin.users.bulk', ['authkey' => $authkey]) }}"
        id="bulk-action-form"
        label="کاربر"
        confirm="این اقدام روی کاربران انتخاب‌شده اجرا می‌شود. ادامه می‌دهید؟">
        <x-slot:actions>
            <select name="action" id="bulk-action-select" class="admin-input !w-auto !h-8 !text-xs !min-h-0">
                <option value="">عملیات گروهی...</option>
                <option value="activate">فعال‌سازی</option>
                <option value="deactivate">غیرفعال‌سازی</option>
                <option value="assign_role">تخصیص نقش</option>
                <option value="delete">حذف</option>
            </select>
            <select name="role_id" id="bulk-role-select" class="hidden admin-input !w-auto !h-8 !text-xs !min-h-0">
                @foreach(\App\Models\Role::all() as $role)
                    <option value="{{ $role->id }}">{{ $role->label }}</option>
                @endforeach
            </select>
            <button type="submit" class="admin-btn admin-btn-primary !h-8 !text-xs">
                <span class="material-icons !text-sm">done_all</span>
                اجرا
            </button>
        </x-slot:actions>
    </x-admin.bulk-bar>

    <div class="space-y-3">
        @foreach($users as $user)
            <div class="admin-card is-surface !p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" form="bulk-action-form" data-bulk-item class="user-checkbox rounded border-slate-300">
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate text-sm">{{ trim($user->first_name.' '.$user->last_name) ?: 'بدون نام' }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="admin-ltr font-mono">#{{ $user->id }}</span>
                                <span class="admin-ltr">{{ $user->email ?: $user->phone ?: '-' }}</span>
                                <span class="flex items-center gap-1">
                                    <span class="material-icons !text-xs">schedule</span>
                                    عضویت: {{ persianDate($user->created_at) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-slate/5 pt-3 md:border-0 md:pt-0">
                        <div class="flex flex-col gap-1 items-center">
                            <span class="text-[9px] font-bold opacity-40">وضعیت</span>
                            <label class="admin-switch"><input type="checkbox" class="admin-switch-input user-status-toggle" data-id="{{ $user->id }}"><div class="admin-switch-track"></div><div class="admin-switch-ball"></div></label>
                            <form id="toggle-form-{{ $user->id }}" action="{{ route('dash.admin.users.update', ['authkey' => $authkey, 'user' => $user->id]) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="is_active" value="{{ $user->is_active ? 0 : 1 }}">
                                <input type="hidden" name="first_name" value="{{ $user->first_name }}">
                                <input type="hidden" name="last_name" value="{{ $user->last_name }}">
                            </form>
                        </div>

                        <button type="button" class="admin-btn user-modal-open !p-2" data-modal-id="user-modal-{{ $user->id }}" title="ویرایش">
                            <span class="material-icons !text-base">edit</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modal remains largely the same but with improved styling -->
            <dialog id="user-modal-{{ $user->id }}" class="admin-dialog w-[min(100vw-16px,720px)] max-w-[720px]">
                <div class="admin-dialog-body">
                    <div class="admin-dialog-head">
                        <h3 class="admin-dialog-title">ویرایش کاربر #{{ $user->id }}</h3>
                        <button type="button" class="admin-toggle inline-flex user-modal-close" data-modal-id="user-modal-{{ $user->id }}"><span class="material-icons">close</span></button>
                    </div>

                    <form action="{{ route('dash.admin.users.update', ['authkey' => $authkey, 'user' => $user->id]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">نام</label>
                                <input name="first_name" value="{{ $user->first_name }}" class="admin-dialog-input" placeholder="نام">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">نام خانوادگی</label>
                                <input name="last_name" value="{{ $user->last_name }}" class="admin-dialog-input" placeholder="نام خانوادگی">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">ایمیل</label>
                                <input name="email" value="{{ $user->email }}" class="admin-dialog-input admin-ltr" placeholder="ایمیل">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">موبایل</label>
                                <input name="phone" value="{{ $user->phone }}" class="admin-dialog-input admin-ltr" placeholder="موبایل">
                            </div>
                        </div>

                        <div class="admin-card !p-0 overflow-hidden border border-white/5">
                            <div class="bg-slate-50 dark:bg-slate-800 p-2 border-b border-white/5">
                                <span class="text-[10px] font-bold text-slate">تخصیص نقش‌ها</span>
                            </div>
                            <div class="p-3 grid gap-2 grid-cols-2 md:grid-cols-4">
                                @foreach(\App\Models\Role::all() as $role)
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input type="checkbox" name="roles[]" value="{{ $role->id }}"
                                            @checked($user->roles->contains($role->id))
                                            class="rounded border-slate-300 text-primary-500">
                                        <span class="text-[11px] text-slate/70 group-hover:text-primary transition-colors">{{ $role->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        <button class="admin-btn w-full justify-center"><span class="material-icons">save</span>ذخیره تغییرات</button>
                    </form>

                    <div class="pt-4 border-t border-white/5 flex flex-wrap gap-2">
                        <form action="{{ route('dash.admin.users.password', ['authkey' => $authkey, 'user' => $user->id]) }}" method="POST" class="flex-1 flex gap-2">
                            @csrf
                            <input type="password" name="password" placeholder="رمز جدید" class="admin-dialog-input admin-ltr !min-h-[38px]">
                            <button class="admin-btn whitespace-nowrap"><span class="material-icons">lock_reset</span>تغییر رمز</button>
                        </form>
                        <form action="{{ route('dash.admin.users.delete', ['authkey' => $authkey, 'user' => $user->id]) }}" method="POST" class="m-0" data-admin-confirm="از حذف این کاربر مطمئن هستید؟">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn admin-btn-danger"><span class="material-icons">delete</span>حذف کاربر</button>
                        </form>
                    </div>
                </div>
            </dialog>
        @endforeach
    </div>

    <x-admin.pagination :paginator="$users" />

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-users.js'])
