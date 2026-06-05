@if (session('message'))
    <div class="font-medium mt-3 mb-4 px-4 py-3 rounded-xl border bg-primary/5 border-primary/20 text-primary">
        <div class="flex items-center gap-2">
            <span class="material-icons text-sm">check_circle</span>
            <span>{{ session('message') }}</span>
        </div>
    </div>
@endif
