@php
    $sliderEnabled = (bool) ($slider['status'] ?? true);
    $sliderConfig = $slider['config'] ?? [];
    $payloadUrl = $sliderConfig['payload_url'] ?? null;
    $payloadPath = is_string($payloadUrl) ? public_path(ltrim($payloadUrl, '/')) : null;
    $payload = (is_string($payloadPath) && file_exists($payloadPath))
        ? json_decode((string) file_get_contents($payloadPath), true)
        : null;
    $desktopSlides = array_values(array_filter($payload['desktop_slides'] ?? ($slider['desktop_slides'] ?? []), fn ($item) => (bool) ($item['is_active'] ?? true)));
    $mobileSlides = array_values(array_filter($payload['mobile_slides'] ?? ($slider['mobile_slides'] ?? []), fn ($item) => (bool) ($item['is_active'] ?? true)));
    $desktopConfig = $payload['desktop_config'] ?? ($slider['desktop_config'] ?? []);
    $mobileConfig = $payload['mobile_config'] ?? ($slider['mobile_config'] ?? []);
    $desktopConfig['direction'] = 'horizontal';
    $mobileConfig['direction'] = 'horizontal';
    $desktopEnabled = (bool) ($desktopConfig['enabled'] ?? true);
    $mobileEnabled = (bool) ($mobileConfig['enabled'] ?? true);
    $desktopNavigation = (bool) ($desktopConfig['navigation'] ?? true);
    $mobileNavigation = (bool) ($mobileConfig['navigation'] ?? true);

@endphp

@if($sliderEnabled && (($desktopEnabled && count($desktopSlides) > 0) || ($mobileEnabled && count($mobileSlides) > 0)))
<section class="w-full !mt-0 mb-8 md:mb-12 overflow-hidden">
    @if($desktopEnabled && count($desktopSlides) > 0)
        <div class="swiper banner-slider-desktop home-slider-desktop"
             data-slider-engine="home_main_banners_desktop"
             data-slider-config='@json($desktopConfig)'>
            <div class="swiper-wrapper">
                @foreach($desktopSlides as $slide)
                    @php
                        $img = $slide['image'] ?? '';
                        if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                            $img = str_starts_with($img, '/') ? $img : ('/' . ltrim($img, '/'));
                        }
                        $alt = $slide['meta']['alt'] ?? $slide['description'] ?? $slide['title'] ?? 'اسلاید';
                    @endphp
                    <div class="swiper-slide">
                        <a href="{{ $slide['link'] ?? '#' }}" class="group relative block">
                            <img alt="{{ $alt }}" class="w-full object-cover" loading="lazy" src="{{ $img }}" />
                            @if(!empty($slide['description'] ?? null))
                                <span class="pointer-events-none absolute bottom-3 right-3 hidden rounded-md bg-black/65 px-2 py-1 text-xs text-white group-hover:block">{{ $slide['description'] }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            @if($desktopNavigation)
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            @endif
            <div class="swiper-pagination main-banner-pagination"></div>
        </div>
    @endif
    @if($mobileEnabled && count($mobileSlides) > 0)
        <div class="swiper banner-slider-mobile home-slider-mobile"
             data-slider-engine="home_main_banners_mobile"
             data-slider-config='@json($mobileConfig)'>
            <div class="swiper-wrapper">
                @foreach($mobileSlides as $slide)
                    @php
                        $img = $slide['image'] ?? '';
                        if (!str_starts_with($img, 'http://') && !str_starts_with($img, 'https://')) {
                            $img = str_starts_with($img, '/') ? $img : ('/' . ltrim($img, '/'));
                        }
                        $alt = $slide['meta']['alt'] ?? $slide['description'] ?? $slide['title'] ?? 'اسلاید';
                    @endphp
                    <div class="swiper-slide">
                        <a href="{{ $slide['link'] ?? '#' }}" class="group relative block">
                            <img alt="{{ $alt }}" class="w-full object-cover" loading="lazy" src="{{ $img }}" />
                            @if(!empty($slide['description'] ?? null))
                                <span class="pointer-events-none absolute bottom-2 right-2 hidden rounded-md bg-black/65 px-2 py-1 text-xs text-white group-hover:block">{{ $slide['description'] }}</span>
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
            @if($mobileNavigation)
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            @endif
            <div class="swiper-pagination main-banner-pagination"></div>
        </div>
    @endif
</section>
@endif
