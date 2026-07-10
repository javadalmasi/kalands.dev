@props([
    'name',
    'label'       => null,
    'type'        => 'text',
    'placeholder' => '',
    'value'       => null,
    'required'    => false,
    'description' => null,
    'ltr'         => false,
])
<div>
    @if($label)
    <label for="{{ $name }}" class="block text-xs font-medium mb-1.5" style="color: var(--adm-fg-2)">
        {{ $label }}@if($required)<span class="text-red-400 ml-0.5">*</span>@endif
    </label>
    @endif
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @required($required)
        {{ $attributes->class(['admin-input', 'admin-ltr' => $ltr]) }}
    >
    @if($description)
    <p class="text-[11px] mt-1" style="color: var(--adm-fg-2)">{{ $description }}</p>
    @endif
</div>
