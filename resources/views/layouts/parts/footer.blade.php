<footer class="border-t border-slate/70 bg-white/95 py-6 dark:border-white/10 dark:bg-slate-900/95">
    <div class="container">
        <div class="flex flex-col items-center justify-between gap-3 md:flex-row">
            <p class="text-sm text-slate-700 dark:text-slate-200">© {{ date('Y') }} کالندز</p>
            <div class="flex items-center gap-2">
                <a href="{{ route('contact.show') }}" class="rounded-squircle border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-primary hover:text-primary dark:border-white/10 dark:text-slate-200 dark:hover:border-primary dark:hover:text-primary">تماس با ما</a>
                <button id="scroll-top-button-footer" type="button" class="rounded-squircle border border-slate-300 px-3 py-2 text-xs font-medium text-slate-700 transition hover:border-slate-500 dark:border-white/10 dark:text-slate-200 dark:hover:border-white/20">برگشت به بالا</button>
            </div>
        </div>
    </div>
</footer>
