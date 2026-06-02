<x-layouts.admin-dashboard title="{{ isset($role) ? 'ویرایش نقش: ' . $role->label : 'ایجاد نقش جدید' }}">
    @php($authkey = request()->route('authkey'))
    <div class="flex items-center justify-between gap-4 mb-6">
        <h1 class="admin-page-title !mb-0">{{ isset($role) ? 'ویرایش نقش: ' . $role->label : 'ایجاد نقش جدید' }}</h1>
        <a href="{{ route('dash.admin.roles', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary" title="بازگشت">
            <span class="material-icons !text-base">arrow_forward</span>
            بازگشت به نقش‌ها
        </a>
    </div>

    <form action="{{ isset($role) ? route('dash.admin.roles.update', ['authkey' => $authkey, 'role' => $role->id]) : route('dash.admin.roles.store', ['authkey' => $authkey]) }}" method="POST">
        @csrf
        <div class="admin-card is-surface grid gap-4 md:grid-cols-2 mb-8">
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">نام انگلیسی نقش (مثلاً: manager)</label>
                <input name="name" value="{{ old('name', $role->name ?? '') }}" placeholder="manager" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700 admin-ltr">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">عنوان فارسی نقش (مثلاً: مدیر ارشد)</label>
                <input name="label" value="{{ old('label', $role->label ?? '') }}" placeholder="مدیر ارشد" class="w-full rounded-lg border border-slate p-2.5 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            </div>
        </div>

        <h2 class="mb-4 font-bold text-slate">تعیین سطح دسترسی</h2>

        <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($permissions as $module => $modulePermissions)
                <div class="admin-card !p-0 overflow-hidden">
                    <div class="bg-slate-50 dark:bg-slate-800 p-3 border-b border-white/10 flex items-center justify-between">
                        <h3 class="font-bold text-sm">{{ $module }}</h3>
                        <button type="button" class="text-[10px] text-primary hover:underline select-all-module" data-module="{{ Str::slug($module) }}">انتخاب همه</button>
                    </div>
                    <div class="p-4 space-y-3" id="module-{{ Str::slug($module) }}">
                        @foreach($modulePermissions as $permission)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                    @checked(in_array($permission->id, old('permissions', $rolePermissions ?? [])))
                                    class="w-4 h-4 rounded border-slate-300 text-primary-500 focus:ring-primary">
                                <span class="text-sm text-slate group-hover:text-primary transition-colors">{{ $permission->label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-8 pt-6 border-t border-white/10 flex justify-end gap-3">
            <button type="submit" class="admin-btn admin-btn-primary px-10 h-12 font-bold shadow-lg shadow-primary/20">
                <span class="material-icons">save</span>
                {{ isset($role) ? 'بروزرسانی و ذخیره نقش' : 'ثبت و ایجاد نقش جدید' }}
            </button>
        </div>
    </form>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-roles-edit.js'])
