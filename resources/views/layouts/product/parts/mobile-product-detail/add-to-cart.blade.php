@php use App\Http\Controllers\ProductController; @endphp
@if($store == "digikala")
    @if (isset($data['data']))
        <div class="fixed inset-x-0 bottom-0 z-10 border-t border-slate-100 bg-white p-4 dark:border-white/10 dark:bg-slate">
            <div class="flex items-center justify-between gap-x-4">
                <div class="flex grow">
                    @if ($data['data']['product']['status'] == "marketable")
                        <a class="btn-primary w-full shadow-lg shadow-primary/30 py-3.5 text-sm js-special-link"
                           href="{{ProductController::GetBaseLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                           data-human-href="{{ProductController::GetSpecialLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                           target="_blank"
                           rel="nofollow"
                           referrerpolicy="no-referrer"
                        >افزودن به سبد خرید</a>
                    @else
                        <a class="w-full js-status-aware-btn"
                           href="{{ProductController::GetBaseLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                           data-human-href="{{ProductController::GetSpecialLink("digikala", $data['data']['product']['id'], $data['data']['product']['title_fa'])}}"
                           target="_blank"
                           rel="nofollow"
                           referrerpolicy="no-referrer"
                           data-human-text="مشخصات بیشتر"
                           data-human-style="btn-secondary"
                        >
                            <button class="btn-primary w-full py-3.5 text-sm js-btn-text">افزودن به سبد خرید</button>
                        </a>
                    @endif
                </div>
                <div class="space-y-1">

                    @if (isset($data['data']['product']['default_variant']['price']['rrp_price']) && $data['data']['product']['default_variant']['price']['discount_percent'] != 0)
                        <div class="flex items-center gap-x-2">
                            <div>
                                <del class="text-sm text-slate decoration-warning dark:text-slate-200 dark:decoration-warning md:text-base">{{$data['data']['product']['default_variant']['price']['rrp_price']}}</del>
                            </div>
                            <div class="flex w-10 items-center justify-center rounded-full bg-warning py-0.5 text-sm font-bold text-white dark:bg-warning">{{$data['data']['product']['default_variant']['price']['discount_percent']}}
                                %
                            </div>
                        </div>
                    @endif
                    @if (isset($data['data']['product']['default_variant']['price']['selling_price']))
                        <div class="text-primary dark:text-primary">
                            <span class="font-semibold">{{number_format($data['data']['product']['default_variant']['price']['selling_price'] / 10)}}</span>
                            <svg class="inline-block h-4 w-4"><use xlink:href="#toman"/></svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif

@if($store == "basalam")
    @if(!empty($data))
        <div class="fixed inset-x-0 bottom-0 z-10 border-t border-slate-100 bg-white p-4 dark:border-white/10 dark:bg-slate">
            <div class="flex items-center justify-between gap-x-4">
                <div class="flex grow">
                    <a class="btn-primary w-full shadow-lg shadow-primary/30 py-3.5 text-sm js-special-link"
                       href="{{ProductController::GetBaseLink("basalam", $data['id'], $data['title'])}}"
                       data-human-href="{{ProductController::GetSpecialLink("basalam", $data['id'], $data['title'])}}"
                       target="_blank"
                       rel="nofollow"
                       referrerpolicy="no-referrer"
                   >افزودن به سبد خرید</a>
                </div>
                <div class="space-y-1">

                    @if (isset($data['primary_price']) && $data['primary_price'] != 0)
                        <div class="flex items-center gap-x-2">
                            <div>
                                <del class="text-sm text-slate decoration-warning dark:text-slate-200 dark:decoration-warning md:text-base">{{number_format($data['primary_price']/10)}}</del>
                            </div>
                            <div class="flex w-10 items-center justify-center rounded-full bg-warning py-0.5 text-sm font-bold text-white dark:bg-warning">{{round((($data['primary_price'] - $data['price']) / $data['primary_price'])*100)}}
                                %
                            </div>
                        </div>
                    @endif
                    @if (isset($data['price']))
                        <div class="text-primary dark:text-primary">
                            <span class="font-semibold">{{number_format($data['price']/10)}}</span>
                            <svg class="inline-block h-4 w-4"><use xlink:href="#toman"/></svg>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
@endif
