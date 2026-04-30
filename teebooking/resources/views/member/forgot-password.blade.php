@extends('layouts.member-auth')

@section('content')
    <section class="login-area padding-top-100 padding-bottom-100">
        <div class="container">
            <div class="login-wrapper  bg-white">
                <div class="login-wrapper-flex">
                    <!-- <div class="login-wrapper-thumb">
                        <img src="public/member/assets/img/login_bg.jpg" alt="img" />
                    </div> -->
                    <div class="login-wrapper-contents login-padding login-shadow">
                        <img src="public/member/assets/img/dsoi-logo-name.png" alt="dsoi-logo" style="height: 50px" class="mb-4" />
                        <h2 class="single-title">Forgot Password</h2>
                      
                        @if ($message = Session::get('error'))
                            <p class="text-danger pt-4">{{ $message }}</p>
                        @endif
                        <form action="{{ route('otp_send') }}" method="post" class="login-wrapper-contents-form custom-form">
                            @csrf
                            <div class="single-input mt-4">
                                <label class="label-title mb-3">Email </label>
                                <input name="email" class="form--control" type="email"
                                    placeholder="Enter Email" required />
                                  
                            </div>
                            <button class="submit-btn w-100 mt-4" type="submit">

                                Send

                            </button>

                        </form>

                       

                    </div>

                </div>

            </div>

        </div>

    </section>

@endsection



@section('script')

    <script type="text/javascript">

        $('#reload').click(function () {

            $.ajax({

                type: 'GET',

                url:'reload-captcha',

                success: function (data) {

                    $(".captcha span").html(data.captcha);

                }

            });

        });

    </script>

@endsection



