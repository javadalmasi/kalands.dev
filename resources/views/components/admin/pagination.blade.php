@props(['paginator', 'class' => ''])
@if($paginator->hasPages())
<div {{ $attributes->class(['mt-5']) }}>
    {{ $paginator->links('pagination.admin') }}
</div>
@endif
