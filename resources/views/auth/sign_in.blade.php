<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" href="{{ asset('frontend/images/favicon.png') }}">
    <title>{{ $setting->project_name }} | Sign In</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <!-- Icons -->
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="https://demos.creative-tim.com/argon-dashboard-pro/assets/css/nucleo-svg.css" rel="stylesheet" />

    <!-- Argon CSS -->
    <link href="{{ asset('auth/css/argon-dashboard.css?v=2.1.0') }}" rel="stylesheet" />

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

    <!-- Toastr -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>
<style>
    .login-image{
        background-image:url('{{ asset('auth/img/WhatsApp Image 2026-03-07 at 13.15.33.jpeg') }}');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh;
    }
</style>
<body class="bg-white">

    <main class="main-content mt-0">
        <section>
            <div class="container-fluid">
                <div class="row min-vh-100">

                    <!-- ================= LEFT PURPLE SECTION ================= -->
                    <div class="col-lg-6 d-lg-flex d-none">
                        <div class="login-image w-100"></div>
                    </div>

                    <!-- ================= RIGHT LOGIN FORM ================= -->
                    <div class="col-xl-4 col-lg-5 col-md-7 col-12 d-flex flex-column justify-content-center mx-auto">
                        <div class="card card-plain">

                            {{-- Validation Errors --}}
                            @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-lg-12 text-center">
                                    <img src="{{ asset($setting->logo) }}" alt="">
                                </div>
                                <div class="col-lg-12 text-center mt-2">
                                    <h4 class="font-weight-bolder">{{ $setting->project_name }}</h4>
                                </div>
                            </div>

                            <div class="card-header pb-0 text-center">
                                <h4 class="font-weight-bolder">Sign In</h4>
                                <p class="mb-0">Enter your email and password to sign in</p>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('authentication') }}" method="POST">
                                    @csrf

                                    <div class="mb-3">
                                        <input type="email" name="email" class="form-control form-control-lg" placeholder="Email" required>
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Password" required>
                                        <i class="fa fa-eye position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" style="cursor:pointer"></i>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-lg btn-primary w-100 mt-3">
                                            Sign In
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>

    <!-- ================= JS FILES ================= -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ asset('auth/js/core/bootstrap.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <!-- Toastr Config -->
    <script>
    toastr.options = {
        closeButton: true,
        progressBar: true,
        timeOut: 5000
    };
    </script>

    <!-- Toastr Messages -->
    <script>
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
    </script>

    <!-- Password Toggle -->
    <script>
    $(document).on('click', '.toggle-password', function() {
        let input = $('#password');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            $(this).removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            $(this).removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });
    </script>

</body>

</html>