@props([
    'label' => '',
    'span'  => 1,
])
@php
$spanClass = match((int)$span) {
    2 => 'col-span-2',
    3 => 'col-span-3',
    4 => 'col-span-4',
    default => '',
};
@endphp
<div class="{{ $spanClass }} space-y-1 min-w-0">
    @if($label)
    <label class="block text-[10px] font-medium px-0.5" style="color: var(--adm-fg-3)">{{ $label }}</label>
    @endif
    {{ $slot }}
</div>
