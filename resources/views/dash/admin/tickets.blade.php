<x-layouts.admin-dashboard title="تیکت‌ها" :helpModuleKey="'tickets'">
    @php($authkey = request()->route('authkey'))

    @vite(['resources/js/admin-tickets.js'])

    <x-admin.page-header title="تیکت‌ها" />

    <x-admin.filter-bar :cols="3">
        <x-admin.filter-field label="مرتب‌سازی">
            <select name="sort" class="admin-input">
                <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                <option value="subject" @selected(($sort ?? '') === 'subject')>موضوع</option>
                <option value="status" @selected(($sort ?? '') === 'status')>وضعیت</option>
            </select>
        </x-admin.filter-field>
        <x-admin.filter-field label="وضعیت">
            <select name="status" class="admin-input">
                <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
                <option value="open" @selected(($status ?? '') === 'open')>باز</option>
                <option value="answered" @selected(($status ?? '') === 'answered')>پاسخ داده‌شده</option>
                <option value="closed" @selected(($status ?? '') === 'closed')>بسته</option>
            </select>
        </x-admin.filter-field>
        <x-admin.filter-field label=" ">
            <button class="admin-btn justify-center w-full" type="submit"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
        </x-admin.filter-field>
    </x-admin.filter-bar>

    <x-admin.bulk-bar
        action="{{ route('dash.admin.tickets.bulk', ['authkey' => $authkey]) }}"
        id="bulk-tickets-form"
        label="اجرای گروهی"
        confirm="این اقدام روی تیکت‌های انتخاب‌شده اجرا می‌شود. ادامه می‌دهید؟"
    >
        <x-slot:actions>
            <select name="action" class="admin-input text-xs">
                <option value="open">باز</option>
                <option value="answered">پاسخ داده‌شده</option>
                <option value="closed">بسته</option>
                <option value="delete">حذف</option>
            </select>
        </x-slot:actions>
    </x-admin.bulk-bar>

    <div class="space-y-2">
        @foreach($tickets as $ticket)
            <div class="admin-card">
                <div class="flex flex-wrap items-start gap-3">
                    <input type="checkbox" data-bulk-item form="bulk-tickets-form" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-item-checkbox mt-1">
                    <a href="{{ route('dash.admin.tickets.show', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" class="min-w-0 flex-1">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold text-slate">{{ $ticket->subject }}</p>
                            <span class="rounded-full border border-slate px-2 py-1 text-xs text-slate">{{ $ticket->status }}</span>
                        </div>
                        <p class="mt-1 text-xs text-slate">{{ $ticket->user->name ?? '-' }} | {{ $ticket->category->name ?? '-' }}</p>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <x-admin.pagination :paginator="$tickets" />

</x-layouts.admin-dashboard>
