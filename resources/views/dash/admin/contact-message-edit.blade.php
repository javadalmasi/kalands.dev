<x-layouts.admin-dashboard title="ویرایش فرم تماس با ما">
    @php($authkey = request()->route('authkey'))
    <h1 class="admin-page-title">ویرایش فرم تماس</h1>

    <form action="{{ route('dash.admin.contact.update', ['authkey' => $authkey, 'contactMessage' => $contactMessage->id]) }}" method="POST" class="admin-card grid gap-3">
        @csrf
        <input name="name" value="{{ old('name', $contactMessage->name) }}" placeholder="نام" class="rounded bg-slate p-2">
        <input name="email" value="{{ old('email', $contactMessage->email) }}" placeholder="ایمیل" class="rounded bg-slate p-2">
        <input name="subject" value="{{ old('subject', $contactMessage->subject) }}" placeholder="موضوع" class="rounded bg-slate p-2">
        <textarea name="message" rows="6" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" placeholder="متن پیام">{{ old('message', $contactMessage->message) }}</textarea>
        <select name="is_read" class="rounded bg-slate p-2 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
            <option value="0" @selected(!old('is_read', $contactMessage->is_read))>جدید</option>
            <option value="1" @selected(old('is_read', $contactMessage->is_read))>خوانده‌شده</option>
        </select>
        <button class="admin-btn"><span class="material-icons">save</span>ذخیره تغییرات</button>
    </form>
</x-layouts.admin-dashboard>
