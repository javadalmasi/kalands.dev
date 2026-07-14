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
  <script>
    (function () {
      var s = document.createElement('script');
      s.type = 'module';
      s.src = 'https://static.cloudflareinsights.com/beacon.min.js';
      s.setAttribute('data-cf-beacon', '{"token": "79916ac2df7e4baf8ebc927082b62d71"}');
      s.onerror = function () {};
      document.body.appendChild(s);
    })();
  </script>
</body>
