<?php

namespace App\Livewire\Product;

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class Recommended extends Component
{
    public $data;
    public $options;
    public $cat;

    public function mount()
    {
        if (!str_ends_with($this->cat, '6be07b748bbaeb9f')) {
            $this->options['mod'] = 1;
            if ($this->options['store'] == "digikala") {
                $data = ProductController::DigikalaApi('product/' . $this->options['id'] . '/recommendation/', 'v1', null, true);
                if (!empty($data['data']['data']['products'])) {
                    $data['data']['data']['products'] = array_values(array_filter($data['data']['data']['products'], function ($item) {
                        $uri = $item['url']['uri'] ?? '';
                        if (str_contains($uri, 'dkp-')) return str_starts_with($uri, '/product/dkp-');
                        return true;
                    }));
                    $this->data = $data['data'];
                } else {
                    $data = ProductController::DigikalaApi('search/?q=' . $this->cat, 'v1', null, true);
                    if (!empty($data['data']['products'])) {
                        $data['data']['products'] = array_values(array_filter($data['data']['products'], function ($item) {
                            $uri = $item['url']['uri'] ?? '';
                            if (str_contains($uri, 'dkp-')) return str_starts_with($uri, '/product/dkp-');
                            return true;
                        }));
                    }
                    $this->data = $data;
                }
            } elseif ($this->options['store'] == "basalam") {
                $data = ProductController::DigikalaApi('search/?q=' . $this->cat, 'v1', null, true);
                if (!empty($data['data']['products'])) {
                    $data['data']['products'] = array_values(array_filter($data['data']['products'], function ($item) {
                        $uri = $item['url']['uri'] ?? '';
                        if (str_contains($uri, 'dkp-')) return str_starts_with($uri, '/product/dkp-');
                        return true;
                    }));
                }
                $this->data = $data;
            }
        } else {
            $this->options['mod'] = 2;
            if ($this->options['store'] == "digikala") {
                $data = Http::withHeaders(['Host' => 'bws.kalands.ir'])->withOptions(['verify' => false])->get('http://89.42.44.25/api/bss/864000/ai-engine/api/v2.0/product/search?from=0&q=' . str_replace('6be07b748bbaeb9f', '', $this->cat) . '&dynamicFacets=true&size=24&adsImpressionDisable=false&exp_ws=0&ads=false')->json();
                if (!empty($data['data']['products'])) {
                    $data['data']['products'] = array_values(array_filter($data['data']['products'], function ($item) {
                        $uri = $item['url']['uri'] ?? '';
                        if (str_contains($uri, 'dkp-')) return str_starts_with($uri, '/product/dkp-');
                        return true;
                    }));
                }
                $this->data = $data;
            } elseif ($this->options['store'] == "basalam") {
                $data = Http::withHeaders(['Host' => 'bws.kalands.ir'])->withOptions(['verify' => false])->get('http://89.42.44.25/api/bss/864000/ai-engine/api/v2.0/mlt?user_id=0&from=0&size=24&rank=relevancy&title=' . str_replace('6be07b748bbaeb9f', '', $this->cat) . '&ads=false&productId=' . $this->options['id'])->json();
                if (empty($data['products'])) {
                    $data = Http::withHeaders(['Host' => 'bws.kalands.ir'])->withOptions(['verify' => false])->get('http://89.42.44.25/api/bss/864000/ai-engine/api/v2.0/product/search?from=0&q=' . str_replace('6be07b748bbaeb9f', '', $this->cat) . '&dynamicFacets=true&size=24&adsImpressionDisable=false&exp_ws=0&ads=false')->json();
                }
                $this->data = $data;
            }
        }
    }

    public function placeholder()
    {
        if (str_starts_with(request()->path(), 'product/XBS-')) {
            $this->options['store'] = "basalam";
            preg_match('/product\/XBS-(\d+)\/*/', request()->path(), $id);
            $this->options['id'] = (int)$id[1];
        } elseif (str_starts_with(request()->path(), 'product/')) {
            $this->options['store'] = "digikala";
            preg_match('/product\/(\d+)\/*/', request()->path(), $id);
            $this->options['id'] = (int)$id[1];
        }
        return <<<'HTML'
        <div class="swiper-wrapper">
        @for ($i = 0; $i < 10; $i++)
            <div class="swiper-slide h-auto">
                <div class="group relative flex h-full min-h-[380px] flex-col overflow-hidden rounded-squircle border border-slate-100 bg-white p-4 dark:border-white/5 dark:bg-slate">
                    <div class="relative mb-4 flex-shrink-0 pt-[100%] overflow-hidden rounded-squircle bg-slate-50/50 dark:bg-white/5">
                        <div data-placeholder class="absolute inset-4 rounded-squircle bg-slate-200/70 dark:bg-white/10"></div>
                    </div>
                    <div class="mb-3 flex-grow space-y-2">
                        <div data-placeholder class="h-4 w-[88%] overflow-hidden rounded bg-slate-200/70 dark:bg-white/10"></div>
                        <div data-placeholder class="h-4 w-[65%] overflow-hidden rounded bg-slate-200/70 dark:bg-white/10"></div>
                    </div>
                    <div class="mt-auto border-t border-slate-50 pt-3 dark:border-white/5">
                        <div class="flex flex-col items-end gap-1">
                            <div data-placeholder class="h-3 w-16 overflow-hidden rounded bg-slate-200/70 dark:bg-white/10"></div>
                            <div data-placeholder class="h-5 w-24 overflow-hidden rounded bg-slate-200/70 dark:bg-white/10"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
        </div>
        HTML;
    }

    public function render()
    {
        return view('livewire.product.recommended');
    }


}
