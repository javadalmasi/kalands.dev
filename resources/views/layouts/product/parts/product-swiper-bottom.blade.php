@if($store == "digikala")
    <div class="mb-6">
        <div>
            <div class="mb-6 mt-4 flex items-center justify-between border-r-4 border-primary pr-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white lg:text-xl">سایر محصولات</h3>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wider text-slate-400">Top curated products for you</p>
                </div>
            </div>
            <div class="swiper product-slider p-px">
                <livewire:product.recommended
                        :cat="$data['data']['product']['breadcrumb'][sizeof($data['data']['product']['breadcrumb'])-2]['title'].'6be07b748bbaeb9f'"
                        lazy/>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
@endif
@if($store == "basalam" && isset($data['navigation']['title']))
    <div class="mb-6">
        <div>
            <div class="mb-6 mt-4 flex items-center justify-between border-r-4 border-primary pr-4">
                <div>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white lg:text-xl">سایر محصولات</h3>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wider text-slate-400">Top curated products for you</p>
                </div>
            </div>
            <div class="swiper product-slider p-px">
                <livewire:product.recommended
                        :cat="str_replace([',','|'],'',$data['navigation']['title']).'6be07b748bbaeb9f'"
                        lazy/>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>
    </div>
@endif
