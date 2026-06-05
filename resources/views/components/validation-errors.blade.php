@if($errors->any())
    @foreach($errors->all() as $error)
        <div class="font-medium mt-3 mb-4 px-4 py-3 rounded-xl border bg-danger/5 border-danger/20 text-danger">
            <div class="flex items-center gap-2">
                <span class="material-icons text-sm">error_outline</span>
                <span>{{ $error }}</span>
            </div>
        </div>
    @endforeach
@endif
