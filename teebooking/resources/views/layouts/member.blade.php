<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>AEPTA | Member</title>
    <link rel="canonical" href="#pageurl" />
    <link rel="icon" href="favicon.png" sizes="16x16" type="icon/png" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/bootstrap.min.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/line-awesome.min.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/animate.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/slick.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/magnific-popup.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/flatpicker.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/intlTelInput.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/nice-select.css')}}" />
    <link rel="stylesheet" href="{{asset('public/member/assets/css/style.css')}}" />
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">
</head>

<body>
    <!-- Header -->
    @include('partials.member.header')
    <!-- .Header -->

    <div class="body-overlay"></div>

    <div class="dashboard-area section-bg-2 dashboard-padding">
        <div class="container">
            <div class="dashboard-contents-wrapper">
                <!-- Dashboard Sidebar -->
                {{-- @include('partials.member.sidebar') --}}
                @yield('sidebar')
                <!-- Dashboard Sidebar -->

                <!-- Dashboard main content -->
                @yield('content')
                <!-- .Dashboard main content -->
            </div>
        </div>
    </div>

    <!-- Footer -->
    @include('partials.member.footer')
    <!-- Footer -->

    <div class="back-to-top">
        <span class="back-top"> <i class="las la-angle-up"></i> </span>
    </div>
    <script src="{{asset('public/member/assets/js/jquery-3.6.0.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/jquery-migrate.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/wow.js')}}"></script>
    <script src="{{asset('public/member/assets/js/slick.js')}}"></script>
    <script src="{{asset('public/member/assets/js/imagesloaded.pkgd.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/isotope.pkgd.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/jquery.magnific-popup.js')}}"></script>
    <script src="{{asset('public/member/assets/js/jquery.nice-select.js')}}"></script>
    <script src="{{asset('public/member/assets/js/flatpicker.js')}}"></script>
    <script src="{{asset('public/member/assets/js/nouislider-8.5.1.min.js')}}"></script>
    <script src="{{asset('public/member/assets/js/intlTelInput.js')}}"></script>
    <script src="{{asset('public/member/assets/js/main.js')}}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

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

    <script>
        /* alert for confirm */
        $('a.confirm').confirm({
            icon: 'fa fa-question',
            theme: 'material',
            closeIcon: true,
            animation: 'scale',
            type: 'red',
            buttons: {
                cancel: function () {
                },
                Confirm: {
                    btnClass: 'btn-red',
                    action: function(){
                        location.href = this.$target.attr('href');
                    },
                }
            }
        });
    </script>
    @yield('script');


</body>

</html>