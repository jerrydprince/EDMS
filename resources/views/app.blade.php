<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <link rel="icon" type="image/svg+xml" href="/vite.svg" />

   <link rel="icon" type="image/png" href="{{asset('images/favicon.png')}}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>EDMS Project</title>
    {{-- <link rel="stylesheet" href="{{asset('css/app.css')}}"> --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- @vite(['resources/js/app.js', "resources/js/pages/{$page['component']}.vue"]) --}}

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
