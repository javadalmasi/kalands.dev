<div class="flex items-center justify-center gap-x-4 md:justify-end">
    <!-- Previous button -->
    @if($Data['data']['pager']['current_page'] > 1)
        <a class="pagination-button flex items-center justify-center" href="{{ str_replace('page=' . $Data['data']['pager']['current_page'], 'page=' . ($Data['data']['pager']['current_page'] - 1), request()->fullUrl()) }}">
            <svg class="h-6 w-6">
                <use xlink:href="#chevron-right"></use>
            </svg>
        </a>
    @else
        <span class="pagination-button flex items-center justify-center opacity-50 cursor-not-allowed">
            <svg class="h-6 w-6">
                <use xlink:href="#chevron-right"></use>
            </svg>
        </span>
    @endif

    <!-- Pages -->
    <div class="flex items-center gap-x-2">
        <!-- Determine max pages to show (either total_pages or 100) -->
        @php
            $maxPages = min($Data['data']['pager']['total_pages'], 100);
        @endphp

        <!-- First page -->
        @if($Data['data']['pager']['current_page'] > 3 && $maxPages > 5)
            <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
            @if($Data['data']['pager']['current_page'] > 4)
                <span class="text-sm text-slate dark:text-slate-200">...</span>
            @endif
        @endif

        <!-- Pages before current -->
        @for($i = max(1, $Data['data']['pager']['current_page'] - 2); $i < $Data['data']['pager']['current_page']; $i++)
            @if($i <= $maxPages)
                <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
            @endif
        @endfor

        <!-- Current page -->
        <a class="pagination-button pagination-button-active" href="#">{{ $Data['data']['pager']['current_page'] }}</a>

        <!-- Pages after current -->
        @for($i = $Data['data']['pager']['current_page'] + 1; $i <= min($maxPages, $Data['data']['pager']['current_page'] + 2); $i++)
            <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
        @endfor

        <!-- Last pages (only if maxPages < total_pages, meaning we're limiting to 100) -->
        @if($Data['data']['pager']['total_pages'] > 100 && $Data['data']['pager']['current_page'] < $maxPages - 2)
            <span class="text-sm text-slate dark:text-slate-200">...</span>
            <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => $maxPages]) }}">{{ $maxPages }}</a>
        @elseif($Data['data']['pager']['current_page'] < $maxPages - 3 && $maxPages <= $Data['data']['pager']['total_pages'])
            <span class="text-sm text-slate dark:text-slate-200">...</span>
            <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => $maxPages]) }}">{{ $maxPages }}</a>
        @elseif($Data['data']['pager']['current_page'] < $maxPages - 2 && $maxPages < $Data['data']['pager']['total_pages'])
            <a class="pagination-button" href="{{ request()->fullUrlWithQuery(['page' => $maxPages]) }}">{{ $maxPages }}</a>
        @endif
    </div>

    <!-- Next button -->
    @if($Data['data']['pager']['current_page'] < $maxPages)
        <a class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate transition-all duration-200 hover:bg-primary hover:text-white dark:bg-slate dark:text-white hover:dark:bg-primary"
           href="{{ str_replace('page=' . $Data['data']['pager']['current_page'], 'page=' . ($Data['data']['pager']['current_page'] + 1), request()->fullUrl()) }}">
            <svg class="h-6 w-6">
                <use xlink:href="#chevron-left"></use>
            </svg>
        </a>
    @else
        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white text-slate opacity-50 cursor-not-allowed dark:bg-slate dark:text-white">
            <svg class="h-6 w-6">
                <use xlink:href="#chevron-left"></use>
            </svg>
        </span>
    @endif
</div>