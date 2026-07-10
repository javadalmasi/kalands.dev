@props([
    'action' => '',
    'method' => 'GET',
    'cols'   => 4,
    'class'  => '',
])
@php
$gridClass = match((int)$cols) {
    2 => 'grid-cols-1 md:grid-cols-2',
    3 => 'grid-cols-1 md:grid-cols-3',
    5 => 'grid-cols-2 md:grid-cols-5',
    6 => 'grid-cols-2 md:grid-cols-6',
    default => 'grid-cols-2 md:grid-cols-4',
};
@endphp
<form
    method="{{ strtoupper($method) }}"
    @if($action) action="{{ $action }}" @endif
    {{ $attributes->class(['admin-card mb-6']) }}
>
    <div class="grid gap-3 items-end {{ $gridClass }}">
        {{ $slot }}
    </div>
</form>
