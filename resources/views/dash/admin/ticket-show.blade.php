<x-layouts.admin-dashboard title="جزئیات تیکت">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">{{ $ticket->subject }}</h1>
    <p class="mb-4 text-xs text-slate">کاربر: {{ $ticket->user->name ?? '-' }} | وضعیت: {{ $ticket->status }}</p>

    <div class="flex flex-wrap gap-4 mb-4">
        <form action="{{ route('dash.admin.tickets.status', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" method="POST" class="admin-card flex-1 admin-actions">
        @csrf
        <select name="status" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="open" @selected($ticket->status === 'open')>open</option>
            <option value="answered" @selected($ticket->status === 'answered')>answered</option>
            <option value="closed" @selected($ticket->status === 'closed')>closed</option>
        </select>
            <button class="admin-btn"><span class="material-icons">save</span>ذخیره وضعیت</button>
        </form>

        <form action="{{ route('dash.admin.tickets.user.toggle-block', ['authkey' => $authkey]) }}" method="POST" class="admin-card flex-1 admin-actions" data-admin-confirm="آیا از تغییر وضعیت مسدودیت این کاربر مطمئن هستید؟">
            @csrf
            <input type="hidden" name="user_id" value="{{ $ticket->user_id }}">
            <input type="hidden" name="action" value="{{ $isUserBlocked ? 'unblock' : 'block' }}">
            <button class="admin-btn {{ $isUserBlocked ? 'admin-btn-secondary text-success' : 'admin-btn-danger' }} w-full justify-center">
                <span class="material-icons">{{ $isUserBlocked ? 'lock_open' : 'block' }}</span>
                {{ $isUserBlocked ? 'رفع مسدودیت کاربر' : 'مسدود کردن کاربر' }}
            </button>
        </form>
    </div>

    <div class="mb-4 space-y-4">
        @foreach($ticket->messages as $message)
            <div class="admin-card {{ $message->sender_type === 'admin' ? 'border-r-4 border-success' : 'border-r-4 border-primary' }}">
                <div class="flex items-center justify-between gap-4 mb-3 border-b border-slate/5 pb-2">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-base opacity-40">{{ $message->sender_type === 'admin' ? 'support_agent' : 'person' }}</span>
                        <p class="text-xs font-bold text-slate">
                            @if($message->sender_type === 'admin')
                                {{ $message->admin->full_name ?? 'ادمین' }} (تیم پشتیبانی)
                            @else
                                {{ $message->user->name ?? 'کاربر' }}
                            @endif
                        </p>
                    </div>
                    <span class="text-[10px] opacity-40">{{ persianTimeAgo($message->created_at) }}</span>
                </div>
                <div class="text-sm leading-relaxed prose dark:prose-invert max-w-none">
                    {!! $message->message !!}
                </div>
            </div>
        @endforeach
    </div>

    <form action="{{ route('dash.admin.tickets.reply', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" method="POST" class="admin-card space-y-2">
        @csrf
        <textarea name="message" rows="12" data-rich-editor class="w-full rounded bg-slate p-3 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="پاسخ ادمین"></textarea>
        <button class="admin-btn"><span class="material-icons">send</span>ارسال پاسخ</button>
    </form>
</x-layouts.admin-dashboard>
