@extends('layouts.index')
@section('head')
    @include('layouts.product.parts.schema')
@endsection
@section('content')
    <main class="flex-grow bg-slate pb-14 pt-36 dark:bg-black xs:pt-36">
        <div class="container">
            <div class="hidden lg:block">
                @include('layouts.product.parts.desktop-product-detail.breadcrumb')
            </div>
            <div class="lg:hidden">
                @include('layouts.product.parts.mobile-product-detail.breadcrumb')
            </div>
            @include('layouts.product.parts.product-detail.index')
            @include('layouts.product.parts.product-swiper')
            <!-- description / specs / commment  -->
            @include('layouts.product.parts.tabs.index')
            @include('layouts.product.parts.product-swiper-bottom')
        </div>
    </main>
    <!-- Product Gallery modal -->
    @include('layouts.product.parts.product-gallery-modal')
    @include('layouts.product.parts.share-modal')
    @vite('resources/js/product-page.js')
@endsection
