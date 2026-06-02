<div id="resultProductsGrid" class="grid grid-cols-2 gap-px gap-y-2 xs:gap-4 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
    @foreach($Data['data']['products'] as $item)
        @include('layouts.result.parts.product-card', ['item' => $item])
    @endforeach
</div>
