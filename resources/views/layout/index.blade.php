
<!DOCTYPE html>
<html dir="ltr" lang="en">
@php
    $baseUrl = url('');
    // Check if 'bpskalsel.com' exists in the base URL
    if (strpos($baseUrl, 'bpskalsel.com') == true || strpos($baseUrl, 'statkalsel.com') == true) {
        // Replace 'http://' with 'https://'
        $baseUrl = str_replace('http://', 'https://', $baseUrl);
    }
@endphp
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">    
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <meta name="_token" content="{{ csrf_token() }}">
    <meta name="base_url" content="{{ $baseUrl }}">  
    {{-- <meta name="base_url" content="{{ config('app.url') }}"> --}}
    <title>@yield('title') - {{config('app.name')}}</title>        
    <link rel="shortcut icon" href="{{ url('public/image/meetappico.png') }}" type="image/png">
        
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/bootstrap-datepicker/css/bootstrap-datepicker.min.css') }}">    
    <link rel="stylesheet" type="text/css" href="{{ url('public/assets/sweetalert/sweetalert2.min.css') }}">    
    @yield('css')
    <link href="{{ url('public/css/style.min.css')}}" rel="stylesheet">    
    
</head>

<body>
    <div class="preloader">
        <div class="lds-ripple">
            <div class="lds-pos"></div>
            <div class="lds-pos"></div>
        </div>
    </div>

    <div id="main-wrapper">
        @include('includes.header')
        @include('includes.sidebar')
        <div class="page-wrapper">
            <div class="container-fluid">
                @yield('content')
            </div>
            <footer class="footer">
                © 2020 MeetApp Kalsel
            </footer>
        </div>
        
    </div>
    
    
    <script src="{{ url('public/js/jquery.min.js')}}"></script>    
    <script src="{{ url('public/js/jquery-ui.min.js')}}"></script>    
    <script src="{{ url('public/js/popper.min.js')}}"></script>
    <script src="{{ url('public/js/bootstrap.min.js')}}"></script>    
    <script src="{{ url('public/admin/app.min.js')}}"></script>
    <script src="{{ url('public/admin/app.init.horizontal-fullwidth.js')}}"></script>
    <script src="{{ url('public/admin/app-style-switcher.horizontal.js')}}"></script>    
    <script src="{{ url('public/admin/perfect-scrollbar.jquery.min.js')}}"></script>
    <script src="{{ url('public/admin/sparkline.js') }}"></script>    
    <script src="{{ url('public/admin/waves.js') }}"></script>    
    <script src="{{ url('public/admin/sidebarmenu.js') }}"></script>    
    <script src="{{ url('public/admin/feather.min.js') }}"></script>
    <script src="{{ url('public/admin/custom.min.js') }}"></script>   
    <script src="{{ url('public/assets/sweetalert/sweetalert2.all.min.js') }}"></script>   
    @yield('js')
</body>

</html>