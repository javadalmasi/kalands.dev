@props(['moduleKey'])

@php
    $markdownContent = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
    $randomId = 'help-' . $moduleKey . '-' . \Illuminate\Support\Str::random(8);

    // Convert markdown to HTML
    $html = '';
    if ($markdownContent) {
        $converter = new \League\CommonMark\CommonMarkConverter([
            'allow_unsafe_links' => false,
            'html_input' => 'strip',
        ]);
        $html = $converter->convert($markdownContent)->getContent();
    }

    // Extract title from markdown (first h1)
    $title = 'راهنمای ماژول';
    if (preg_match('/^#\s+(.+)$/m', $markdownContent, $matches)) {
        $title = $matches[1];
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

    /* Markdown Styling */
    .markdown-content {
        color: rgb(71, 85, 105);
    }

    .markdown-content h2 {
        font-size: 0.875rem;
        font-weight: 700;
        color: rgb(15, 23, 42);
        margin-top: 0.75rem;
        margin-bottom: 0.5rem;
        padding-bottom: 0.375rem;
        border-bottom: 2px solid rgba(59, 130, 246, 0.3);
    }

    .markdown-content h3 {
        font-size: 0.8125rem;
        font-weight: 600;
        color: rgb(30, 41, 59);
        margin-top: 0.5rem;
        margin-bottom: 0.375rem;
    }

    .markdown-content p {
        font-size: 0.75rem;
        line-height: 1.4;
        margin-bottom: 0.5rem;
    }

    .markdown-content ul,
    .markdown-content ol {
        margin-left: 1rem;
        margin-bottom: 0.5rem;
    }

    .markdown-content li {
        font-size: 0.75rem;
        line-height: 1.4;
        margin-bottom: 0.25rem;
    }

    .markdown-content code {
        background-color: rgb(241, 245, 249);
        color: rgb(30, 41, 59);
        padding: 0.125rem 0.375rem;
        border-radius: 0.25rem;
        font-size: 0.7rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    }

    .markdown-content pre {
        background-color: rgb(241, 245, 249);
        color: rgb(15, 23, 42);
        padding: 0.75rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin-bottom: 0.5rem;
        border: 1px solid rgb(226, 232, 240);
        font-size: 0.7rem;
        line-height: 1.3;
    }

    .markdown-content pre code {
        background: none;
        color: inherit;
        padding: 0;
        border-radius: 0;
    }

    .markdown-content table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 0.5rem;
        font-size: 0.75rem;
    }

    .markdown-content table th {
        background-color: rgb(241, 245, 249);
        color: rgb(15, 23, 42);
        font-weight: 600;
        padding: 0.5rem;
        text-align: right;
        border: 1px solid rgb(226, 232, 240);
    }

    .markdown-content table td {
        padding: 0.5rem;
        border: 1px solid rgb(226, 232, 240);
        color: rgb(71, 85, 105);
    }

    .markdown-content table tr:hover {
        background-color: rgba(241, 245, 249, 0.5);
    }

    .markdown-content blockquote {
        border-right: 3px solid rgb(59, 130, 246);
        padding-right: 0.75rem;
        margin: 0.5rem 0;
        color: rgb(100, 116, 139);
        font-size: 0.75rem;
        font-style: italic;
    }

    .markdown-content em {
        font-style: italic;
    }

    .markdown-content strong {
        font-weight: 600;
    }

    /* Dark mode */
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

        .markdown-content {
            color: rgb(203, 213, 225);
        }

        .markdown-content h2,
        .markdown-content h3 {
            color: rgb(226, 232, 240);
        }

        .markdown-content code {
            background-color: rgb(30, 41, 59);
            color: rgb(226, 232, 240);
        }

        .markdown-content pre {
            background-color: rgb(15, 23, 42);
            color: rgb(203, 213, 225);
            border-color: rgb(51, 65, 85);
        }

        .markdown-content table th {
            background-color: rgb(30, 41, 59);
            color: rgb(226, 232, 240);
            border-color: rgb(51, 65, 85);
        }

        .markdown-content table td {
            border-color: rgb(51, 65, 85);
            color: rgb(203, 213, 225);
        }

        .markdown-content table tr:hover {
            background-color: rgba(30, 41, 59, 0.5);
        }

        .markdown-content blockquote {
            color: rgb(148, 163, 184);
        }
    }
</style>
