@props(['product', 'challenge', 'commentSettings' => ['enabled' => true]])

<div class="space-y-6">
    @if(!($commentSettings['enabled'] ?? true))
        <div class="relative overflow-hidden rounded-2xl border border-amber-500/10 bg-amber-500/5 p-8 text-center dark:border-amber-500/20 dark:bg-amber-500/10">
            <div class="absolute inset-x-0 top-0 h-1.5 bg-amber-500"></div>
            <div class="mx-auto mb-6 grid h-20 w-20 place-items-center rounded-full bg-white text-amber-500 shadow-xl shadow-amber-500/10 dark:bg-slate-800">
                <span class="material-icons !text-5xl !leading-none block">warning</span>
            </div>
            <h3 class="text-xl font-black text-amber-600 dark:text-amber-500">ارسال نظر محدود شده است</h3>
            <p class="mx-auto mt-3 max-w-sm text-sm font-bold leading-relaxed text-slate-600 dark:text-slate-400">
                {{ $commentSettings['disabled_message'] ?? 'در حال حاضر امکان ثبت نظر جدید در این بخش به دستور مدیریت غیرفعال شده است.' }}
            </p>
            <div class="mt-8 flex justify-center">
                <button type="button" data-review-close class="rounded-xl bg-amber-600 px-10 py-3 text-xs font-black text-white shadow-lg shadow-amber-600/30 transition-all hover:bg-amber-700 active:scale-95">متوجه شدم</button>
            </div>
        </div>
    @endif

    <form method="POST" class="space-y-6 {{ !($commentSettings['enabled'] ?? true) ? 'opacity-50 pointer-events-none' : '' }}" action="{{ route('comments.store') }}">
        @csrf
        <input type="hidden" name="product_id" value="{{ $product->id }}">

        @guest
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate pr-2">نام شما</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-squircle border border-slate-100 bg-slate-50 p-3.5 text-sm font-medium focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
                </div>
                <div class="space-y-1">
                    <label class="text-xs font-bold text-slate pr-2">ایمیل (نمایش داده نمی‌شود)</label>
                    <input type="email" name="email" value="{{ old('email') }}" class="w-full rounded-squircle border border-slate-100 bg-slate-50 p-3.5 text-sm font-medium focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700" />
                </div>
            </div>
        @endguest

        <div class="space-y-1">
            <label class="text-xs font-bold text-slate pr-2">متن دیدگاه</label>
            <textarea
                name="content"
                rows="6"
                placeholder="تجربه خود را در مورد این کالا بنویسید..."
                class="w-full rounded-squircle border border-slate-100 bg-slate-50 p-4 text-sm font-medium text-slate transition-all focus:border-primary focus:bg-white focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700"
            >{{ old('content') }}</textarea>
        </div>

        <input type="hidden" name="parent_id" value="{{ old('parent_id') }}" />

        <x-security.challenge-options :challenge="$challenge" />

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" @disabled(!($commentSettings['enabled'] ?? true)) class="btn-primary flex-1 py-4 shadow-lg shadow-primary/30 text-base">ارسال نهایی دیدگاه</button>
            <button type="button" data-review-close class="btn-secondary px-8 py-4 text-base">انصراف</button>
        </div>
    </form>
</div>
