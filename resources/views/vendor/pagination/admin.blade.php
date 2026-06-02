@if ($paginator->hasPages())
    <nav role="navigation" aria-label="راهبری صفحات" class="flex items-center justify-between">
        {{-- Mobile View --}}
        <div class="flex justify-between flex-1 md:hidden">
            @if ($paginator->onFirstPage())
                <span class="admin-btn opacity-50 cursor-not-allowed">
                    <span class="material-icons">chevron_right</span>
                    قبلی
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="admin-btn">
                    <span class="material-icons">chevron_right</span>
                    قبلی
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="admin-btn">
                    بعدی
                    <span class="material-icons">chevron_left</span>
                </a>
            @else
                <span class="admin-btn opacity-50 cursor-not-allowed">
                    بعدی
                    <span class="material-icons">chevron_left</span>
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden md:flex-1 md:flex md:items-center md:justify-between">
            <div>
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    نمایش
                    @if ($paginator->firstItem())
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span>
                        تا
                        <span class="font-bold text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    از
                    <span class="font-bold text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span>
                    نتیجه
                </p>
            </div>

            <div class="flex items-center gap-1">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <span class="admin-btn !p-2 opacity-50 cursor-not-allowed" aria-disabled="true">
                        <span class="material-icons">chevron_right</span>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-btn !p-2" aria-label="@lang('pagination.previous')">
                        <span class="material-icons">chevron_right</span>
                    </a>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($elements as $element)
                    {{-- "Three Dots" Separator --}}
                    @if (is_string($element))
                        <span class="admin-btn !bg-transparent !text-slate-400 cursor-default" aria-disabled="true">{{ $element }}</span>
                    @endif

                    {{-- Array Of Links --}}
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="admin-btn !bg-primary !text-white font-bold" aria-current="page">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="admin-btn" aria-label="برو به صفحه {{ $page }}">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-btn !p-2" aria-label="@lang('pagination.next')">
                        <span class="material-icons">chevron_left</span>
                    </a>
                @else
                    <span class="admin-btn !p-2 opacity-50 cursor-not-allowed" aria-disabled="true">
                        <span class="material-icons">chevron_left</span>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
