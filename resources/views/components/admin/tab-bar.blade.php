@props([
    'tabs'  => [],   /* optional: array of {key, label, icon?} — alternatively use slot */
    'active' => null, /* active tab key when using $tabs array */
    'id'    => 'module-tabs',
])
@php
/* Ensure the wrapper id ends with "-tabs" for global init in admin-app.js */
$wrapperId = str_ends_with($id, '-tabs') ? $id : $id . '-tabs';
@endphp
<div class="admin-card !p-0 overflow-hidden mb-6">
    <div class="flex overflow-x-auto whitespace-nowrap admin-tab-bar" id="{{ $wrapperId }}">
        @if(count($tabs))
            @foreach($tabs as $tab)
            @php $isActive = ($tab['key'] === $active); @endphp
            <button
                type="button"
                class="admin-tab-btn {{ $isActive ? 'border-b-2 border-primary text-primary font-bold' : 'text-slate-500 font-medium' }}"
                data-tab-target="{{ $tab['key'] }}"
            >
                @if(!empty($tab['icon']))
                <span class="material-icons !text-[17px]">{{ $tab['icon'] }}</span>
                @endif
                <span class="hidden sm:inline">{{ $tab['label'] }}</span>
            </button>
            @endforeach
        @else
            {{ $slot }}
        @endif
    </div>
</div>
