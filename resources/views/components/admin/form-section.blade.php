@props([
    'title',
    'description' => null,
    'icon' => null,
])
<div class="admin-card !p-0 overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3.5 border-b border-slate/[0.08] bg-slate/[0.025] dark:bg-white/[0.02]">
        @if($icon)
        <span class="material-icons text-success/80 text-[1.15rem] shrink-0">{{ $icon }}</span>
        @endif
        <div class="min-w-0">
            <p class="text-sm font-bold text-slate leading-tight">{{ $title }}</p>
            @if($description)
            <p class="text-[11px] text-slate/50 mt-0.5 leading-4">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div class="divide-y divide-slate/[0.06] dark:divide-white/[0.04]">
        {{ $slot }}
    </div>
</div>
