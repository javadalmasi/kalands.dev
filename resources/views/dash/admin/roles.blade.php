<x-layouts.admin-dashboard title="مدیریت نقش‌ها و دسترسی‌ها">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="مدیریت نقش‌ها و دسترسی‌ها">
        <x-slot:actions>
            <a href="{{ route('dash.admin.roles.create', ['authkey' => $authkey]) }}" class="admin-btn">
                <span class="material-icons">add_moderator</span>
                ایجاد نقش جدید
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="space-y-3">
        @foreach($roles as $role)
            <div class="admin-card is-surface !p-4">
                <div class="flex flex-col md:flex-row md:items-center gap-4">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="rounded-full bg-primary/10 p-2 text-primary">
                            <span class="material-icons">security</span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="font-bold text-slate text-sm">{{ $role->label }}</h3>
                            <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] text-slate/60">
                                <span class="admin-ltr font-mono">{{ $role->name }}</span>
                                <span class="bg-primary/5 text-primary px-1.5 py-0.5 rounded border border-primary/10">ادمین‌ها: {{ $role->admins_count }}</span>
                                <span class="bg-primary/5 text-primary px-1.5 py-0.5 rounded border border-primary/10">کاربران: {{ $role->users_count }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 border-t border-slate/5 pt-3 md:border-0 md:pt-0">
                        <a href="{{ route('dash.admin.roles.edit', ['authkey' => $authkey, 'role' => $role->id]) }}" class="admin-btn !py-2" title="ویرایش دسترسی‌ها">
                            <span class="material-icons !text-base">rule</span>
                            ویرایش دسترسی‌ها
                        </a>
                        <form action="{{ route('dash.admin.roles.delete', ['authkey' => $authkey, 'role' => $role->id]) }}" method="POST" class="m-0" data-admin-confirm="از حذف این نقش مطمئن هستید؟">
                            @csrf
                            @method('DELETE')
                            <button class="admin-btn admin-btn-danger !p-2">
                                <span class="material-icons !text-base">delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.admin-dashboard>
