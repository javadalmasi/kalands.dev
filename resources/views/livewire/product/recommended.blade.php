@php use App\Http\Controllers\ProductController; @endphp
<div class="swiper-wrapper">
    @if($options['mod'] == 1)
        @foreach ($data['data']['products'] as $item)
            <div class="swiper-slide h-auto">
                @include('components.product.card', [
                    'store' => 'digikala',
                    'item' => $item,
                    'href' => ProductController::AffiliateLinkGenerator('digikala', $item['id'], $item['title_fa']),
                    'newTab' => true,
                ])
            </div>
        @endforeach
    @elseif($options['mod'] == 2)
        @foreach ($data['products'] as $item)
            <div class="swiper-slide h-auto">
                @include('components.product.card', [
                    'store' => 'basalam',
                    'item' => $item,
                    'href' => '/product/XBS-'.$item['id'].'/'.str_slug_persian($item['name']),
                    'newTab' => true,
                ])
            </div>
        @endforeach
    @endif
</div>
