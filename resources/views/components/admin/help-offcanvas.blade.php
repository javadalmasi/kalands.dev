@props(['moduleKey'])

@php
    $manifest = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
    $randomId = 'help-' . $moduleKey . '-' . \Illuminate\Support\Str::random(8);
@endphp

<!-- Help Button -->
<button type="button"
    class="admin-btn admin-btn-ghost !text-base"
    onclick="document.getElementById('{{ $randomId }}').classList.toggle('hidden')"
    title="راهنمای ماژول">
    <span class="material-icons">help_outline</span>
</button>

<!-- Help Offcanvas (Sidebar) -->
<div id="{{ $randomId }}"
    class="hidden fixed inset-y-0 left-0 z-50 w-[450px] max-w-[90vw] bg-slate-900 text-slate-100 shadow-lg transition-all duration-300 overflow-y-auto"
    dir="rtl">
    <!-- Header -->
    <div class="sticky top-0 z-10 border-b border-slate-700 bg-slate-900 p-4 flex items-center justify-between">
        <h5 class="font-bold text-base flex items-center gap-2">
            <span class="material-icons">help_outline</span>
            {{ $manifest['help']['title'] ?? 'راهنمای ماژول' }}
        </h5>
        <button type="button"
            class="p-1 hover:bg-slate-800 rounded transition"
            onclick="document.getElementById('{{ $randomId }}').classList.toggle('hidden')">
            <span class="material-icons text-lg">close</span>
        </button>
    </div>

    <!-- Content -->
    <div class="p-4 space-y-6 text-sm">
        @if ($manifest && isset($manifest['help']['sections']))
            @foreach ($manifest['help']['sections'] as $index => $section)
                <div>
                    @if (isset($section['heading']))
                        <h3 class="font-bold text-base text-slate-100 mb-3 pb-2 border-b border-slate-700">
                            {{ $section['heading'] }}
                        </h3>
                    @endif

                    @if ($section['type'] === 'text')
                        <div class="text-slate-300 leading-relaxed whitespace-pre-wrap text-xs">
                            {{ $section['content'] }}
                        </div>

                    @elseif ($section['type'] === 'code')
                        <pre class="bg-slate-800 text-slate-200 p-3 rounded-lg overflow-x-auto text-xs border border-slate-700"><code>{{ $section['content'] }}</code></pre>

                    @elseif ($section['type'] === 'tip')
                        <div class="bg-success/20 border border-success/50 rounded-lg p-3 text-success/90">
                            <div class="flex gap-2">
                                <span class="material-icons text-lg shrink-0">lightbulb</span>
                                <div class="text-xs leading-relaxed">{{ $section['content'] }}</div>
                            </div>
                        </div>

                    @elseif ($section['type'] === 'warning')
                        <div class="bg-warning/20 border border-warning/50 rounded-lg p-3 text-warning/90">
                            <div class="flex gap-2">
                                <span class="material-icons text-lg shrink-0">warning</span>
                                <div class="text-xs leading-relaxed">{{ $section['content'] }}</div>
                            </div>
                        </div>

                    @elseif ($section['type'] === 'table' && isset($section['data']))
                        <div class="overflow-x-auto">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-700">
                                        @foreach ($section['data']['headers'] as $header)
                                            <th class="px-3 py-2 text-left font-bold text-slate-100 bg-slate-800/50">{{ $header }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($section['data']['rows'] as $row)
                                        <tr class="border-b border-slate-700 hover:bg-slate-800/30 transition">
                                            @foreach ($row as $cell)
                                                <td class="px-3 py-2 text-slate-300">{{ $cell }}</td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if ($index < count($manifest['help']['sections']) - 1)
                    <hr class="border-slate-700">
                @endif
            @endforeach
        @else
            <div class="text-center text-slate-400 py-8">
                <span class="material-icons block text-2xl mb-2">info</span>
                <p class="text-xs">راهنما برای این ماژول موجود نیست.</p>
            </div>
        @endif
    </div>
</div>

<!-- Backdrop (overlay) -->
<div id="{{ $randomId }}-backdrop"
    class="hidden fixed inset-0 z-40 bg-black/50 transition-opacity duration-300"
    onclick="document.getElementById('{{ $randomId }}').classList.toggle('hidden'); document.getElementById('{{ $randomId }}-backdrop').classList.toggle('hidden');">
</div>

<script>
    // Toggle backdrop when sidebar opens/closes
    const sidebar = document.getElementById('{{ $randomId }}');
    const backdrop = document.getElementById('{{ $randomId }}-backdrop');
    const observer = new MutationObserver(() => {
        if (sidebar.classList.contains('hidden')) {
            backdrop.classList.add('hidden');
        } else {
            backdrop.classList.remove('hidden');
        }
    });
    observer.observe(sidebar, { attributes: true, attributeFilter: ['class'] });
</script>

<style>
    #{{ $randomId }} {
        scrollbar-width: thin;
        scrollbar-color: rgba(148, 163, 184, 0.3) transparent;
    }

    #{{ $randomId }}::-webkit-scrollbar {
        width: 6px;
    }

    #{{ $randomId }}::-webkit-scrollbar-track {
        background: transparent;
    }

    #{{ $randomId }}::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.3);
        border-radius: 3px;
    }

    #{{ $randomId }}::-webkit-scrollbar-thumb:hover {
        background: rgba(148, 163, 184, 0.5);
    }
</style>
