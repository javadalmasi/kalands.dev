<div id="product-share-modal" tabindex="-1" aria-hidden="true" class="modal-anim fixed inset-0 z-50 hidden h-full w-full items-center justify-center overflow-y-auto overflow-x-hidden p-4" data-modal-root>
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md" data-modal-overlay="product-share-modal"></div>
    <div class="modal-panel relative z-10 w-full max-w-lg opacity-0 scale-95 transition duration-300 ease-out" data-modal-content>
        <div class="rounded-squircle bg-white p-6 shadow-2xl dark:bg-slate">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-black text-slate-800 dark:text-white">اشتراک‌گذاری محصول</h3>
                <button type="button" data-modal-hide="product-share-modal" class="rounded-full p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-white/10" aria-label="بستن">
                    <svg class="h-6 w-6"><use xlink:href="#close"/></svg>
                </button>
            </div>

            <div class="space-y-6">
                <div class="grid grid-cols-5 gap-4">
                    <a id="share-telegram-link" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-50 text-blue-500 transition-all group-hover:scale-110 dark:bg-blue-500/10">
                            <svg class="h-6 w-6"><use xlink:href="#telegram"/></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">تلگرام</span>
                    </a>
                    <a id="share-x-link" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-800 transition-all group-hover:scale-110 dark:bg-white/10 dark:text-white">
                            <svg class="h-5 w-5"><use xlink:href="#twitter"/></svg>
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">ایکس</span>
                    </a>
                    <a id="share-eitaa-link" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 text-orange-600 transition-all group-hover:scale-110 dark:bg-orange-600/10">
                            <img data-share-lazy-icon data-src="/assets/images/messengers/eitaa.svg" class="h-6 w-6 dark:brightness-0 dark:invert" alt="ایتا">
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">ایتا</span>
                    </a>
                    <a id="share-bale-link" target="_blank" rel="noopener" class="flex flex-col items-center gap-2 group">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 text-green-600 transition-all group-hover:scale-110 dark:bg-green-600/10">
                            <img data-share-lazy-icon data-src="/assets/images/messengers/bale.svg" class="h-6 w-6 dark:brightness-0 dark:invert" alt="بله">
                        </div>
                        <span class="text-[10px] font-bold text-slate-500">بله</span>
                    </a>
                    <button type="button" id="share-copy-link-btn" class="flex flex-col items-center gap-2 group">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-800 transition-all group-hover:scale-110 dark:bg-white/10 dark:text-white">
                            <svg class="h-5 w-5"><use xlink:href="#copy"/></svg>
                        </span>
                        <span class="text-[10px] font-bold text-slate-500">کپی لینک</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
