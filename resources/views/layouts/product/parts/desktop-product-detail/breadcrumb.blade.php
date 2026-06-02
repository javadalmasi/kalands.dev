@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if (!empty($data['data']['product']['breadcrumb']))
        <div class="mb-4">
            <nav aria-label="Breadcrumb" class="py-1">
                <ol class="flex flex-wrap items-center gap-1.5">
                    @php($counter = 0)
                    @foreach ($data['data']['product']['breadcrumb'] as $item)
                        @if ($counter == 0)
                            <li class="flex items-center gap-x-2">
                                <a href="/" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">کالندز</a>
                                <span class="text-slate dark:text-slate-200">
                            <svg class="h-4 w-4">
                              <use xlink:href="#chevron-left"/>
                            </svg>
                          </span>
                            </li>
                        @elseif ($counter > 0 && $counter != sizeof($data['data']['product']['breadcrumb'])-1)
                            <li class="flex items-center gap-x-2">
                                <a href="{{ProductController::LinkReplaced($item['url']['uri'])}}" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$item['title']}}</a>
                                <span class="text-slate dark:text-slate-200">
                                 @if($counter != sizeof($data['data']['product']['breadcrumb']) - 2)
                                        <svg class="h-4 w-4">
                                      <use xlink:href="#chevron-left"/>
                                    </svg>
                                    @endif
                                </span>
                            </li>
                        @elseif ($counter == sizeof($data['data']['product']['breadcrumb'])-1)
                            <li>
                                <a href="{{ProductController::LinkReplaced($item['url']['uri'])}}"
                                   class="text-sm text-slate hover:text-slate dark:text-slate-200 hover:dark:text-white hidden">{{$item['title']}}</a>
                            </li>
                        @endif
                        @php($counter++)
                    @endforeach
                </ol>
            </nav>
        </div>
    @endif

@endif

@if($store == "basalam")
    @if (!empty($data['navigation']))
        <div class="mb-4">
            <nav aria-label="Breadcrumb" class="py-1">
                <ol class="flex flex-wrap items-center gap-1.5">

                    <li class="flex items-center gap-x-2">
                        <a href="/" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">کالندز</a>
                        <span class="text-slate dark:text-slate-200">
                        <svg class="h-4 w-4">
                          <use xlink:href="#chevron-left"/>
                        </svg>
                        </span>
                    </li>
                    @if(isset($data['navigation']['parent']['parent']['parent']))
                        <li class="flex items-center gap-x-2">
                            <a href="/result/?q={{urlencode($data['navigation']['parent']['parent']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['parent']['parent']['title']}}</a>
                            <span class="text-slate dark:text-slate-200">
                            <svg class="h-4 w-4">
                              <use xlink:href="#chevron-left"/>
                            </svg>
                        </span>
                        </li>
                    @endif
                    @if(isset($data['navigation']['parent']['parent']))
                        <li class="flex items-center gap-x-2">
                            <a href="/result/?q={{urlencode($data['navigation']['parent']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['parent']['title']}}</a>
                            <span class="text-slate dark:text-slate-200">
                            <svg class="h-4 w-4">
                              <use xlink:href="#chevron-left"/>
                            </svg>
                        </span>
                        </li>
                    @endif
                    @if(isset($data['navigation']['parent']))
                        <li class="flex items-center gap-x-2">
                            <a href="/result/?q={{urlencode($data['navigation']['parent']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['parent']['title']}}</a>
                            <span class="text-slate dark:text-slate-200">
                            <svg class="h-4 w-4">
                              <use xlink:href="#chevron-left"/>
                            </svg>
                        </span>
                        </li>
                    @endif
                    <li class="flex items-center gap-x-2">
                        <a href="/result/?q={{urlencode($data['navigation']['title'])}}&sort=22" class="text-xs text-slate transition-colors hover:text-primary dark:text-slate-200 dark:hover:text-primary">{{$data['navigation']['title']}}</a>
                    </li>
                </ol>
            </nav>
        </div>
    @endif

@endif
