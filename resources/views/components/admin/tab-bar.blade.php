@props([
    'tabs',    /* array of {key, label, icon?} */
    'active',  /* active tab key */
    'id' => 'tab-bar',
])
<div class="admin-card !p-0 overflow-hidden mb-6">
    <div class="flex overflow-x-auto whitespace-nowrap" id="{{ $id }}" style="border-bottom: 1px solid rgba(255,255,255,0.07)">
        @foreach($tabs as $tab)
        @php $isActive = ($tab['key'] === $active); @endphp
        <button
            type="button"
            class="flex items-center gap-1.5 px-4 py-3 text-sm font-medium transition-colors shrink-0 relative"
            style="color: {{ $isActive ? '#10b981' : '#6b7280' }}; border-bottom: 2px solid {{ $isActive ? '#10b981' : 'transparent' }}; margin-bottom: -1px"
            data-tab-target="{{ $tab['key'] }}"
        >
            @if(!empty($tab['icon']))
            <span class="material-icons !text-[17px]">{{ $tab['icon'] }}</span>
            @endif
            <span class="hidden sm:inline">{{ $tab['label'] }}</span>
        </button>
        @endforeach
    </div>
</div>
