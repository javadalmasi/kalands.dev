<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-xl font-black text-slate-800 dark:text-white">تیکت‌ها</h1>
                <a href="{{ route('dash.user.tickets.create', ['authkey' => $authkey]) }}" class="btn-primary px-6 py-2.5 text-xs">ثبت تیکت جدید</a>
            </div>
            <div class="space-y-4">
                @forelse($tickets as $ticket)
                    <a href="{{ route('dash.user.tickets.show', ['authkey' => $authkey, 'ticket' => $ticket->id]) }}" class="group block rounded-squircle border border-slate-50 bg-slate-50/30 p-5 transition-all hover:bg-slate-50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <p class="truncate text-base font-bold text-slate-800 dark:text-white group-hover:text-primary transition-colors">{{ $ticket->subject }}</p>
                                <div class="mt-3 flex items-center gap-4 text-[11px] font-medium text-slate-400">
                                    <span class="flex items-center gap-1.5"><span class="material-icons !text-sm">category</span>{{ $ticket->category->name ?? '-' }}</span>
                                    <span class="flex items-center gap-1.5"><span class="material-icons !text-sm">schedule</span>{{ $ticket->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            <div class="text-left shrink-0">
                                @if($ticket->status === 'answered')
                                    <span class="rounded-full bg-success/10 px-3 py-1 text-[10px] font-black text-success">پاسخ داده شده</span>
                                @elseif($ticket->status === 'open')
                                    <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-black text-primary">در جریان</span>
                                @elseif($ticket->status === 'closed')
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black text-slate-500">بسته شده</span>
                                @elseif($ticket->status === 'spam')
                                    <span class="rounded-full bg-warning/10 px-3 py-1 text-[10px] font-black text-warning">اسپم</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black text-slate-500">{{ $ticket->status }}</span>
                                @endif
                                @if($ticket->status === 'answered')
                                    <p class="mt-2 text-[10px] font-black text-success flex items-center gap-1 justify-end animate-pulse"><span class="material-icons !text-xs">reply</span>پاسخ جدید</p>
                                @endif
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="rounded-squircle border border-dashed border-slate-200 py-16 text-center dark:border-white/10">
                        <span class="material-icons !text-5xl text-slate-200 dark:text-white/10 mb-4">confirmation_number</span>
                        <p class="text-sm font-medium text-slate-400">هنوز هیچ تیکت یا درخواستی ثبت نکرده‌اید.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.profile>
