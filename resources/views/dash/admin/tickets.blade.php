<x-layouts.admin-dashboard title="تیکت‌ها">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">تیکت‌ها</h1>

    <form method="GET" class="admin-card mb-4 grid gap-3 md:grid-cols-3">
        <select name="sort" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
            <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
            <option value="subject" @selected(($sort ?? '') === 'subject')>موضوع</option>
            <option value="status" @selected(($sort ?? '') === 'status')>وضعیت</option>
        </select>
        <select name="status" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
            <option value="open" @selected(($status ?? '') === 'open')>باز</option>
            <option value="answered" @selected(($status ?? '') === 'answered')>پاسخ داده‌شده</option>
            <option value="closed" @selected(($status ?? '') === 'closed')>بسته</option>
        </select>
        <button class="admin-btn justify-center" type="submit"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
    </form>

    <form id="bulk-tickets-form" method="POST" action="{{ route('dash.admin.tickets.bulk', ['authkey' => $authkey]) }}" class="admin-card mb-4">
        @csrf
        <div class="admin-actions">
            <label class="inline-flex items-center gap-2 text-xs">
                <input type="checkbox" id="select-all-tickets">
                انتخاب همه
            </label>
            <select name="action" class="rounded p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="open">باز</option>
                <option value="answered">پاسخ داده‌شده</option>
                <option value="closed">بسته</option>
                <option value="delete">حذف</option>
            </select>
            <button class="admin-btn" type="submit" data-admin-confirm="این اقدام روی تیکت‌های انتخاب‌شده اجرا می‌شود. ادامه می‌دهید؟"><span class="material-icons">done_all</span>اجرای گروهی</button>
        </div>
    </form>

    <div class="space-y-2">
        @foreach($tickets as $ticket)
            <div class="admin-card">
                <div class="flex flex-wrap items-start gap-3">
                    <input type="checkbox" form="bulk-tickets-form" name="ticket_ids[]" value="{{ $ticket->id }}" class="ticket-item-checkbox mt-1">
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

    <div class="mt-4">{{ $tickets->links() }}</div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-tickets.js'])
