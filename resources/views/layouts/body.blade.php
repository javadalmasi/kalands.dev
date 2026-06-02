<body>
@include('layouts.parts.svg')
<div class="flex min-h-screen flex-col">
    @if(!($clean ?? false))
        @include('layouts.parts.header')
    @endif
    @yield('content')
    @if(!($clean ?? false))
        @include('layouts.parts.footer')
    @endif
</div>
  @stack('scripts')
</body>
