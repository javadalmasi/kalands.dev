@if($errors->any())
    @foreach($errors->all() as $error)
        <p class="h-5 text-sm text-warning dark:text-warning">
            {{ $error }}
        </p>
    @endforeach
@endif
