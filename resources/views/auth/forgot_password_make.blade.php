<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" href="{{ asset('frontend/images/favicon.png') }}">
    <title>{{ $setting->project_name }} | Forgot Password</title>

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
    .btn-lg {
        background-color: #2e4374 !important;
    }
    
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
                                <h4 class="font-weight-bolder">Forgot Password</h4>
                                <p class="mb-0">Enter your username to receive an OTP</p>
                            </div>

                            <div class="card-body">
                                <form action="{{ route('update.forgot.password') }}" method="POST" class="signin-form" autocomplete="off">
                                    @csrf

                                    <div class="mb-3">
                                        <input type="text" name="username" value="{{ Session::get('username') }}" class="form-control form-control-lg" placeholder="Username" required readonly>
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <input type="password" name="password" id="password" class="form-control form-control-lg" placeholder="Password" required>
                                        <i class="fa fa-eye position-absolute top-50 end-0 translate-middle-y me-3 toggle-password" style="cursor:pointer"></i>
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <input type="password" name="password_confirmation" id="confirm_password" class="form-control form-control-lg" placeholder="Confirm Password" required>
                                        <i class="fa fa-eye position-absolute top-50 end-0 translate-middle-y me-3 toggle-passwordd" style="cursor:pointer"></i>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-lg btn-primary w-100 mt-3">
                                            Submit
                                        </button>
                                        <div class="row">
                                            <div class="col-6 text-start">
                                                <a href="{{ route('sign-up') }}" class="text-sm text-secondary">Sign Up</a>
                                            </div>
                                            <div class="col-6 text-end">
                                                <a href="{{ route('student.forgot.password') }}" class="text-sm text-secondary">Forgot password?</a>   
                                            </div>
                                        </div>
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

    <script>
        $(document).on('click', '.toggle-passwordd', function() {
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