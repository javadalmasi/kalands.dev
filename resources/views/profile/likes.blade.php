<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <h1 class="mb-6 text-xl font-black text-slate-800 dark:text-white">پسندیده‌ها</h1>

            <div class="mb-10 grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div class="group flex flex-col items-center justify-center rounded-2xl border border-slate-100 bg-white p-6 shadow-sm transition-all hover:border-slate-200 dark:border-white/5 dark:bg-white/5">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-slate-50 text-slate-400 transition-colors group-hover:bg-slate-100 group-hover:text-slate-600 dark:bg-white/5">
                        <span class="material-icons !text-2xl">assessment</span>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">کل آرای ثبت شده</p>
                    <p class="mt-1 text-3xl font-black text-slate-800 dark:text-white">{{ number_format($commentVotesSummary['total']) }}</p>
                </div>
                <div class="group flex flex-col items-center justify-center rounded-2xl border border-primary/10 bg-primary/5 p-6 shadow-sm transition-all hover:border-primary/20">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-white">
                        <span class="material-icons !text-2xl">thumb_up</span>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-primary/70">نظرات مثبت</p>
                    <p class="mt-1 text-3xl font-black text-primary">{{ number_format($commentVotesSummary['likes']) }}</p>
                </div>
                <div class="group flex flex-col items-center justify-center rounded-2xl border border-warning/10 bg-warning/5 p-6 shadow-sm transition-all hover:border-warning/20">
                    <div class="mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-warning/10 text-warning transition-colors group-hover:bg-warning group-hover:text-white">
                        <span class="material-icons !text-2xl">thumb_down</span>
                    </div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-warning/70">نظرات منفی</p>
                    <p class="mt-1 text-3xl font-black text-warning">{{ number_format($commentVotesSummary['dislikes']) }}</p>
                </div>
            </div>

            <div class="space-y-3">
                @forelse($likes as $like)
                    <div class="flex items-center justify-between rounded-squircle border border-slate-100 bg-slate-50/30 p-4 transition-all hover:bg-slate-50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10">
                        <p class="text-sm font-medium text-slate-700 dark:text-white">{{ $like->product->title ?? 'محصول' }}</p>
                        <a class="text-xs font-bold text-warning hover:underline" href="{{ route('dash.user.likes.delete', ['authkey' => $authkey, 'like' => $like->id]) }}">حذف از لیست</a>
                    </div>
                @empty
                    <div class="rounded-squircle border border-dashed border-slate-200 p-8 text-center dark:border-white/10">
                        <p class="text-sm text-slate-400">محصولی لایک نشده است.</p>
                    </div>
                @endforelse
            </div>

            <h2 class="mb-5 mt-10 text-lg font-black text-slate-800 dark:text-white">رأی‌های شما روی نظرات</h2>
            <div class="space-y-3">
                @forelse($commentVotes as $commentVote)
                    <div class="rounded-squircle border border-slate-100 bg-slate-50/30 p-4 dark:border-white/5 dark:bg-white/5">
                        <div class="mb-2 flex items-center justify-between gap-4">
                            <p class="line-clamp-1 text-sm font-bold text-slate-700 dark:text-white">{{ $commentVote->comment->product->title ?? 'محصول' }}</p>
                            <span class="rounded-full px-3 py-1 text-[10px] font-black {{ $commentVote->vote === 1 ? 'bg-primary/10 text-primary' : 'bg-warning/10 text-warning' }}">
                                {{ $commentVote->vote === 1 ? 'لایک' : 'دیسلایک' }}
                            </span>
                        </div>
                        <p class="line-clamp-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">{{ $commentVote->comment->content ?? '-' }}</p>
                    </div>
                @empty
                    <div class="rounded-squircle border border-dashed border-slate-200 p-8 text-center dark:border-white/10">
                        <p class="text-sm text-slate-400">هنوز روی نظری رأی ثبت نکرده‌اید.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.profile>
