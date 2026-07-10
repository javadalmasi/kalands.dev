<x-layouts.admin-dashboard title="ویرایش فرم تماس با ما">
    @php($authkey = request()->route('authkey'))
    <x-admin.page-header title="ویرایش فرم تماس" />

    <form action="{{ route('dash.admin.contact.update', ['authkey' => $authkey, 'contactMessage' => $contactMessage->id]) }}" method="POST" class="admin-card grid gap-3">
        @csrf
        <input name="name" value="{{ old('name', $contactMessage->name) }}" placeholder="نام" class="admin-input">
        <input name="email" value="{{ old('email', $contactMessage->email) }}" placeholder="ایمیل" class="admin-input">
        <input name="subject" value="{{ old('subject', $contactMessage->subject) }}" placeholder="موضوع" class="admin-input">
        <textarea name="message" rows="6" class="admin-input" placeholder="متن پیام">{{ old('message', $contactMessage->message) }}</textarea>
        <select name="is_read" class="admin-input">
            <option value="0" @selected(!old('is_read', $contactMessage->is_read))>جدید</option>
            <option value="1" @selected(old('is_read', $contactMessage->is_read))>خوانده‌شده</option>
        </select>
        <button class="admin-btn"><span class="material-icons">save</span>ذخیره تغییرات</button>
    </form>
</x-layouts.admin-dashboard>
