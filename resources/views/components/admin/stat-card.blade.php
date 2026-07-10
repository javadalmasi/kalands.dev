@props([
    'title',
    'value',
    'icon',
    'color'   => 'primary',
    'trend'   => null,
    'trendUp' => true,
])
@php
$colors = [
    'primary' => ['icon' => '#10b981', 'bg' => 'rgba(16,185,129,0.1)',  'border' => 'rgba(16,185,129,0.15)'],
    'success' => ['icon' => '#22c55e', 'bg' => 'rgba(34,197,94,0.1)',   'border' => 'rgba(34,197,94,0.15)'],
    'warning' => ['icon' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)',  'border' => 'rgba(245,158,11,0.15)'],
    'danger'  => ['icon' => '#ef4444', 'bg' => 'rgba(239,68,68,0.1)',   'border' => 'rgba(239,68,68,0.15)'],
    'info'    => ['icon' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.1)',  'border' => 'rgba(59,130,246,0.15)'],
];
$c = $colors[$color] ?? $colors['primary'];
@endphp
<div class="admin-card flex items-start gap-3">
    <div class="rounded-lg p-2 shrink-0 mt-0.5" style="background: {{ $c['bg'] }}; border: 1px solid {{ $c['border'] }}">
        <span class="material-icons !text-lg" style="color: {{ $c['icon'] }}">{{ $icon }}</span>
    </div>
    <div class="min-w-0 flex-1">
        <p class="text-xs font-medium mb-1" style="color: var(--adm-fg-2)">{{ $title }}</p>
        <p class="text-2xl font-bold leading-none" style="color: var(--adm-fg)">{{ $value }}</p>
        @if($trend)
        <p class="text-xs mt-1.5 font-medium" style="color: {{ $trendUp ? '#10b981' : '#ef4444' }}">{{ $trend }}</p>
        @endif
    </div>
</div>
