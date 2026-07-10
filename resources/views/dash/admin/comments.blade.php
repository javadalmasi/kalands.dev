<x-layouts.admin-dashboard title="مدیریت نظرات">
    @php($authkey = request()->route('authkey'))
    @vite(['resources/js/admin-comments.js'])

    <x-admin.page-header title="نظرات" />

    <x-admin.filter-bar cols="3">
        <x-admin.filter-field label="مرتب‌سازی">
            <select name="sort" class="admin-input">
                <option value="latest" @selected(($sort ?? 'latest') === 'latest')>جدیدترین</option>
                <option value="oldest" @selected(($sort ?? '') === 'oldest')>قدیمی‌ترین</option>
                <option value="status" @selected(($sort ?? '') === 'status')>بر اساس وضعیت</option>
            </select>
        </x-admin.filter-field>
        <x-admin.filter-field label="وضعیت">
            <select name="status" class="admin-input">
                <option value="all" @selected(($status ?? 'all') === 'all')>همه وضعیت‌ها</option>
                <option value="pending" @selected(($status ?? '') === 'pending')>در انتظار</option>
                <option value="approved" @selected(($status ?? '') === 'approved')>تایید</option>
                <option value="rejected" @selected(($status ?? '') === 'rejected')>رد</option>
                <option value="spam" @selected(($status ?? '') === 'spam')>اسپم</option>
            </select>
        </x-admin.filter-field>
        <x-admin.filter-field label=" ">
            <button class="admin-btn justify-center w-full"><span class="material-icons">filter_alt</span>اعمال فیلتر</button>
        </x-admin.filter-field>
    </x-admin.filter-bar>

    <x-admin.bulk-bar
        action="{{ route('dash.admin.comments.bulk', ['authkey' => $authkey]) }}"
        id="bulk-comments-form"
        label="مورد"
        confirm="این اقدام روی نظرات انتخاب‌شده انجام می‌شود. ادامه می‌دهید؟"
    >
        <x-slot:actions>
            <select name="action" class="admin-input text-xs">
                <option value="approved">تایید</option>
                <option value="rejected">رد</option>
                <option value="spam">اسپم</option>
                <option value="delete">حذف</option>
            </select>
        </x-slot:actions>
    </x-admin.bulk-bar>

    <div class="space-y-3">
        @foreach($comments as $comment)
            <div class="admin-card !p-3">
                <div class="mb-1 flex items-start gap-2">
                    <input type="checkbox" data-bulk-item form="bulk-comments-form" name="comment_ids[]" value="{{ $comment->id }}" class="comment-item-checkbox mt-1">
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

    <x-admin.pagination :paginator="$comments" />

</x-layouts.admin-dashboard>
