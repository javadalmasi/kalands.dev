<div class="space-y-4" id="commentsContainer">
    @forelse($comments as $comment)
        @php($userVote = auth()->check() ? (int) ($comment->votes->firstWhere('user_id', auth()->id())?->vote ?? 0) : 0)
        <div class="rounded-squircle border border-slate-100 dark:border-white/5 bg-slate-50/10 p-4 dark:border-white/5 dark:bg-slate-50/70">
            <div class="mb-2 flex items-center justify-between gap-3">
                <p class="text-sm font-bold text-slate dark:text-white">
                    {{ $comment->user?->name ?: ($comment->name ?: 'کاربر مهمان') }}
                </p>
                <span class="text-xs text-slate dark:text-slate-200">{{ $comment->created_at }}</span>
            </div>

            <p class="text-sm leading-7 text-slate dark:text-slate-200">{{ $comment->content }}</p>

            <div class="mt-3 flex items-center gap-2">
                @auth
                    <form method="POST" action="{{ route('comments.vote', ['comment' => $comment->id]) }}">
                        @csrf
                        <input type="hidden" name="vote" value="1">
                        <button type="submit" class="flex items-center gap-1 rounded-squircle border px-2.5 py-1.5 text-xs transition-colors {{ $userVote === 1 ? 'border-primary bg-slate-50 dark:bg-slate-800 text-primary dark:text-primary' : 'border-slate-100 dark:border-white/5 text-slate hover:border-primary hover:text-primary dark:border-white/5 dark:text-slate-200 dark:hover:text-primary' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017a2 2 0 01-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095a.905.905 0 00-.905.905c0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path>
                            </svg>
                            {{ $comment->likes_count }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('comments.vote', ['comment' => $comment->id]) }}">
                        @csrf
                        <input type="hidden" name="vote" value="-1">
                        <button type="submit" class="flex items-center gap-1 rounded-squircle border px-2.5 py-1.5 text-xs transition-colors {{ $userVote === -1 ? 'border-warning bg-warning/10 text-warning dark:text-warning' : 'border-slate-100 dark:border-white/5 text-slate hover:border-warning hover:text-warning dark:border-white/5 dark:text-slate-200 dark:hover:text-warning' }}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018c.163 0 .326.02.485.06L17 4m-7 10v5a2 2 0 002 2h.096a.905.905 0 00.905-.904c0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path>
                            </svg>
                            {{ $comment->dislikes_count }}
                        </button>
                    </form>
                @else
                    <span class="text-xs text-slate dark:text-slate-200">برای رأی دادن وارد حساب کاربری شوید.</span>
                @endauth
            </div>

            @if($comment->children->count())
                <x-comments.answers :children="$comment->children"/>
            @endif
        </div>
    @empty
        <p class="text-sm text-slate dark:text-slate-200">دیدگاهی ثبت نشده است.</p>
    @endforelse
</div>
