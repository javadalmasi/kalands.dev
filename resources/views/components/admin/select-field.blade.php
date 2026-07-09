@props([
    'name',
    'label',
    'description' => null,
    'options'     => [],
    'selected'    => null,
])
<div class="flex items-center justify-between gap-4 px-5 py-3.5">
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-slate">{{ $label }}</p>
        @if($description)
        <p class="text-[11px] text-slate/45 dark:text-white/35 mt-0.5 leading-[1.45]">{{ $description }}</p>
        @endif
    </div>
    <div class="shrink-0">
        <select
            name="{{ $name }}"
            class="rounded-lg border border-slate/25 dark:border-white/10 bg-white dark:bg-slate-800 px-3 py-1.5 text-sm text-slate dark:text-white focus:border-success/60 focus:ring-1 focus:ring-success/25 outline-none transition-colors"
        >
            @foreach($options as $val => $label_text)
            <option value="{{ $val }}" {{ (old($name, $selected) == $val) ? 'selected' : '' }}>
                {{ $label_text }}
            </option>
            @endforeach
        </select>
    </div>
</div>
