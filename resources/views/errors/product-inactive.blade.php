@extends('layouts.index')

@section('head')
    <meta name="robots" content="noindex, nofollow">
    <title>محصول ناموجود - کالندز</title>
@endsection

@section('content')
    <main class="flex-grow bg-slate pb-14 dark:bg-black">
        <!-- Immersive Header -->
        <div class="relative flex min-h-[50vh] flex-col items-center justify-center overflow-hidden py-12">
            <!-- Background Decoration -->
            <div class="absolute inset-0 -z-10 flex items-center justify-center pointer-events-none select-none">
                <span class="text-[20vw] font-black leading-none text-slate-500/5 dark:text-slate-400/5">
                    OFFLINE
                </span>
            </div>

            <div class="container relative z-10">
                <div class="mx-auto max-w-3xl text-center">
                    <!-- Icon with pulsed background -->
                    <div class="relative mx-auto mb-8 flex h-24 w-24 items-center justify-center">
                        <div class="absolute inset-0 animate-ping rounded-full bg-amber-500/10"></div>
                        <div class="relative flex h-20 w-20 items-center justify-center rounded-2xl bg-white shadow-xl dark:bg-slate-900 dark:border dark:border-white/5">
                            <span class="material-icons !text-5xl text-amber-500 !leading-none">inventory_2</span>
                        </div>
                    </div>

                    <!-- Breadcrumb (Inlined for design) -->
                    <div class="mb-4 flex items-center justify-center gap-2 text-xs font-bold text-slate-400">
                        <a href="{{ route('index') }}" class="hover:text-primary">خانه</a>
                        <span class="material-icons text-sm">chevron_left</span>
                        <span>محصول ناموجود</span>
                        @if($productTitle)
                            <span class="material-icons text-sm">chevron_left</span>
                            <span class="truncate max-w-[200px]">{{ $productTitle }}</span>
                        @endif
                    </div>

                    <!-- Content -->
                    <h1 class="text-3xl font-black tracking-tight sm:text-4xl text-slate-800 dark:text-white">
                        این محصول دیگر در دسترس نیست
                    </h1>

                    <p class="mt-6 text-lg leading-8 text-slate-600 dark:text-slate-400">
                        متاسفانه محصول مورد نظر شما در حال حاضر غیرفعال شده یا موجود نمی‌باشد. اما نگران نباشید، می‌توانید از محصولات مشابه زیر دیدن کنید.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center justify-center gap-4">
                        <a href="{{ route('index') }}" class="flex items-center gap-2 rounded-2xl bg-primary px-8 py-3.5 text-sm font-bold text-white transition-all hover:scale-105 active:scale-95 shadow-lg shadow-primary/20">
                            <span class="material-icons text-xl">home</span>
                            بازگشت به صفحه اصلی
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recommendations Section -->
        <div class="container mt-12">
            <div class="mb-8 flex items-center justify-between">
                <h2 class="text-2xl font-black text-slate-800 dark:text-slate-100">{{ $recommendationTitle }}</h2>
                <div class="h-1 flex-grow mx-6 rounded-full bg-slate-100 dark:bg-slate-800/50"></div>
            </div>

            <div class="grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                @forelse($recommendations as $item)
                    @include('layouts.result.parts.product-card', ['item' => $item])
                @empty
                    <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-100 p-16 text-center dark:border-white/5">
                        <span class="material-icons text-6xl text-slate-200 dark:text-white/5 mb-4">search_off</span>
                        <p class="text-lg font-bold text-slate-400">موردی برای نمایش یافت نشد.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </main>
@endsection
