@props([
    'name',
    'label',
    'description' => null,
    'value'       => '',
    'min'         => null,
    'max'         => null,
    'unit'        => null,
    'required'    => false,
])
<div class="flex items-center justify-between gap-4 px-5 py-3.5">
    <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-slate">{{ $label }}</p>
        @if($description)
        <p class="text-[11px] text-slate/45 dark:text-white/35 mt-0.5 leading-[1.45]">{{ $description }}</p>
        @endif
    </div>
    <div class="flex items-center gap-2 shrink-0">
        <input
            type="number"
            name="{{ $name }}"
            value="{{ old($name, $value) }}"
            @if($min !== null) min="{{ $min }}" @endif
            @if($max !== null) max="{{ $max }}" @endif
            {{ $required ? 'required' : '' }}
            class="admin-ltr w-[4.5rem] rounded-lg border border-slate/25 dark:border-white/10 bg-white dark:bg-slate-800 px-3 py-1.5 text-sm text-center text-slate dark:text-white focus:border-success/60 focus:ring-1 focus:ring-success/25 outline-none transition-colors"
        >
        @if($unit)
        <span class="text-[11px] text-slate/50 dark:text-white/40 select-none min-w-[2.5rem]">{{ $unit }}</span>
        @endif
    </div>
</div>
