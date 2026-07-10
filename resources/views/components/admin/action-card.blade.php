@props([
    'title',
    'description' => null,
    'icon',
    'variant'     => 'default',
])
@php
$s = match($variant) {
    'danger'  => ['border' => 'rgba(239,68,68,0.15)',  'iconBg' => 'rgba(239,68,68,0.1)',  'iconColor' => '#f87171'],
    'success' => ['border' => 'rgba(34,197,94,0.15)',  'iconBg' => 'rgba(34,197,94,0.1)',  'iconColor' => '#4ade80'],
    'info'    => ['border' => 'rgba(59,130,246,0.15)', 'iconBg' => 'rgba(59,130,246,0.1)', 'iconColor' => '#60a5fa'],
    default   => ['border' => 'var(--adm-border)',     'iconBg' => 'var(--adm-search-bg)', 'iconColor' => 'var(--adm-fg-2)'],
};
@endphp
<div class="rounded-lg overflow-hidden h-full flex flex-col" style="border: 1px solid {{ $s['border'] }}; background: var(--adm-card)">
    <div class="flex items-start gap-3 p-4" style="border-bottom: 1px solid {{ $s['border'] }}">
        <div class="rounded-lg p-2 shrink-0 mt-0.5" style="background: {{ $s['iconBg'] }}">
            <span class="material-icons !text-[17px]" style="color: {{ $s['iconColor'] }}">{{ $icon }}</span>
        </div>
        <div class="min-w-0 pt-0.5">
            <p class="font-semibold text-sm leading-tight" style="color: var(--adm-fg)">{{ $title }}</p>
            @if($description)
            <p class="text-[11px] mt-1 leading-[1.5]" style="color: var(--adm-fg-2)">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div class="p-4 flex flex-col gap-3 flex-1">
        @if(isset($body))
        <div class="space-y-2.5 flex-1">{{ $body }}</div>
        @endif
        @if(isset($action))
        <div class="mt-auto pt-1">{{ $action }}</div>
        @endif
    </div>
</div>
