@if (session('message'))
    <div class="font-medium bg-primary mt-3 mb-4 text-white px-3 py-2 rounded">
        <p>{{ session('message') }}</p>
    </div>
@endif
