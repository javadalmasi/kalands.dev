@props([
    'action',
    'id'      => 'bulk-bar',
    'label'   => 'مورد انتخاب شده',
    'confirm' => null,
])
{{--
  Floating bulk-action bar — slides up from bottom when items are selected.

  Usage in blade:
    <x-admin.bulk-bar action="{{ route('...bulk') }}" id="bulk-users" confirm="این اقدام روی موارد انتخاب‌شده اجرا می‌شود. ادامه می‌دهید؟">
        <x-slot:actions>
            <select name="action" class="admin-input !w-auto !min-h-0 !h-8 !text-xs">
                <option>عملیات...</option>
                <option value="delete">حذف</option>
            </select>
            <button type="submit" class="admin-btn admin-btn-primary !h-8 !text-xs">
                <span class="material-icons !text-sm">done_all</span>
                اجرا
            </button>
        </x-slot:actions>
    </x-admin.bulk-bar>

  On each item's checkbox:
    <input type="checkbox" name="ids[]" value="{{ $item->id }}"
           data-bulk-item form="{{ $id }}" class="rounded">
--}}
<form
    id="{{ $id }}"
    action="{{ $action }}"
    method="POST"
    class="admin-bulk-bar"
    data-bulk-bar="{{ $id }}"
    @if($confirm) data-confirm="{{ $confirm }}" @endif
>
    @csrf
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3 sm:gap-4">

        {{-- Select all --}}
        <label class="flex items-center gap-2 cursor-pointer shrink-0 select-none">
            <input
                type="checkbox"
                data-select-all="{{ $id }}"
                class="w-4 h-4 rounded border-white/30 bg-white/10 text-primary focus:ring-primary/40 focus:ring-1 cursor-pointer"
            >
            <span class="text-sm font-medium hidden sm:inline" style="color: rgba(255,255,255,0.75)">همه</span>
        </label>

        <div class="w-px h-5 shrink-0" style="background: rgba(255,255,255,0.12)"></div>

        {{-- Count --}}
        <div class="flex items-center gap-1.5 shrink-0">
            <span class="text-sm font-bold tabular-nums" style="color: rgba(255,255,255,0.95)" data-bulk-num="{{ $id }}">۰</span>
            <span class="text-sm" style="color: rgba(255,255,255,0.65)">{{ $label }}</span>
        </div>

        <div class="flex-1"></div>

        {{-- Actions slot --}}
        @if(isset($actions))
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
        @endif

        {{-- Clear selection --}}
        <button
            type="button"
            data-bulk-clear="{{ $id }}"
            class="w-8 h-8 rounded-lg flex items-center justify-center transition-colors shrink-0"
            style="background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.55)"
            title="لغو انتخاب"
        >
            <span class="material-icons !text-sm">close</span>
        </button>
    </div>
</form>
