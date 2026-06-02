<x-layouts.profile>
    @php($authkey = request()->route('authkey') ?? auth()->user()->dashboard_authkey)
    <div class="col-span-12 lg:col-span-9">
        <div class="rounded-squircle border border-slate-100 bg-white p-6 shadow-sm dark:border-white/5 dark:bg-slate-900">
            <h1 class="mb-6 text-xl font-black text-slate-800 dark:text-white">ایجاد تیکت جدید</h1>

            @if(!($settings['enabled'] ?? true))
                <div class="mb-8 flex items-center gap-4 rounded-2xl border border-amber-500/20 bg-amber-500/5 p-6 dark:bg-amber-500/10">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-amber-500 text-white shadow-lg shadow-amber-500/30">
                        <span class="material-icons !leading-none block">warning</span>
                    </div>
                    <div>
                        <p class="text-sm font-black text-amber-700 dark:text-amber-500">ارسال تیکت غیرفعال است</p>
                        <p class="mt-1 text-xs font-bold text-amber-600/80 dark:text-amber-400/80">{{ $settings['disabled_message'] ?? 'ارسال تیکت در این زمان ممکن نیست.' }}</p>
                    </div>
                </div>
            @endif

            @if($isBlocked ?? false)
                <div class="mb-8 flex items-center gap-4 rounded-2xl border border-warning/20 bg-warning/5 p-6 dark:bg-warning/10">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-warning text-white shadow-lg shadow-warning/30">
                        <span class="material-icons !leading-none block">block</span>
                    </div>
                    <div>
                        <p class="text-sm font-black text-warning">دسترسی محدود شده است</p>
                        <p class="mt-1 text-xs font-bold text-warning/80">شما به دلیل عدم رعایت قوانین از ارسال درخواست جدید منع شده‌اید.</p>
                    </div>
                </div>
            @endif

            <div class="mb-6 rounded-squircle border border-primary/20 bg-primary/5 p-4 text-sm text-slate-700 dark:text-slate-200">
                قبل از ارسال پیام، لطفا بخش
                <a href="{{ route('faq.index') }}" class="font-bold text-primary hover:underline">سوالات متداول</a>
                را مطالعه کنید.
            </div>

            <form action="{{ route('dash.user.tickets.store', ['authkey' => $authkey]) }}" method="POST">
                @csrf
                <fieldset @disabled(!($settings['enabled'] ?? true) || ($isBlocked ?? false)) class="space-y-5 disabled:opacity-50">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 pr-2">دسته‌بندی موضوعی</label>
                            <select name="category_id" class="w-full rounded-squircle border border-slate-200 bg-white p-3.5 text-sm font-medium text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10">
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="text-xs font-bold text-slate-500 pr-2">اولویت درخواست</label>
                            <select name="priority" class="w-full rounded-squircle border border-slate-200 bg-white p-3.5 text-sm font-medium text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10">
                                <option value="low">کم</option>
                                <option value="medium" selected>متوسط</option>
                                <option value="high">زیاد</option>
                            </select>
                        </div>
                    </div>

                    <x-form.input name="subject" label="موضوع تیکت" value="{{ old('subject') }}"/>

                    <div class="space-y-2">
                        <label class="text-xs font-bold text-slate-500 pr-2">متن درخواست شما</label>
                        <textarea name="message" rows="6" placeholder="توضیحات خود را اینجا بنویسید..." class="w-full rounded-squircle border border-slate-200 bg-white p-4 text-sm font-medium text-slate-700 outline-none focus:border-primary dark:bg-slate-800 dark:text-white dark:border-white/10">{{ old('message') }}</textarea>
                    </div>

                    <div class="flex justify-end">
                        <button class="btn-primary w-full px-10 py-3.5 text-sm font-black md:w-auto">ارسال نهایی تیکت</button>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
</x-layouts.profile>
