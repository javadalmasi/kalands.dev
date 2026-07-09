@props([
    'name',
    'label',
    'description' => null,
    'checked'     => false,
    'badge'       => null,
])
<div class="flex items-center justify-between gap-4 px-5 py-3.5">
    <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2">
            <p class="text-sm font-medium text-slate">{{ $label }}</p>
            @if($badge)
            <span class="px-1.5 py-px rounded text-[9px] font-bold tracking-wide bg-slate/10 dark:bg-white/10 text-slate/60 dark:text-white/50">{{ $badge }}</span>
            @endif
        </div>
        @if($description)
        <p class="text-[11px] text-slate/45 dark:text-white/35 mt-0.5 leading-[1.45]">{{ $description }}</p>
        @endif
    </div>
    <label class="admin-switch shrink-0">
        <input type="checkbox" name="{{ $name }}" value="1" class="admin-switch-input" @checked($checked)>
        <div class="admin-switch-track"></div>
        <div class="admin-switch-ball"></div>
    </label>
</div>
