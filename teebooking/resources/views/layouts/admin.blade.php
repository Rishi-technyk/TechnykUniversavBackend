<!DOCTYPE html>

<html lang="en">



<head>

    <meta charset="utf-8" />

    <meta content="width=device-width, initial-scale=1.0" name="viewport" />



    <title>{{ config('constants.APP_SHORT_NAME') }}</title>

    <meta content="" name="description" />

    <meta content="" name="keywords" />



    <!-- Favicons -->

    <!-- <link href="{{ asset('public/admin/assets/img/favicon.ico') }}" rel="icon" /> -->

    <!-- <link href="{{ asset('public/admin/assets/img/apple-icon.png') }}" rel="apple-touch-icon" /> -->



    <!-- Google Fonts -->

    <link href="https://fonts.gstatic.com" rel="preconnect" />

    <link

        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"

        rel="stylesheet" />



    <!-- Vendor CSS Files -->

    <link href="{{ asset('public/admin/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/quill/quill.snow.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet" />

    <link href="{{ asset('public/admin/assets/vendor/simple-datatables/style.css') }}" rel="stylesheet" />

    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.0/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.css">

    <link rel="stylesheet" type="text/css"

        href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">



    <!-- Template Main CSS File -->

    <link href="{{ asset('public/admin/assets/css/style.css') }}" rel="stylesheet" />



    {{--dont touch this--}}

    <meta name="_token" content="{{csrf_token()}}">

    {{--dont touch this--}}



    <!-- =======================================================

  * Template Name: NiceAdmin

  * Updated: Sep 18 2023 with Bootstrap v5.3.2

  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/

  * Author: BootstrapMade.com

  * License: https://bootstrapmade.com/license/

  ======================================================== -->

</head>



<body>

    <!-- ======= Header ======= -->

    @include('partials.admin.header')

    <!-- End Header -->



    <!-- ======= Sidebar ======= -->

    @include('partials.admin.sidebar')

    <!-- End Sidebar-->



    <!-- ======= #main ======= -->

    @yield('content')

    <!-- End #main -->



    <!-- ======= Footer ======= -->

    @include('partials.admin.footer')

    <!-- End Footer -->



    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i

            class="bi bi-arrow-up-short"></i></a>



    <!-- Vendor JS Files -->

    <!-- <script src="{{asset('public/admin/assets/js/jquery-3.6.0.min.js')}}"></script> -->

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    <script src="{{ asset('public/admin/assets/vendor/apexcharts/apexcharts.min.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/chart.js/chart.umd.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/echarts/echarts.min.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/quill/quill.min.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/simple-datatables/simple-datatables.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/tinymce/tinymce.min.js') }}"></script>

    <script src="{{ asset('public/admin/assets/vendor/php-email-form/validate.js') }}"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    <script src="//cdnjs.cloudflare.com/ajax/libs/timepicker/1.3.5/jquery.timepicker.min.js"></script>



    <!-- Template Main JS File -->

    <script src="{{ asset('public/admin/assets/js/main.js') }}"></script>

    <script>

        $(document).ready(function(){

      

         $('.timepicker').timepicker({

                timeFormat: 'h:mm p',

                interval: 30,

                minTime: '10',

                maxTime: '6:00pm',

                defaultTime: '11',

                startTime: '06:30',

                dynamic: true,

                dropdown: true,

                scrollbar: false

            });

            });

        </script>



    @stack('js')



    @if ($message = Session::get('error'))

    <script>

        toastr.error('{{ $message }}');

    </script>

    @endif

    @if ($message = Session::get('success'))

    <script>

        toastr.success('{{ $message }}');

    </script>

    @endif

</body>



</html>