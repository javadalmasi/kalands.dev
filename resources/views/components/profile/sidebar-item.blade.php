@props([
    'href',
    'label',
    'active' => false
])

<li>
    <a
        href="{{ $href }}"
        class="flex items-center justify-between rounded-squircle px-3 py-2.5 text-sm font-bold transition-all {{ $active ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-slate-600 hover:bg-primary/5 hover:text-primary dark:text-slate-300 dark:hover:bg-white/5' }}"
    >
        <div class="flex items-center gap-x-4">
            <span>{{ $label }}</span>
        </div>
        @if($active)
            <svg class="h-4 w-4"><use xlink:href="#chevron-left"/></svg>
        @endif
    </a>
</li>
