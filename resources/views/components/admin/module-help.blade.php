@props(['moduleKey'])

@php
    $manifest = app(\App\Services\ModuleRegistry::class)->helpManifest($moduleKey);
@endphp

@if ($manifest && isset($manifest['help']['sections']))
    <div class="space-y-6 p-6">
        @foreach ($manifest['help']['sections'] as $section)
            <div>
                @if (isset($section['heading']))
                    <h3 class="font-bold text-base text-slate mb-3">{{ $section['heading'] }}</h3>
                @endif

                @if ($section['type'] === 'text')
                    <p class="text-sm text-slate/70 leading-relaxed">{{ $section['content'] }}</p>
                @elseif ($section['type'] === 'code')
                    <pre class="bg-slate-900 text-slate-100 p-4 rounded-lg overflow-x-auto text-xs"><code>{{ $section['content'] }}</code></pre>
                @elseif ($section['type'] === 'tip')
                    <div class="bg-success/10 border border-success/30 rounded-lg p-4 text-sm text-success/80">
                        <div class="flex gap-2">
                            <span class="material-icons text-lg shrink-0">lightbulb</span>
                            <div>{{ $section['content'] }}</div>
                        </div>
                    </div>
                @elseif ($section['type'] === 'warning')
                    <div class="bg-warning/10 border border-warning/30 rounded-lg p-4 text-sm text-warning/80">
                        <div class="flex gap-2">
                            <span class="material-icons text-lg shrink-0">warning</span>
                            <div>{{ $section['content'] }}</div>
                        </div>
                    </div>
                @elseif ($section['type'] === 'table' && isset($section['data']))
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr class="border-b border-slate/20">
                                    @foreach ($section['data']['headers'] as $header)
                                        <th class="px-3 py-2 text-left font-bold text-slate">{{ $header }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($section['data']['rows'] as $row)
                                    <tr class="border-b border-slate/10">
                                        @foreach ($row as $cell)
                                            <td class="px-3 py-2 text-slate/70">{{ $cell }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="p-6 text-center text-slate/50">
        <p class="text-sm">راهنما برای این ماژول موجود نیست.</p>
    </div>
@endif
