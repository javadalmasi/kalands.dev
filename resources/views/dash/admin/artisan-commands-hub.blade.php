<x-layouts.admin-dashboard title="دستورات Artisan" :helpModuleKey="'artisan_commands'">
    @php($authkey = request()->route('authkey'))

    <x-admin.page-header title="دستورات Artisan">
        <x-slot:actions>
            <span class="px-3 py-1.5 rounded-lg bg-slate/5 border border-slate/10 font-bold text-xs">
                <span class="material-icons text-base align-middle ml-1">terminal</span>
                {{ count($commands) }} دستور قابل اجرا
            </span>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.tab-bar id="artisan-tabs">
        <button class="admin-tab-btn border-b-2 border-primary text-primary font-bold" data-tab-target="tab-commands">
            <span class="material-icons text-base">terminal</span>
            <span>دستورات</span>
        </button>
        <button class="admin-tab-btn" data-tab-target="tab-logs">
            <span class="material-icons text-base">history</span>
            <span>آخرین اجراها</span>
            @if($recentLogs->isNotEmpty())
                <span class="bg-primary/10 text-primary px-1.5 py-0.5 rounded-full text-[10px]">{{ $recentLogs->count() }}</span>
            @endif
        </button>
    </x-admin.tab-bar>

    <div id="tab-commands" class="tab-content space-y-6">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($commands as $cmd)
                <div class="admin-card is-surface cursor-pointer hover:shadow-lg hover:-translate-y-0.5 transition-all duration-200 artisan-command-card"
                     data-command="{{ $cmd['command'] }}"
                     data-label="{{ $cmd['label'] }}"
                     data-description="{{ $cmd['description'] }}"
                     data-icon="{{ $cmd['icon'] }}"
                     data-danger="{{ ($cmd['danger'] ?? false) ? 'true' : 'false' }}"
                     data-warning-message="{{ $cmd['warning_message'] ?? '' }}">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-xl bg-primary/10 flex items-center justify-center shrink-0 border border-primary/20">
                            <span class="material-icons text-primary">{{ $cmd['icon'] }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-bold text-slate text-sm">{{ $cmd['label'] }}</h3>
                            <p class="mt-0.5 text-[11px] text-slate/60 font-mono ltr block">{{ $cmd['command'] }}</p>
                        </div>
                        <div class="shrink-0">
                            <span class="material-icons text-slate/30">chevron_left</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div id="tab-logs" class="tab-content hidden space-y-6">
        <div class="admin-card is-surface">
            <div class="flex items-center justify-between gap-4 mb-6 pb-4 border-b border-slate/10">
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-primary/10 p-2 text-primary">
                        <span class="material-icons">history</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate">آخرین اجراها</h2>
                        <p class="text-xs text-slate/60 mt-1">۱۵ اجرای آخر به همراه مجری و خروجی</p>
                    </div>
                </div>
            </div>

            @if($recentLogs->isEmpty())
                <div class="text-center py-10 opacity-50">
                    <span class="material-icons text-4xl block mb-2">history</span>
                    <p class="text-sm font-bold text-slate">هنوز دستوری اجرا نشده است.</p>
                </div>
            @else
                <div class="overflow-hidden rounded-lg border border-slate/10">
                    <table class="w-full text-right text-sm">
                        <thead class="bg-slate/5 border-b border-slate/10">
                            <tr>
                                <th class="p-3 font-bold text-[11px]">دستور</th>
                                <th class="p-3 font-bold text-[11px]">مجری</th>
                                <th class="p-3 font-bold text-[11px]">وضعیت</th>
                                <th class="p-3 font-bold text-[11px]">زمان</th>
                                <th class="p-3 font-bold text-[11px] text-center">خروجی</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate/5">
                            @foreach($recentLogs as $log)
                                <tr class="hover:bg-slate/5 transition-colors">
                                    <td class="p-3">
                                        <div class="font-bold text-slate text-xs">{{ $log->label }}</div>
                                        <div class="text-[10px] text-slate/50 font-mono ltr mt-0.5">{{ $log->command }}</div>
                                    </td>
                                    <td class="p-3 text-xs text-slate">{{ $log->admin_name }}</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $log->status === 'success' ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger' }}">
                                            {{ $log->status === 'success' ? 'موفق' : 'ناموفق' }}
                                        </span>
                                    </td>
                                    <td class="p-3 text-xs text-slate/60 font-mono ltr" dir="ltr">{{ $log->persian_executed_at }}</td>
                                    <td class="p-3 text-center">
                                        @if($log->output)
                                            <button type="button" class="admin-btn !py-1 !px-2 text-[10px] view-log-output" data-output="{{ $log->output }}">
                                                <span class="material-icons !text-sm">visibility</span>
                                                مشاهده
                                            </button>
                                        @else
                                            <span class="text-[10px] text-slate/40">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <dialog id="artisan-command-modal" class="admin-dialog w-[min(100vw-16px,640px)] max-w-[640px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <div class="flex items-center gap-3">
                    <div class="h-10 w-10 rounded-xl bg-primary/10 border border-primary/20 flex items-center justify-center shrink-0">
                        <span class="material-icons text-primary" id="artisan-modal-icon"></span>
                    </div>
                    <div>
                        <h3 class="admin-dialog-title" id="artisan-modal-title"></h3>
                        <p class="text-xs text-slate/60 font-mono ltr" id="artisan-modal-command"></p>
                    </div>
                </div>
                <button type="button" class="admin-toggle inline-flex" data-artisan-modal-close>
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="text-sm text-slate/80 leading-relaxed" id="artisan-modal-description"></div>

            <div id="artisan-modal-output" class="hidden mt-4">
                <h4 class="text-xs font-bold text-slate mb-2 flex items-center gap-1.5">
                    <span class="material-icons text-sm">output</span>
                    خروجی دستور
                </h4>
                <pre id="artisan-modal-output-text" class="bg-slate-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto max-h-64 ltr leading-relaxed font-mono whitespace-pre-wrap"></pre>
            </div>

            <div id="artisan-modal-password-section" class="hidden">
                <div class="border-t border-slate/10 pt-4 mt-2">
                    <label for="artisan-modal-password-input" class="block text-xs font-bold text-slate mb-1.5">رمز عبور فعلی (برای دستورات خطرناک)</label>
                    <div class="relative">
                        <input type="password" id="artisan-modal-password-input" class="admin-dialog-input w-full text-right pr-10" placeholder="رمز عبور خود را وارد کنید" autocomplete="off">
                        <button type="button" class="absolute left-2 top-1/2 -translate-y-1/2 text-slate/40 hover:text-slate transition-colors" id="artisan-modal-password-toggle" tabindex="-1">
                            <span class="material-icons text-base">visibility_off</span>
                        </button>
                    </div>
                </div>
            </div>

            <div id="artisan-modal-error" class="hidden mt-4 p-4 rounded-lg border text-sm flex items-center gap-3 bg-danger/10 border-danger/20 text-danger">
                <span class="material-icons">error</span>
                <span id="artisan-modal-error-text"></span>
            </div>

            <div id="artisan-modal-actions" class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-artisan-modal-close>
                    <span class="material-icons">close</span>
                    <span>انصراف</span>
                </button>
                <button type="button" class="admin-btn" id="artisan-modal-execute">
                    <span class="material-icons" id="artisan-modal-execute-icon">play_arrow</span>
                    <span id="artisan-modal-execute-text">اجرا</span>
                </button>
            </div>
            <div id="artisan-modal-executing" class="hidden">
                <div class="flex items-center justify-end gap-3 text-sm text-slate/60 pt-4 border-t border-slate/10">
                    <span class="material-icons animate-spin">refresh</span>
                    <span>در حال اجرا...</span>
                </div>
            </div>
        </div>
    </dialog>

    <dialog id="artisan-log-output-modal" class="admin-dialog w-[min(100vw-16px,700px)] max-w-[700px]">
        <div class="admin-dialog-body">
            <div class="admin-dialog-head">
                <h3 class="admin-dialog-title">خروجی دستور</h3>
                <button type="button" class="admin-toggle inline-flex" data-log-modal-close>
                    <span class="material-icons">close</span>
                </button>
            </div>
            <pre id="artisan-log-output-text" class="bg-slate-900 text-green-400 text-xs p-4 rounded-lg overflow-x-auto max-h-96 ltr leading-relaxed font-mono whitespace-pre-wrap"></pre>
            <div class="admin-dialog-actions">
                <button type="button" class="admin-btn admin-btn-secondary" data-log-modal-close>
                    <span class="material-icons">close</span>
                    <span>بستن</span>
                </button>
            </div>
        </div>
    </dialog>

</x-layouts.admin-dashboard>
@vite(['resources/js/admin-artisan-commands-hub.js'])
