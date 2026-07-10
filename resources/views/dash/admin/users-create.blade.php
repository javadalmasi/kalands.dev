<x-layouts.admin-dashboard title="افزودن کاربر جدید">
    @php($authkey = request()->route('authkey'))
    <x-admin.page-header title="افزودن کاربر جدید">
        <x-slot:actions>
            <a href="{{ route('dash.admin.users', ['authkey' => $authkey]) }}" class="admin-btn admin-btn-secondary" title="بازگشت">
                <span class="material-icons !text-base">arrow_forward</span>
                بازگشت به کاربران
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <form action="{{ route('dash.admin.users.store', ['authkey' => $authkey]) }}" method="POST" class="admin-card is-surface space-y-6">
        @csrf
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">نام</label>
                <input name="first_name" value="{{ old('first_name') }}" placeholder="مثلاً: علی" class="admin-input">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">نام خانوادگی</label>
                <input name="last_name" value="{{ old('last_name') }}" placeholder="مثلاً: محمدی" class="admin-input">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">آدرس ایمیل</label>
                <input name="email" value="{{ old('email') }}" placeholder="email@example.com" class="admin-input admin-ltr">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">شماره موبایل</label>
                <input name="phone" value="{{ old('phone') }}" placeholder="09123456789" class="admin-input admin-ltr">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">رمز عبور</label>
                <input type="password" name="password" placeholder="••••••••" class="admin-input admin-ltr">
            </div>
            <div class="space-y-1">
                <label class="text-[10px] font-bold opacity-60 px-1">وضعیت حساب</label>
                <select name="is_active" class="admin-input">
                    <option value="1" @selected(old('is_active', '1') == '1')>فعال</option>
                    <option value="0" @selected(old('is_active') == '0')>غیرفعال</option>
                </select>
            </div>
        </div>

        <div class="admin-card !p-0 overflow-hidden border border-slate-100 dark:border-slate-800">
            <div class="bg-slate-50 dark:bg-slate-900 p-3 border-b border-slate-100 dark:border-slate-800">
                <span class="text-xs font-bold text-slate">انتخاب نقش‌ها و دسترسی‌ها</span>
            </div>
            <div class="p-4 grid gap-3 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                @foreach(\App\Models\Role::all() as $role)
                    <label class="flex items-center gap-3 cursor-pointer group p-2 rounded-lg hover:bg-primary/5 transition-colors">
                        <input type="checkbox" name="roles[]" value="{{ $role->id }}" @checked(in_array($role->id, old('roles', []))) class="h-5 w-5 rounded border-slate-300 text-primary-500 focus:ring-primary/20">
                        <span class="text-xs font-medium text-slate/80 group-hover:text-primary">{{ $role->label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="pt-4 border-t border-white/5">
            <button class="admin-btn admin-btn-primary w-full md:w-auto px-10 h-12 font-bold">
                <span class="material-icons">person_add</span>
                ثبت و ایجاد کاربر
            </button>
        </div>
    </form>
</x-layouts.admin-dashboard>
