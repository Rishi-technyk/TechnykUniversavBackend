<!DOCTYPE html>
<html lang="zxx">

<head>
    <meta charset="UTF-8">
    <meta name="description" content="Sona Template">
    <meta name="keywords" content="Sona, unica, creative, html">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $setting->project_name }} | @yield('title', 'Member')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('frontend/images/favicon.png') }}" type="image/png" sizes="any">
    
    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css?family=Lora:400,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Cabin:400,500,600,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

    <!-- Css Styles -->
    <link rel="stylesheet" href="{{ asset('frontend/css/bootstrap.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/font-awesome.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/elegant-icons.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/flaticon.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/owl.carousel.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/nice-select.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/jquery-ui.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/magnific-popup.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/slicknav.min.css') }}" type="text/css">
    <link rel="stylesheet" href="{{ asset('frontend/css/style.css') }}" type="text/css">
</head>
<style>
    #toast-container { z-index: 999999 !important; }

    #toast-container > div {
        opacity: 1 !important;
        color: #fff !important;
        border-radius: 6px !important;
        box-shadow: 0 6px 20px rgba(0,0,0,0.25) !important;
    }

    .toast-success { background-color: #28a745 !important; }
    .toast-error   { background-color: #dc3545 !important; }
    .toast-info    { background-color: #17a2b8 !important; }
    .toast-warning { background-color: #ffc107 !important; color: #000 !important; }

    .mt-15p {
        margin-top: 15%;
    }

    .contact-section {
        min-height: calc(70vh - 0px);
    }

    .user-profile a::after {
        background: transparent !important;
    }
</style>
<style>
    .pagination-wrapper {
        margin-top: 15px;
        text-align: center;
    }

    .pagination {
        justify-content: center;
    }

    .pagination .page-item {
        margin: 0 3px;
    }

    .pagination .page-link {
        padding: 4px 10px !important;
        font-size: 13px !important;
        border-radius: 6px !important;
        color: #1e3a8a !important;
        border: 1px solid #d1d5db !important;
        background: #fff !important;
        transition: all 0.2s ease;
    }

    .pagination .page-link:hover {
        background: #1e3a8a !important;
        color: #fff !important;
        border-color: #1e3a8a !important;
    }

    .pagination .page-item.active .page-link {
        background: #1e3a8a !important;
        border-color: #1e3a8a !important;
        color: #fff !important;
        font-weight: 600;
    }

    .pagination .page-item.disabled .page-link {
        background: #f3f4f6 !important;
        color: #9ca3af !important;
        border-color: #e5e7eb !important;
    }

    /* Ultra small option */
    .pagination .page-link {
        padding: 2px 8px !important;
        font-size: 12px !important;
    }
</style>
<body>
    <!-- Page Preloder -->
    <div id="preloder">
        <div class="loader"></div>
    </div>

    @include('frontend.layouts.header')

    <!-- Contact Section Begin -->
    <section class="contact-section">

        <!-- Content -->
        @yield('content')
        
    </section>
    <!-- Contact Section End -->

    <!-- Footer Section Begin -->
    @include('frontend.layouts.footer')
    <!-- Footer Section End -->

    <!-- Js Plugins -->
    <script src="{{ asset('frontend/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('frontend/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.nice-select.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('frontend/js/jquery.slicknav.js') }}"></script>
    <script src="{{ asset('frontend/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('frontend/js/main.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script>
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "timeOut": "3000",
            "positionClass": "toast-bottom-left"
        };
    </script>

    <script>
        $(document).ready(function () {

            @if(session('success'))
                toastr.success(@json(session('success')));
            @endif

            @if(session('error'))
                toastr.error(@json(session('error')));
            @endif

            @if(session('warning'))
                toastr.warning(@json(session('warning')));
            @endif

            @if(session('info'))
                toastr.info(@json(session('info')));
            @endif

        });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        $(document).ready(function(){
            $('#billPayment').modal('show');
        });
    </script>

    <script>
        $(document).ready(function () {

            $('.profile-toggle').click(function (e) {
                e.stopPropagation();
                $('.profile-dropdown').slideToggle(200);
            });

            $(document).click(function () {
                $('.profile-dropdown').slideUp(200);
            });

        });

    </script>
    @yield('script')
</body>

</html>