@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if (!empty($data['data']['product']['breadcrumb']))
        <div class="mb-4 py-1">
            <div class="flex flex-wrap items-center gap-1.5">
            @php($counter = 0)
            @foreach ($data['data']['product']['breadcrumb'] as $item)
                @if ($counter == 0)
                    <div class="flex items-center gap-x-1">
                        <a href="/" class="text-xs text-slate dark:text-slate-200">کالندز</a>
                        <svg class="h-4 w-4 text-slate dark:text-slate-200">
                            <use xlink:href="#chevron-left"/>
                        </svg>
                    </div>
                @elseif ($counter > 0 && $counter != sizeof($data['data']['product']['breadcrumb']) - 1)
                    <div class="flex items-center gap-x-1">
                        <a href="{{ProductController::LinkReplaced($item['url']['uri'])}}" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$item['title']}}</a>
                        @if($counter != sizeof($data['data']['product']['breadcrumb']) - 2)
                            <svg class="h-4 w-4 text-slate dark:text-slate-200">
                                <use xlink:href="#chevron-left"/>
                            </svg>
                        @endif
                    </div>
                @elseif ($counter == sizeof($data['data']['product']['breadcrumb']) - 1)
                    <div class="line-clamp-1 text-sm font-light text-slate dark:text-slate-200 sm:text-base md:text-base hidden">
                        <a href="{{ProductController::LinkReplaced($item['url']['uri'])}}">{{$item['title']}}</a>
                    </div>
                @endif
                @php($counter++)
            @endforeach
            </div>
        </div>
    @endif
@endif

@if($store == "basalam")
    @if (!empty($data['navigation']))
        <div class="mb-4 py-1">
            <div class="flex flex-wrap items-center gap-1.5">
            <div class="flex items-center gap-x-1">
                <a href="/" class="text-xs text-slate dark:text-slate-200">کالندز</a>
                <svg class="h-4 w-4 text-slate dark:text-slate-200">
                    <use xlink:href="#chevron-left"/>
                </svg>
            </div>
            @if(isset($data['navigation']['parent']['parent']['parent']))
                <div class="flex items-center gap-x-1">
                    <a href="/result/?q={{urlencode($data['navigation']['parent']['parent']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['parent']['parent']['title']}}</a>
                    <svg class="h-4 w-4 text-slate dark:text-slate-200">
                        <use xlink:href="#chevron-left"/>
                    </svg>
                </div>
            @endif
            @if(isset($data['navigation']['parent']['parent']))
                <div class="flex items-center gap-x-1">
                    <a href="/result/?q={{urlencode($data['navigation']['parent']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['parent']['title']}}</a>
                    <svg class="h-4 w-4 text-slate dark:text-slate-200">
                        <use xlink:href="#chevron-left"/>
                    </svg>
                </div>
            @endif
            @if(isset($data['navigation']['parent']))
                <div class="flex items-center gap-x-1">
                    <a href="/result/?q={{urlencode($data['navigation']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['title']}}</a>
                    <svg class="h-4 w-4 text-slate dark:text-slate-200">
                        <use xlink:href="#chevron-left"/>
                    </svg>
                </div>
            @endif
            <div class="flex items-center gap-x-1">
                <a href="/result/?q={{urlencode($data['navigation']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['title']}}</a>
            </div>
            </div>
        </div>
    @endif
@endif
