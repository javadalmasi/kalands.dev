@extends('layouts.index')
@section('head')
    <title>صفحه اصلی | کالندز</title>
@endsection
@section('content')
    <main class="flex-grow bg-slate pb-14 pt-0 dark:bg-black">
        <!-- Main Banners section Start -->
        @include('layouts.home.parts.main-banners', ['slider' => $slider ?? []])
        <!-- Main Banners section End -->

        <!-- Special Products section Start -->
        @include('layouts.home.parts.product-swiper',$items = $Data['data']['offers'])
        <!-- Special Products section End -->

        <!-- Category section TOP Start -->
        @include('layouts.home.parts.category-thumb', ['homeCategoryBanners' => $homeCategoryBanners ?? [], 'type' => 'top'])
        <!-- Category section TOP End -->

        <!-- Newest Products section Start -->
        @include('layouts.home.parts.product-swiper',$items = $Data['data']['selling_stock'])
        <!-- Newest Products section End -->

        <!-- Category Banners section Start -->
        @include('layouts.home.parts.category-banners', ['homeCategoryBanners' => $homeCategoryBanners ?? []])
        <!-- Category Banners section End -->

        <!-- Category section BOTTOM Start -->
        @include('layouts.home.parts.category-thumb', ['homeCategoryBanners' => $homeCategoryBanners ?? [], 'type' => 'bottom'])
        <!-- Category section BOTTOM End -->

        <!-- Special Products section Start -->
        {{-- @include('layouts.home.parts.product-swiper', ['items' => $Data['data']['best_selling']]) --}}
        <!-- Special Products section End -->


    </main>
@endsection
