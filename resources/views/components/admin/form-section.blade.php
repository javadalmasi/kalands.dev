@props([
    'title',
    'description' => null,
    'icon' => null,
])
<div class="admin-card !p-0 overflow-hidden">
    <div class="flex items-center gap-3 px-5 py-3.5" style="border-bottom: 1px solid var(--adm-border); background: var(--adm-search-bg)">
        @if($icon)
        <span class="material-icons !text-[17px] shrink-0" style="color: var(--admin-primary)">{{ $icon }}</span>
        @endif
        <div class="min-w-0">
            <p class="text-sm font-semibold leading-tight" style="color: var(--adm-fg)">{{ $title }}</p>
            @if($description)
            <p class="text-[11px] mt-0.5 leading-4" style="color: var(--adm-fg-2)">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div class="divide-y" style="border-color: var(--adm-border)">
        {{ $slot }}
    </div>
</div>
