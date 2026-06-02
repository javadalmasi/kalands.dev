@php use App\Models\Comment; @endphp
<x-layouts.profile>
    <div class="flex flex-col col-span-8">
        <h1 class="text-3xl font-bold">مدیریت نظرات</h1>
        @foreach($comments as $comment)
            <div class="mt-2 bg-white gap-2 rounded border-2 flex flex-col p-4">
                <p class="text-sm">{{ $comment->content }}</p>
                <p class="text-sm text-slate">{{ $comment->user?->name ?? $comment->name }}</p>
                <p class="font-bold">وضعیت: {{ __($comment->status) }}</p>
                <div class="w-full items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if($comment->status !== Comment::STATUS_APPROVED)
                            <a
                                href="{{ route('admin.comments.accept', $comment->id) }}"
                                class="btn-primary px-2 py-1"
                            >تایید</a>
                        @endif
                        @if($comment->status !== Comment::STATUS_REJECTED)
                            <a
                                href="{{ route('admin.comments.reject', $comment->id) }}"
                                class="btn-red px-2 py-1"
                            >عدم تایید</a>
                        @endif
                        @if($comment->status !== Comment::STATUS_SPAM)
                            <a
                                href="{{ route('admin.comments.spam', $comment->id) }}"
                                class="btn-red px-2 py-1"
                            >اسپم</a>
                        @endif
                    </div>
                    <div class="flex mt-4">
                        <form
                            action="{{ route('admin.comments.delete', $comment->id) }}"
                            method="POST"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="btn-red px-2 py-1" type="submit">حذف کامنت</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</x-layouts.profile>
