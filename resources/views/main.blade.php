<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>GENS - Excel Navigation System -</title>

    <!-- Scripts -->
    <script src="{{ asset('vendor/sheetjs/xlsx.full.min.js') }}"></script>
    <script src="{{ asset('vendor/sheetjs/shim.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/encoding-japanese/1.0.30/encoding.min.js"></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@4.x/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Styles -->
    <!-- Bootstrap Core CSS -->
    <link href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/dropify/dist/css/dropify.min.css') }}">
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
    <!-- Custom CSS -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <!-- You can change the theme colors from here -->
    <link href="{{ asset('css/colors/default.css') }}" id="theme" rel="stylesheet">
    <link href="{{ asset('css/upload.css') }}" rel="stylesheet">

</head>
<body>
    <div id="app">        
        <main-container-component>
            
        </main-container-component>
    </div>
    <!-- ============================================================== -->
    <!-- All Jquery -->
    <!-- ============================================================== -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <!-- Bootstrap tether Core JavaScript -->
    <script src="{{ asset('vendor/bootstrap/js/popper.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.min.js') }}"></script>
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/7.29.2/sweetalert2.all.js"></script>

    <!--Wave Effects -->
    <script src="{{ asset('js/waves.js') }}"></script>
    <!--Menu sidebar -->
    <script src="{{ asset('js/sidebarmenu.js') }}"></script>

    <script src=" {{ mix('js/app.js') }} "></script>

    <script>
        Vue.config.errorHandler = (err, vm, info) => {
            console.log(`Captured in Vue.config.errorHandler: ${info}`, err);
        };
        window.addEventListener("error", event => {
            console.log("Captured in error EventListener", event.error);
        });
        window.addEventListener("unhandledrejection", event => {
            console.log("Captured in unhandledrejection EventListener", event.reason);
        });
    </script>
</body>
</html>
