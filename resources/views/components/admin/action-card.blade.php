@props([
    'title',
    'description' => null,
    'icon',
    'variant'     => 'default',
])
@php
$borderClass = match($variant) {
    'danger'  => 'border-danger/20 dark:border-danger/15',
    'success' => 'border-success/20 dark:border-success/15',
    'info'    => 'border-info/20 dark:border-info/15',
    default   => 'border-slate/15 dark:border-white/[0.07]',
};
$bgClass = match($variant) {
    'danger'  => 'bg-danger/[0.025] dark:bg-danger/[0.06]',
    'success' => 'bg-success/[0.025] dark:bg-success/[0.06]',
    'info'    => 'bg-info/[0.025] dark:bg-info/[0.06]',
    default   => 'bg-slate/[0.02] dark:bg-white/[0.02]',
};
$iconBg = match($variant) {
    'danger'  => 'bg-danger/10',
    'success' => 'bg-success/10',
    'info'    => 'bg-info/10',
    default   => 'bg-slate/10 dark:bg-white/10',
};
$iconColor = match($variant) {
    'danger'  => 'text-danger',
    'success' => 'text-success',
    'info'    => 'text-info',
    default   => 'text-slate/60 dark:text-white/50',
};
@endphp
<div class="rounded-xl border {{ $borderClass }} {{ $bgClass }} overflow-hidden h-full flex flex-col">
    <div class="flex items-start gap-3 p-4 border-b {{ $borderClass }}">
        <div class="rounded-lg {{ $iconBg }} p-2 shrink-0 mt-0.5">
            <span class="material-icons text-lg {{ $iconColor }}">{{ $icon }}</span>
        </div>
        <div class="min-w-0 pt-0.5">
            <p class="font-bold text-sm text-slate leading-tight">{{ $title }}</p>
            @if($description)
            <p class="text-[11px] text-slate/50 dark:text-white/40 mt-1 leading-[1.5]">{{ $description }}</p>
            @endif
        </div>
    </div>
    <div class="p-4 flex flex-col gap-3 flex-1">
        @if(isset($body))
        <div class="space-y-2.5 flex-1">
            {{ $body }}
        </div>
        @endif
        @if(isset($action))
        <div class="mt-auto pt-1">
            {{ $action }}
        </div>
        @endif
    </div>
</div>
