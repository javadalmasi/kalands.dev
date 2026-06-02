<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <h1 class="mb-6 text-xl font-black text-slate-800 dark:text-white">نظرات شما</h1>
            <div class="space-y-4">
                @forelse($comments as $comment)
                    <a href="{{ route('dash.user.comments.show', ['authkey' => $authkey, 'comment' => $comment->id]) }}" class="group block rounded-squircle border border-slate-50 bg-slate-50/30 p-4 transition-all hover:bg-slate-50 dark:border-white/5 dark:bg-white/5 dark:hover:bg-white/10">
                        <div class="flex items-center justify-between gap-4 mb-2">
                            <p class="text-xs font-bold text-slate-400">{{ $comment->created_at->diffForHumans() }}</p>
                            @if($comment->status === 'approved')
                                <span class="rounded-full bg-primary/10 px-3 py-1 text-[10px] font-black text-primary">تایید شده</span>
                            @elseif($comment->status === 'pending')
                                <span class="rounded-full bg-amber-500/10 px-3 py-1 text-[10px] font-black text-amber-600">در انتظار تایید</span>
                            @elseif($comment->status === 'rejected')
                                <span class="rounded-full bg-warning/10 px-3 py-1 text-[10px] font-black text-warning">عدم تایید</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black text-slate-500">{{ $comment->status }}</span>
                            @endif
                        </div>
                        <p class="text-sm leading-relaxed text-slate-700 line-clamp-2 dark:text-white group-hover:text-primary transition-colors">{{ $comment->content }}</p>
                        <div class="mt-3 flex items-center gap-1 text-[10px] font-black text-primary">
                            <span>مشاهده و ویرایش</span>
                            <svg class="h-3 w-3 transition-transform group-hover:-translate-x-1"><use xlink:href="#chevron-left"/></svg>
                        </div>
                    </a>
                @empty
                    <div class="rounded-squircle border border-dashed border-slate-200 p-10 text-center dark:border-white/10">
                        <p class="text-sm text-slate-400">نظری ثبت نشده است.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.profile>
