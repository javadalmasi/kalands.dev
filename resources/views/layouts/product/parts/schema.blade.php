@php
use App\Http\Controllers\ProductController;

$isDigikala = ($store === 'digikala');
$isBasalam  = ($store === 'basalam');

/* ======================================================
   DIGIKALA
====================================================== */
if ($isDigikala && isset($data['data'])) {

    $seo     = $data['data']['seo'] ?? [];
    $product = $data['data']['product'] ?? [];

    $title       = ProductController::TextReplaced($seo['title'] ?? '');
    $description = ProductController::TextReplaced($seo['header']['description'] ?? '');
    $canonical   = ProductController::LinkReplaced($seo['header']['canonical_url'] ?? '');
    $image       = ProductController::ImgProfile($seo['twitter_card']['image'] ?? null,800,800,80,false);

    $isMarketable = ($product['status'] ?? null) === 'marketable';
    $prices = [];

    if ($isMarketable && isset($product['variants'])) {
        foreach ($product['variants'] as $v) {
            if (isset($v['price']['selling_price'])) {
                $prices[] = $v['price']['selling_price'];
            }
        }
    }
}

/* ======================================================
   BASALAM
====================================================== */
if ($isBasalam && !empty($data)) {

    $title       = ProductController::TextReplaced($data['title'] ?? '');
    $description = ProductController::TextReplaced(
        ProductController::RemoveEmoji($data['description'] ?? '')
    );

    $canonical = 'https://www.kalands.ir/product/XBS-' .
        ($data['id'] ?? '') . '/' . str_slug_persian($data['title'] ?? '');

    $image = ProductController::ImgProfile(
        $data['photo']['large'] ?? null,
        null,null,null,null,null,'basalam'
    );

    $isMarketable = (bool)($data['is_saleable'] ?? false);
    $prices = $isMarketable && isset($data['price']) ? [$data['price']] : [];
}
@endphp

@if(($isDigikala && isset($data['data'])) || ($isBasalam && !empty($data)))

{{-- ================= META TAGS ================= --}}
<title>{{ $title }} | کالندز</title>
<meta name="robots" content="index">
<link rel="canonical" href="{{ $canonical }}">
<meta name="description" content="{{ $description }}">

<meta property="og:type" content="product">
<meta property="og:title" content="{{ $title }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="fa_IR">
<meta property="og:image:width" content="800">
<meta property="og:image:height" content="800">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">

<meta name="DC.Title" content="{{ $title }}">
<meta name="DC.Creator" content="Kalands Marketing Department">
<meta name="DC.Description" content="{{ $description }}">
<meta name="DC.Language" content="fa-IR">

@if($isMarketable)
    <meta property="product:price:amount" content="{{ min($prices) }}">
    <meta property="product:price:currency" content="IRR">
    <meta property="product:availability" content="available for order">
@else
    <meta property="product:availability" content="out of stock">
@endif

{{-- ================= LD+JSON ================= --}}
@php
$schema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Product',
    'name'     => $title,
    'image'    => $image,
    'description' => $description,
    'sku' => $isDigikala
        ? ($product['id'] ?? '')
        : ('XBS-' . ($data['id'] ?? '')),
    'url' => $canonical,
];

if ($isDigikala && !empty($product['brand']['title_fa'])) {
    $schema['brand'] = [
        '@type' => 'Brand',
        'name'  => $product['brand']['title_fa'],
    ];
}

if ($isBasalam) {
    $schema['brand'] = [
        '@type' => 'Brand',
        'name'  => 'متفرقه',
    ];
}

if ($isMarketable && count($prices)) {

    $schema['offers'] = [
        '@type'         => $isDigikala ? 'AggregateOffer' : 'Offer',
        'priceCurrency' => 'IRR',
        'lowPrice'      => $isDigikala ? min($prices) : null,
        'highPrice'     => $isDigikala ? max($prices) : null,
        'price'         => $isBasalam ? min($prices) : null,
        'offerCount'    => $isDigikala ? count($prices) : null,
        'availability'  => 'https://schema.org/InStock',
        'itemCondition' => 'https://schema.org/NewCondition',
        'priceValidUntil' => date('Y-m-d'),
    ];

} else {
    $schema['offers'] = [
        '@type' => 'Offer',
        'price' => 0,
        'priceCurrency' => 'IRR',
        'availability' => 'https://schema.org/OutOfStock',
        'itemCondition'=> 'https://schema.org/NewCondition',
        'priceValidUntil' => date('Y-m-d'),
    ];
}

$schema = array_filter($schema, fn($v) => !is_null($v));
@endphp

<script type="application/ld+json">
@json($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
</script>

@endif
