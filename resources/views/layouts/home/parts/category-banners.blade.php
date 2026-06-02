@php
    $payloadUrl = $homeCategoryBanners['payload_url'] ?? null;
    $payloadPath = is_string($payloadUrl) ? public_path(ltrim($payloadUrl, '/')) : null;
    $payload = (is_string($payloadPath) && file_exists($payloadPath)) ? json_decode((string) file_get_contents($payloadPath), true) : [];
    $banners = $payload['desktop']['banners'] ?? ($homeCategoryBanners['banners'] ?? []);
    $bannersEnabled = (bool) ($homeCategoryBanners['banners_enabled'] ?? true);
@endphp

@if($bannersEnabled && !empty($banners))
<section class="mb-8">
    <div class="container relative">
        <div class="grid w-full grid-cols-1 gap-4 md:grid-cols-2">
            @foreach(array_slice($banners, 0, 2) as $banner)
                @if(!empty($banner['image']))
                    <a href="{{ $banner['link'] ?? '#' }}">
                        <img alt="{{ $banner['alt'] ?? 'بنر دسته‌بندی' }}" class="rounded-squircle w-full" src="{{ $banner['image'] }}" loading="lazy"/>
                    </a>
                @endif
            @endforeach
        </div>
    </div>
</section>
@endif
