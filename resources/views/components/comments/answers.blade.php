@php use App\Models\Comment; @endphp

<ul class="mt-3 space-y-2">
    @foreach($children as $child)
        @continue($child->status !== Comment::STATUS_APPROVED)
        <li class="mr-4 rounded-squircle border-r-4 border-primary bg-white dark:bg-slate-900 dark:bg-slate-50/70 p-3 dark:bg-slate-50">
            <p class="mb-1 text-xs text-slate dark:text-slate-200">
                {{ $child->user?->name ?: ($child->name ?: 'کاربر مهمان') }} - {{ $child->created_at }}
            </p>
            <p class="text-sm text-slate dark:text-slate-200">{{ $child->content }}</p>
            <div class="mt-2 flex items-center gap-2">
                @php
                    $childLikes = $child->votes->where('vote', 1)->count();
                    $childDislikes = $child->votes->where('vote', -1)->count();
                    $childUserVote = auth()->check() ? (int) ($child->votes->firstWhere('user_id', auth()->id())?->vote ?? 0) : 0;
                @endphp
                @auth
                    <form method="POST" action="{{ route('comments.vote', ['comment' => $child->id]) }}">
                        @csrf
                        <input type="hidden" name="vote" value="1">
                        <button type="submit" class="flex items-center gap-1 rounded-squircle border px-2 py-1 text-xs transition-colors {{ $childUserVote === 1 ? 'border-primary bg-slate-50 dark:bg-slate-800 text-primary dark:text-primary' : 'border-slate-100 dark:border-white/5 text-slate hover:border-primary hover:text-primary dark:border-white/5 dark:text-slate-200' }}">
                            +{{ $childLikes }}
                        </button>
                    </form>
                    <form method="POST" action="{{ route('comments.vote', ['comment' => $child->id]) }}">
                        @csrf
                        <input type="hidden" name="vote" value="-1">
                        <button type="submit" class="flex items-center gap-1 rounded-squircle border px-2 py-1 text-xs transition-colors {{ $childUserVote === -1 ? 'border-warning bg-warning/10 text-warning dark:text-warning' : 'border-slate-100 dark:border-white/5 text-slate hover:border-warning hover:text-warning dark:border-white/5 dark:text-slate-200' }}">
                            -{{ $childDislikes }}
                        </button>
                    </form>
                @endauth
            </div>
        </li>
    @endforeach
</ul>
