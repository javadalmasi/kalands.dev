<x-layouts.admin-dashboard title="مدیریت نظرات">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">نظرات</h1>
    <form method="GET" class="admin-card mb-4 grid gap-3 md:grid-cols-3">
        <select name="sort" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
            <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
            <option value="status" @selected(($sort ?? '') === 'status')>بر اساس وضعیت</option>
        </select>
        <select name="status" class="rounded p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
            <option value="pending" @selected(($status ?? '') === 'pending')>در انتظار</option>
            <option value="approved" @selected(($status ?? '') === 'approved')>تایید</option>
            <option value="rejected" @selected(($status ?? '') === 'rejected')>رد</option>
            <option value="spam" @selected(($status ?? '') === 'spam')>اسپم</option>
        </select>
        <button class="admin-btn justify-center"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
    </form>

    <form id="bulk-comments-form" method="POST" action="{{ route('dash.admin.comments.bulk', ['authkey' => $authkey]) }}" class="admin-card mb-4 space-y-3">
        @csrf
        <div class="admin-actions">
            <label class="inline-flex items-center gap-2 text-xs">
                <input type="checkbox" id="select-all-comments">
                انتخاب همه
            </label>
            <select name="action" class="rounded p-2 text-xs dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                <option value="approved">تایید</option>
                <option value="rejected">رد</option>
                <option value="spam">اسپم</option>
                <option value="delete">حذف</option>
            </select>
            <button class="admin-btn" type="submit" data-admin-confirm="این اقدام روی نظرات انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟"><span class="material-icons">done_all</span>اجرای گروهی</button>
        </div>

    </form>
    <div class="space-y-3">
        @foreach($comments as $comment)
            <div class="admin-card !p-3">
                <div class="mb-1 flex items-start gap-2">
                    <input type="checkbox" form="bulk-comments-form" name="comment_ids[]" value="{{ $comment->id }}" class="comment-item-checkbox mt-1">
                    <p class="text-sm">{{ $comment->content }}</p>
                </div>
                <p class="mt-1 text-xs text-slate">وضعیت: {{ $comment->status }} | {{ $comment->created_at }}</p>
                <div class="mt-2 admin-actions text-xs">
                    <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'approved']) }}" method="POST">@csrf<button class="admin-btn"><span class="material-icons">check_circle</span>تایید</button></form>
                    <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'rejected']) }}" method="POST">@csrf<button class="admin-btn"><span class="material-icons">cancel</span>رد</button></form>
                    <form action="{{ route('dash.admin.comments.status', ['authkey' => $authkey, 'comment' => $comment->id, 'status' => 'spam']) }}" method="POST">@csrf<button class="admin-btn"><span class="material-icons">report</span>اسپم</button></form>
                    <form action="{{ route('dash.admin.comments.delete', ['authkey' => $authkey, 'comment' => $comment->id]) }}" method="POST" data-admin-confirm="حذف این نظر غیرقابل بازگشت است. ادامه می‌دهید؟">@csrf @method('DELETE')<button class="admin-btn admin-btn-danger"><span class="material-icons">delete</span>حذف</button></form>
                </div>
            </div>
        @endforeach
    </div>
    <div class="mt-4">{{ $comments->links() }}</div>

</x-layouts.admin-dashboard>
    @vite(['resources/js/admin-comments.js'])
