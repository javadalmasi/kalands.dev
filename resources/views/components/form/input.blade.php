@props([
    'name',
    'id' => null,
    'label',
    'placeholder' => null,
    'type' => 'text',
    'value' => "",
])

<div class="space-y-2">
    <label for="{{ $id ?? $name }}" class="text-xs font-bold text-slate-500 pr-2">
        {{ $label }}
    </label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        value="{{ $value }}"
        placeholder="{{ $placeholder ?? $label }}"
        class="w-full rounded-squircle border border-slate-200 bg-white px-4 py-3.5 text-sm font-medium text-slate-700 outline-none transition-all focus:border-primary focus:ring-4 focus:ring-primary/10 dark:bg-slate-800 dark:text-white dark:border-white/10 dark:focus:bg-slate-700"
    />
</div>
