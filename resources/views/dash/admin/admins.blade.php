<x-layouts.admin-dashboard title="مدیریت ادمین‌ها">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="مدیریت ادمین‌ها">
        <x-slot:actions>
            <button type="button" class="admin-btn" onclick="document.getElementById('add-admin-dialog').showModal()">
                <span class="material-icons">person_add</span>
                افزودن ادمین جدید
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="space-y-3">
        @foreach($admins as $adminItem)
            <div class="admin-card is-surface !p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">admin_panel_settings</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate text-sm">{{ $adminItem->full_name ?: $adminItem->username }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="admin-ltr font-mono">@ {{ $adminItem->username }}</span>
                                <span class="admin-ltr">{{ $adminItem->email_address ?: $adminItem->mobile_number ?: '-' }}</span>
                                <div class="flex gap-1">
                                    @foreach($adminItem->roles as $role)
                                        <span class="bg-primary/5 text-primary px-1.5 py-0.5 rounded border border-primary/10">{{ $role->label }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 border-t border-slate/5 pt-3 md:border-0 md:pt-0">
                        <div class="flex flex-col gap-1 items-center">
                            <span class="text-[9px] font-bold opacity-40">وضعیت</span>
                            <label class="admin-switch">
                                <input type="checkbox" class="admin-switch-input admin-status-toggle" data-id="{{ $adminItem->id }}" {{ $adminItem->is_active ? 'checked' : '' }}>
                                <div class="admin-switch-track"></div>
                                <div class="admin-switch-ball"></div>
                            </label>
                            <form id="toggle-form-{{ $adminItem->id }}" action="{{ route('dash.admin.admins.update', ['authkey' => $authkey, 'admin' => $adminItem->id]) }}" method="POST" class="hidden">
                                @csrf
                                <input type="hidden" name="is_active" value="{{ $adminItem->is_active ? 0 : 1 }}">
                                <input type="hidden" name="full_name" value="{{ $adminItem->full_name ?: $adminItem->username }}">
                                @foreach($adminItem->roles as $role)
                                    <input type="hidden" name="roles[]" value="{{ $role->id }}">
                                @endforeach
                            </form>
                        </div>

                        <button type="button" class="admin-btn !p-2" onclick="document.getElementById('edit-admin-dialog-{{ $adminItem->id }}').showModal()" title="ویرایش">
                            <span class="material-icons !text-base">edit</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Edit Admin Dialog -->
            <dialog id="edit-admin-dialog-{{ $adminItem->id }}" class="admin-dialog w-[min(100vw-16px,720px)] max-w-[720px]">
                <div class="admin-dialog-body">
                    <div class="admin-dialog-head">
                        <h3 class="admin-dialog-title">ویرایش ادمین #{{ $adminItem->id }}</h3>
                        <button type="button" class="admin-toggle inline-flex" onclick="this.closest('dialog').close()"><span class="material-icons">close</span></button>
                    </div>

                    <form action="{{ route('dash.admin.admins.update', ['authkey' => $authkey, 'admin' => $adminItem->id]) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid gap-3 md:grid-cols-2">
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">نام کامل</label>
                                <input name="full_name" value="{{ $adminItem->full_name }}" class="admin-dialog-input" placeholder="نام کامل">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">نام کاربری</label>
                                <input name="username" value="{{ $adminItem->username }}" class="admin-dialog-input admin-ltr" placeholder="نام کاربری">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">ایمیل</label>
                                <input name="email_address" value="{{ $adminItem->email_address }}" class="admin-dialog-input admin-ltr" placeholder="ایمیل">
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-bold opacity-60">موبایل</label>
                                <input name="mobile_number" value="{{ $adminItem->mobile_number }}" class="admin-dialog-input admin-ltr" placeholder="موبایل">
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
                                            @checked($adminItem->roles->contains($role->id))
                                            class="rounded border-slate-300 text-primary-500">
                                        <span class="text-[11px] text-slate/70 group-hover:text-primary transition-colors">{{ $role->label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-1">
                            <label class="text-[10px] font-bold opacity-60">تغییر رمز عبور (خالی بگذارید تا تغییر نکند)</label>
                            <input name="password" type="password" class="admin-dialog-input admin-ltr" placeholder="رمز عبور جدید">
                        </div>

                        <div class="flex gap-2">
                            <button class="admin-btn flex-1 justify-center"><span class="material-icons">save</span>ذخیره تغییرات</button>
                            <button type="button" class="admin-btn admin-btn-danger"
                                onclick="if(confirm('آیا از حذف این ادمین مطمئن هستید؟')) { document.getElementById('delete-admin-form-{{ $adminItem->id }}').submit(); }">
                                <span class="material-icons">delete</span>
                            </button>
                        </div>
                    </form>
                    <form id="delete-admin-form-{{ $adminItem->id }}" action="{{ route('dash.admin.admins.delete', ['authkey' => $authkey, 'admin' => $adminItem->id]) }}" method="POST" class="hidden">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </dialog>
        @endforeach
    </div>

    <x-admin.pagination :paginator="$admins" />

    <!-- Add Admin Dialog -->
    <dialog id="add-admin-dialog" class="admin-dialog w-[min(100vw-16px,720px)] max-w-[720px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">افزودن ادمین جدید</h3>
                <button type="button" class="admin-toggle inline-flex" onclick="this.closest('dialog').close()"><span class="material-icons">close</span></button>
            </div>

            <form action="{{ route('dash.admin.admins.store', ['authkey' => $authkey]) }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid gap-3 md:grid-cols-2">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold opacity-60 px-1">نام کامل</label>
                        <input name="full_name" class="admin-dialog-input" placeholder="نام کامل">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold opacity-60 px-1">نام کاربری</label>
                        <input name="username" class="admin-dialog-input admin-ltr" placeholder="نام کاربری">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold opacity-60 px-1">ایمیل</label>
                        <input name="email_address" class="admin-dialog-input admin-ltr" placeholder="ایمیل">
                    </div>
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold opacity-60 px-1">موبایل</label>
                        <input name="mobile_number" class="admin-dialog-input admin-ltr" placeholder="موبایل">
                    </div>
                </div>

                <div class="admin-card !p-0 overflow-hidden border border-slate-100 dark:border-slate-800">
                    <div class="bg-slate-50 dark:bg-slate-900 p-2 border-b border-slate-100 dark:border-slate-800">
                        <span class="text-[10px] font-bold text-slate">تخصیص نقش‌ها</span>
                    </div>
                    <div class="p-3 grid gap-2 grid-cols-2 md:grid-cols-4">
                        @foreach(\App\Models\Role::all() as $role)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" class="rounded border-slate-300 text-primary-500">
                                <span class="text-[11px] text-slate/70 group-hover:text-primary transition-colors">{{ $role->label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold opacity-60 px-1">رمز عبور</label>
                    <input name="password" type="password" class="admin-dialog-input admin-ltr" placeholder="رمز عبور">
                </div>

                <button class="admin-btn w-full justify-center h-12 font-bold"><span class="material-icons">person_add</span>ایجاد ادمین</button>
            </form>
        </div>
    </dialog>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-admins.js'])
