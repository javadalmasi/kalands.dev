@props(['moduleKey'])

@php
    $markdownContent = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
    $randomId = 'help-' . $moduleKey . '-' . \Illuminate\Support\Str::random(8);

    // Convert markdown to HTML with table support
    $html = '';
    if ($markdownContent) {
        // Convert markdown tables to HTML
        $markdownContent = convertMarkdownTablesToHtml($markdownContent);

        $converter = new \League\CommonMark\CommonMarkConverter([
            'allow_unsafe_links' => false,
            'html_input' => 'strip',
        ]);
        $html = $converter->convert($markdownContent)->getContent();
    }

    function convertMarkdownTablesToHtml($markdown) {
        // Pattern to match markdown tables
        $pattern = '/\|(.+?)\n\|[-\s|:]+\n((?:\|.+\n)*)/s';

        return preg_replace_callback($pattern, function($matches) {
            $header = trim($matches[1]);
            $rows = trim($matches[2]);

            $headerCells = array_map('trim', explode('|', $header));
            $headerCells = array_filter($headerCells, fn($cell) => $cell !== '');

            $rowsArray = array_filter(array_map('trim', explode("\n", $rows)));

            $html = '<table><thead><tr>';
            foreach ($headerCells as $cell) {
                $html .= '<th>' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</th>';
            }
            $html .= '</tr></thead><tbody>';

            foreach ($rowsArray as $row) {
                $cells = array_map('trim', explode('|', $row));
                $cells = array_filter($cells, fn($cell) => $cell !== '');
                $html .= '<tr>';
                foreach ($cells as $cell) {
                    $html .= '<td>' . htmlspecialchars($cell, ENT_QUOTES, 'UTF-8') . '</td>';
                }
                $html .= '</tr>';
            }

            $html .= '</tbody></table>';
            return $html;
        }, $markdown);
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
        color: rgb(30, 41, 59);
    }

    .markdown-content h2 {
        font-size: 1rem;
        font-weight: 700;
        color: rgb(0, 0, 0);
        margin-top: 0.75rem;
        margin-bottom: 0.625rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(59, 130, 246, 0.4);
    }

    .markdown-content h3 {
        font-size: 0.9375rem;
        font-weight: 600;
        color: rgb(15, 23, 42);
        margin-top: 0.625rem;
        margin-bottom: 0.5rem;
    }

    .markdown-content p {
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 0.625rem;
        color: rgb(30, 41, 59);
    }

    .markdown-content ul,
    .markdown-content ol {
        margin-left: 1.25rem;
        margin-bottom: 0.625rem;
    }

    .markdown-content li {
        font-size: 0.875rem;
        line-height: 1.5;
        margin-bottom: 0.375rem;
        color: rgb(30, 41, 59);
    }

    .markdown-content code {
        background-color: rgb(240, 243, 247);
        color: rgb(15, 23, 42);
        padding: 0.1875rem 0.4375rem;
        border-radius: 0.3rem;
        font-size: 0.8125rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    }

    .markdown-content pre {
        background-color: rgb(240, 243, 247);
        color: rgb(15, 23, 42);
        padding: 1rem;
        border-radius: 0.5rem;
        overflow-x: auto;
        margin: 0.625rem 0;
        border: 1px solid rgb(226, 232, 240);
        font-size: 0.8125rem;
        line-height: 1.4;
    }

    .markdown-content pre code {
        background: none;
        color: inherit;
        padding: 0;
        border-radius: 0;
        font-size: inherit;
    }

    .markdown-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 0.875rem 0;
        font-size: 0.875rem;
        background-color: white;
        border: 1px solid rgb(226, 232, 240);
        border-radius: 0.375rem;
        overflow: hidden;
    }

    .markdown-content table th {
        background-color: rgb(248, 250, 252);
        color: rgb(0, 0, 0);
        font-weight: 600;
        padding: 0.625rem 0.75rem;
        text-align: right;
        border-bottom: 2px solid rgb(200, 210, 220);
        font-size: 0.875rem;
    }

    .markdown-content table td {
        padding: 0.625rem 0.75rem;
        border-bottom: 1px solid rgb(226, 232, 240);
        color: rgb(30, 41, 59);
        font-size: 0.875rem;
    }

    .markdown-content table tbody tr:last-child td {
        border-bottom: none;
    }

    .markdown-content table tr:hover {
        background-color: rgb(249, 250, 251);
    }

    .markdown-content blockquote {
        border-right: 3px solid rgb(59, 130, 246);
        padding-right: 0.875rem;
        margin: 0.625rem 0;
        color: rgb(30, 41, 59);
        font-size: 0.875rem;
        background-color: rgb(240, 245, 250);
        padding: 0.75rem;
        padding-right: 0.875rem;
        border-radius: 0.375rem;
    }

    .markdown-content em {
        font-style: italic;
        color: rgb(30, 41, 59);
    }

    .markdown-content strong {
        font-weight: 600;
        color: rgb(15, 23, 42);
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

        .markdown-content h2 {
            color: rgb(255, 255, 255);
            border-bottom-color: rgba(59, 130, 246, 0.5);
        }

        .markdown-content h3 {
            color: rgb(226, 232, 240);
        }

        .markdown-content p,
        .markdown-content li {
            color: rgb(203, 213, 225);
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

        .markdown-content table {
            background-color: rgb(20, 28, 40);
            border-color: rgb(51, 65, 85);
        }

        .markdown-content table th {
            background-color: rgb(30, 41, 59);
            color: rgb(226, 232, 240);
            border-bottom-color: rgb(71, 85, 105);
        }

        .markdown-content table td {
            border-bottom-color: rgb(51, 65, 85);
            color: rgb(203, 213, 225);
        }

        .markdown-content table tr:hover {
            background-color: rgba(30, 41, 59, 0.6);
        }

        .markdown-content blockquote {
            background-color: rgba(59, 130, 246, 0.1);
            border-right-color: rgb(59, 130, 246);
            color: rgb(148, 163, 184);
        }

        .markdown-content strong {
            color: rgb(226, 232, 240);
        }
    }
</style>
