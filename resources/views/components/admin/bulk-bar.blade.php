@props([
    'action',
    'id'      => 'bulk-bar',
    'label'   => 'مورد',
    'confirm' => null,
])
{{--
  Usage:
    <x-admin.bulk-bar action="{{ route('...bulk') }}" id="bulk-users" label="کاربر"
        confirm="این اقدام روی موارد انتخاب‌شده اجرا می‌شود. ادامه می‌دهید؟">
        <x-slot:actions>
            <select name="action" class="admin-input">...</select>
            <button type="submit" class="admin-btn admin-btn-primary">اجرا</button>
        </x-slot:actions>
    </x-admin.bulk-bar>

  Each item checkbox needs:
    <input type="checkbox" name="ids[]" value="{{ $id }}" data-bulk-item form="{{ $formId }}" class="rounded">
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
    <div class="admin-bulk-inner">

        {{-- select-all --}}
        <label class="admin-bulk-select">
            <input type="checkbox" data-select-all="{{ $id }}">
            <span>انتخاب همه</span>
        </label>

        <span class="admin-bulk-sep"></span>

        {{-- count --}}
        <div class="admin-bulk-count">
            <span data-bulk-num="{{ $id }}" class="admin-bulk-count-num">۰</span>
            <span class="admin-bulk-count-label">{{ $label }} انتخاب شده</span>
        </div>

        {{-- actions (flex-1 via margin-right: auto in .admin-bulk-actions) --}}
        @if(isset($actions))
        <div class="admin-bulk-actions">
            {{ $actions }}
        </div>
        @endif

        {{-- clear --}}
        <button type="button" class="admin-bulk-clear" data-bulk-clear="{{ $id }}" title="لغو انتخاب">
            <span class="material-icons">close</span>
        </button>

    </div>
</form>
