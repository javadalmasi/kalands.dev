@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="font-medium bg-warning mt-3 mb-4 text-white px-3 py-2 rounded">
            {{ $error }}
        </div>
    @endforeach
@endif
