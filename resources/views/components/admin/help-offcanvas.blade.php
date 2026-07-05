@props(['moduleKey'])

@php
    $markdownContent = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
    $randomId = 'help-' . $moduleKey . '-' . \Illuminate\Support\Str::random(8);

    // Convert markdown to HTML
    $html = '';
    $title = 'راهنمای ماژول';

    if ($markdownContent) {
        $converter = app(\App\Services\MarkdownConverter::class);
        $html = $converter->convert($markdownContent);

        // Extract title from markdown (first h1)
        if (preg_match('/^#\s+(.+)$/m', $markdownContent, $matches)) {
            $title = $matches[1];
        }
    }
@endphp

<!-- Help Button -->
<button type="button"
    id="help-btn-{{ $randomId }}"
    class="admin-toggle inline-flex relative cursor-pointer"
    title="راهنمای ماژول"
    aria-label="راهنمای ماژول">
    <span class="material-icons text-slate dark:text-slate-300">help_outline</span>
</button>

<!-- Help Offcanvas (Sidebar) -->
<div id="{{ $randomId }}"
    class="hidden fixed inset-y-0 left-0 z-50 w-[450px] max-w-[90vw] bg-white dark:bg-slate-900 shadow-2xl transition-all duration-300 overflow-y-auto"
    dir="rtl">
    <!-- Header -->
    <div class="sticky top-0 z-10 border-b border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 px-6 py-4 flex items-center justify-between">
        <h5 class="font-bold text-lg flex items-center gap-3 text-slate dark:text-slate-100">
            <span class="material-icons text-primary">help_outline</span>
            <span>{{ $title }}</span>
        </h5>
        <button type="button"
            class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition text-slate dark:text-slate-300"
            data-close-offcanvas>
            <span class="material-icons">close</span>
        </button>
    </div>

    <!-- Content -->
    <div class="px-5 py-4 text-sm markdown-content">
        @if ($html)
            {!! $html !!}
        @else
            <div class="text-center text-slate-400 dark:text-slate-500 py-8">
                <span class="material-icons block text-3xl mb-2">info</span>
                <p class="text-sm">راهنما برای این ماژول موجود نیست.</p>
            </div>
        @endif
    </div>
</div>

<!-- Backdrop (overlay) -->
<div id="{{ $randomId }}-backdrop"
    class="hidden fixed inset-0 z-40 bg-black/50 transition-opacity duration-300 cursor-pointer">
</div>

<style>
    #{{ $randomId }} {
        scrollbar-width: thin;
        scrollbar-color: rgba(100, 116, 139, 0.5) transparent;
    }

    #{{ $randomId }}::-webkit-scrollbar {
        width: 8px;
    }

    #{{ $randomId }}::-webkit-scrollbar-track {
        background: transparent;
    }

    #{{ $randomId }}::-webkit-scrollbar-thumb {
        background: rgba(100, 116, 139, 0.5);
        border-radius: 4px;
    }

    #{{ $randomId }}::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.8);
    }

    @media (prefers-color-scheme: dark) {
        #{{ $randomId }} {
            scrollbar-color: rgba(71, 85, 105, 0.6) transparent;
        }

        #{{ $randomId }}::-webkit-scrollbar-thumb {
            background: rgba(71, 85, 105, 0.6);
        }

        #{{ $randomId }}::-webkit-scrollbar-thumb:hover {
            background: rgba(71, 85, 105, 0.9);
        }
    }
</style>
