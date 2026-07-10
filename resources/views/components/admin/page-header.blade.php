@props([
    'title',
    'description' => null,
])
<div class="flex items-start justify-between gap-4 mb-6">
    <div>
        <h1 class="text-lg font-semibold" style="color: var(--adm-fg)">{{ $title }}</h1>
        @if($description)
        <p class="text-sm mt-1" style="color: var(--adm-fg-2)">{{ $description }}</p>
        @endif
    </div>
    @if(isset($actions))
    <div class="flex items-center gap-2 shrink-0">
        {{ $actions }}
    </div>
    @endif
</div>
