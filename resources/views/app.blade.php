<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />

   <link rel="icon" type="image/png" href="{{asset('images/favicon.png')}}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EDMS Project</title>
    <link rel="stylesheet" href="{{asset('css/emoji-picker.css')}}">
    {{-- <link rel="stylesheet" href="{{asset('css/app.css')}}"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @vite(['resources/js/app.js', "resources/js/pages/{$page['component']}.vue"]) --}}

    <script>
      window.addEventListener('error', function(e) {
        fetch('/api/log-error', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: e.message, filename: e.filename, lineno: e.lineno, colno: e.colno, error: e.error ? e.error.stack : '' })
        });
      });
      window.addEventListener('unhandledrejection', function(e) {
        fetch('/api/log-error', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: 'Unhandled Promise Rejection: ' + (e.reason ? (e.reason.message || e.reason) : 'Unknown'), error: e.reason ? e.reason.stack : '' })
        });
      });
      const originalConsoleErrorBlade = console.error;
      console.error = function(...args) {
        fetch('/api/log-error', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ message: args.map(a => {
            if (a instanceof Error) return a.name + ': ' + a.message + '\n' + a.stack;
            if (typeof a === 'object' && a !== null) {
              if (a.message) return a.message + (a.stack ? '\n' + a.stack : '');
              try { return JSON.stringify(a, Object.getOwnPropertyNames(a)); } catch(e) { return String(a); }
            }
            return String(a);
          }).join(' ') })
        });
        originalConsoleErrorBlade.apply(console, args);
      };
    </script>
    <script>
      (function() {
        try {
          var userTheme = "{!! auth()->check() ? auth()->user()->theme : '' !!}";
          var userFontSize = "{!! auth()->check() ? auth()->user()->font_size : '' !!}";

          var theme = userTheme || localStorage.getItem('edms_theme');
          if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
          }
          
          var fontSize = userFontSize || localStorage.getItem('edms_fontSize');
          if (fontSize) {
            document.documentElement.style.fontSize = fontSize + 'px';
          } else {
            // Default font size fallback if none is stored
            document.documentElement.style.fontSize = '16px';
          }
        } catch (e) {}
      })();
    </script>

    @inertiaHead

    <style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide_scroll::-webkit-scrollbar {
      display: none;
    }
    
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide_scroll {
      -ms-overflow-style: none;  /* IE and Edge */
      scrollbar-width: none;  /* Firefox */
    }
   #app{
    width:100%;
   }
    </style>
  </head>
  {{-- <body class="w-full min-h-screen flex justify-start hide_scroll"> --}}
  <body class="min-h-screen flex items-center justify-start w-full">
   
  @inertia
    <!-- <div id="app">

    </div> -->
    <!-- <script type="module" src="/src/main.js"></script> -->
    {{-- <script src="{{asset('js/app.js')}}"></script> --}}
  </body>
</html>
