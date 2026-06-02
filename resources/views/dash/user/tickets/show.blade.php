<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <div class="mb-8 flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 pb-6 dark:border-white/5">
                <div class="min-w-0">
                    <h1 class="text-xl font-black text-slate-800 dark:text-white">{{ $ticket->subject }}</h1>
                    <div class="mt-3 flex items-center gap-3">
                        <span class="rounded-full bg-slate-50 px-3 py-1 text-[10px] font-black text-slate-500 dark:bg-white/5">کد تیکت: #{{ $ticket->id }}</span>
                        <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-black text-primary">وضعیت: {{ $ticket->status }}</span>
                    </div>
                </div>
                @if($ticket->status !== 'closed')
                    <form action="{{ route('dash.user.tickets.close', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" method="POST" data-admin-confirm="آیا از بستن این تیکت مطمئن هستید؟">
                        @csrf
                        <button class="flex items-center gap-2 rounded-squircle bg-warning/10 px-4 py-2.5 text-xs font-black text-warning transition-all hover:bg-warning hover:text-white active:scale-95">
                            <span class="material-icons !text-base">lock</span>
                            بستن و اتمام تیکت
                        </button>
                    </form>
                @endif
            </div>

            @php($ticketSettings = app(\App\Repositories\SettingsRepository::class)->get('tickets.settings', []))
            <div class="mb-8 space-y-6">
                @foreach($ticket->messages as $message)
                    <div class="rounded-squircle border border-slate-50 p-5 {{ $message->sender_type === 'admin' ? 'bg-primary/5 border-primary/10' : 'bg-slate-50/50' }} dark:border-white/5 dark:bg-white/5">
                        <div class="flex items-center justify-between gap-4 mb-4 border-b border-slate-100 dark:border-white/5 pb-3">
                            <div class="flex items-center gap-3">
                                <div class="grid h-8 w-8 place-items-center rounded-full {{ $message->sender_type === 'admin' ? 'bg-primary text-white' : 'bg-slate-200 text-slate-500' }}">
                                    <span class="material-icons !text-sm !leading-none block">{{ $message->sender_type === 'admin' ? 'support_agent' : 'person' }}</span>
                                </div>
                                <p class="text-xs font-black text-slate-800 dark:text-white">
                                    @if($message->sender_type === 'admin')
                                        {{ ($ticketSettings['hide_admin_name'] ?? false) ? 'پاسخ پشتیبانی' : ($message->admin->full_name ?? 'تیم پشتیبانی') }}
                                    @else
                                        {{ $message->user->name ?? 'شما' }}
                                    @endif
                                </p>
                            </div>
                            <span class="text-[10px] font-bold text-slate-400">{{ $message->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="text-sm leading-relaxed text-slate-700 dark:text-slate-300 prose dark:prose-invert max-w-none">
                            {!! $message->message !!}
                        </div>
                    </div>
                @endforeach
            </div>

            @if($ticket->status !== 'closed')
                <form action="{{ route('dash.user.tickets.reply', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 pr-2">متن پاسخ شما</label>
                        <textarea name="message" rows="5" class="w-full rounded-squircle border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10" placeholder="پاسخ خود را اینجا بنویسید..."></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button class="btn-primary w-full px-12 py-3.5 text-sm font-black md:w-auto">ارسال پاسخ</button>
                    </div>
                </form>
            @else
                <div class="rounded-squircle bg-slate-50 p-6 text-center dark:bg-white/5">
                    <p class="text-sm font-bold text-slate-400">این تیکت بسته شده است و امکان ارسال پاسخ وجود ندارد.</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.profile>
