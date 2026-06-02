<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <h1 class="mb-6 text-xl font-black text-slate-800 dark:text-white">ویرایش نظر</h1>
            <form action="{{ route('dash.user.comments.update', ['authkey' => $authkey]) }}" method="POST" class="space-y-6">
                @csrf
                @method('PATCH')
                <input type="hidden" name="comment_id" value="{{ $comment->id }}">
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-500 pr-2">متن نظر شما</label>
                    <textarea name="content" rows="6" class="w-full rounded-squircle border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">{{ $comment->content }}</textarea>
                </div>
                <div class="flex justify-end">
                    <button class="btn-primary w-full px-10 py-3 text-sm md:w-auto">ذخیره نهایی تغییرات</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.profile>
