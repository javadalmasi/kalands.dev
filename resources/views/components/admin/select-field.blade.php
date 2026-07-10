@props([
    'name',
    'label',
    'description' => null,
    'options'     => [],
    'selected'    => null,
])
<div class="flex items-center justify-between gap-4 px-5 py-3.5">
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium" style="color: var(--adm-fg)">{{ $label }}</p>
        @if($description)
        <p class="text-[11px] mt-0.5 leading-[1.45]" style="color: var(--adm-fg-2)">{{ $description }}</p>
        @endif
    </div>
    <div class="shrink-0">
        <select name="{{ $name }}" class="admin-input !w-auto">
            @foreach($options as $val => $label_text)
            <option value="{{ $val }}" {{ (old($name, $selected) == $val) ? 'selected' : '' }}>
                {{ $label_text }}
            </option>
            @endforeach
        </select>
    </div>
</div>
