<x-layouts.app title="سوالات متداول">
    @section('head')
        <title>سوالات متداول</title>
        <script type="application/ld+json">
            @json($jsonLd, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        </script>
    @endsection

    <main class="bg-slate pb-16 pt-28 dark:bg-black md:pb-20 md:pt-36">
        <div class="container">
            <div class="mx-auto max-w-4xl rounded-squircle border border-slate/70 bg-white p-6 shadow-base dark:border-white/10 dark:bg-slate">
                <div class="mb-6">
                    <h1 class="text-2xl font-black text-slate dark:text-white">سوالات متداول</h1>
                    <p class="mt-2 text-sm text-slate dark:text-slate-200">قبل از ارسال پیام، پاسخ سوالات رایج را اینجا بررسی کنید.</p>
                </div>

                <form method="GET" action="{{ route('faq.index') }}" class="mb-6">
                    <label class="sr-only" for="faq-search">جستجو</label>
                    <input id="faq-search" name="q" value="{{ $q }}" placeholder="جستجو در سوالات متداول..." class="w-full rounded-squircle border border-slate bg-transparent p-3 text-sm dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700">
                </form>

                <div class="space-y-3">
                    @forelse($faqs as $faq)
                        <details class="rounded-squircle border border-slate/70 p-4 dark:border-white/10">
                            <summary class="cursor-pointer text-sm font-bold text-slate dark:text-white">{{ $faq->title }}</summary>
                            <div class="prose prose-sm mt-3 max-w-none text-slate dark:prose-invert">{!! $faq->description !!}</div>
                        </details>
                    @empty
                        <div class="rounded-squircle border border-slate/70 p-4 text-sm text-slate dark:border-white/10 dark:text-slate-200">سوالی پیدا نشد.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </main>
</x-layouts.app>
