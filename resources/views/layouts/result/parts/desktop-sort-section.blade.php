@if(isset($Data['data']['sort_options']))
    <div id="resultDesktopSort" class="hidden md:block mb-4">
        <div class="flex h-14 items-center gap-x-2 rounded-squircle bg-white px-2 shadow-base dark:bg-slate lg:px-6">
            <div class="flex items-center gap-2 text-slate-400 ml-4">
                <svg class="h-5 w-5"><use xlink:href="#sort"/></svg>
                <p class="text-xs font-bold uppercase tracking-tight">مرتب‌سازی:</p>
            </div>
            
            <div class="flex items-center gap-1">
                @foreach($Data['data']['sort_options'] as $by)
                    @if($loop->index > 4)
                        @break
                    @endif
                    @php($isActive = (int)request()->query('sort', 1) === (int)$by['id'])
                    <button type="button"
                            data-sort-url="{{request()->fullUrlWithQuery(['sort'=>$by['id'], 'page' => 1])}}"
                            class="cursor-pointer rounded-full px-4 py-2 text-xs font-bold transition-all {{ $isActive ? 'bg-primary text-white shadow-md shadow-primary/20' : 'text-slate-500 hover:bg-slate-50 dark:text-slate-400 dark:hover:bg-white/5' }}">
                        {{$by['title_fa']}}
                    </button>
                @endforeach
            </div>
        </div>
    </div>
@endif
