<!-- Product Detail (Desktop + Mobile) -->
<div class="hidden lg:block">
    @include('layouts.product.parts.desktop-product-detail.product-detail-section')
</div>

<div class="lg:hidden">
    @include('layouts.product.parts.mobile-product-detail.product-detail-section')
    @include('layouts.product.parts.mobile-product-detail.property')
    @include('layouts.product.parts.mobile-product-detail.add-to-cart')
</div>
