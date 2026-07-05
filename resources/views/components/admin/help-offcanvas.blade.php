@props(['moduleKey'])

@php
    $manifest = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
    $randomId = 'help-' . $moduleKey . '-' . \Illuminate\Support\Str::random(8);
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
            <span>{{ $manifest['help']['title'] ?? 'راهنمای ماژول' }}</span>
        </h5>
        <button type="button"
            class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 rounded transition text-slate dark:text-slate-300"
            data-close-offcanvas>
            <span class="material-icons">close</span>
        </button>
    </div>

    <!-- Content -->
    <div class="px-6 py-6 space-y-6 text-sm">
        @if ($manifest && isset($manifest['help']['sections']))
            @foreach ($manifest['help']['sections'] as $index => $section)
                <div>
                    @if (isset($section['heading']))
                        <h3 class="font-bold text-base text-slate dark:text-slate-100 mb-3 pb-2 border-b-2 border-primary/30">
                            {{ $section['heading'] }}
                        </h3>
                    @endif

                    @if ($section['type'] === 'text')
                        <div class="text-slate-600 dark:text-slate-300 leading-relaxed whitespace-pre-wrap text-sm space-y-2">
                            {{ $section['content'] }}
                        </div>

                    @elseif ($section['type'] === 'code')
                        <pre class="bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 p-4 rounded-lg overflow-x-auto text-xs border border-slate-200 dark:border-slate-700 font-mono"><code>{{ $section['content'] }}</code></pre>

                    @elseif ($section['type'] === 'tip')
                        <div class="bg-success/10 dark:bg-success/20 border border-success/30 dark:border-success/50 rounded-lg p-4 text-success dark:text-success/90">
                            <div class="flex gap-3">
                                <span class="material-icons text-lg shrink-0 mt-0.5">lightbulb</span>
                                <div class="text-sm leading-relaxed">{{ $section['content'] }}</div>
                            </div>
                        </div>

                    @elseif ($section['type'] === 'warning')
                        <div class="bg-warning/10 dark:bg-warning/20 border border-warning/30 dark:border-warning/50 rounded-lg p-4 text-warning dark:text-warning/90">
                            <div class="flex gap-3">
                                <span class="material-icons text-lg shrink-0 mt-0.5">warning</span>
                                <div class="text-sm leading-relaxed">{{ $section['content'] }}</div>
                            </div>
                        </div>

                    @elseif ($section['type'] === 'table' && isset($section['data']))
                        <div class="overflow-x-auto rounded-lg border border-slate-200 dark:border-slate-700">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                                        @foreach ($section['data']['headers'] as $header)
                                            <th class="px-4 py-3 text-left font-bold text-slate-900 dark:text-slate-100">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section['data']['rows'] as $row)
                                        <tr class="border-b border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 transition">
                                            @foreach ($row as $cell)
                                                <td class="px-4 py-3 text-slate-700 dark:text-slate-300">{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if ($index < count($manifest['help']['sections']) - 1)
                    <hr class="border-slate-200 dark:border-slate-700 my-2">
                @endif
            @endforeach
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

    /* Dark mode scrollbar */
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
