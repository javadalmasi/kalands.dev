@php
    $hrefLink = str_replace('dkp-', '', $item['url']['uri'] ?? '#');
    $productKey = '';

    if (isset($item['id']) && $item['id'] !== null && $item['id'] !== '') {
        $productKey = 'id:' . (string)$item['id'];
    } elseif (isset($item['url']['uri']) && is_string($item['url']['uri']) && $item['url']['uri'] !== '') {
        $productKey = 'uri:' . $item['url']['uri'];
    } elseif (isset($item['title_fa']) && is_string($item['title_fa']) && $item['title_fa'] !== '') {
        $productKey = 'title:' . $item['title_fa'];
    }
@endphp

@include('components.product.card', [
    'store' => 'digikala',
    'item' => $item,
    'href' => $hrefLink,
    'productKey' => $productKey,
    'newTab' => false,
])
