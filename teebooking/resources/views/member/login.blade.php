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
                        <h2 class="single-title">Member login</h2>
                        <p class="sigle-para mt-2">Login to Continue</p>
                        @if ($message = Session::get('error'))
                            <p class="text-danger pt-4">{{ $message }}</p>
                        @endif
                        <form action="{{ route('login.admin') }}" method="post" class="login-wrapper-contents-form custom-form">
                            @csrf
                            <div class="single-input mt-4">
                                <label class="label-title mb-3">Membership No. </label>
                                <input name="username" class="form--control" type="text"
                                    placeholder="Enter Membership No." />
                                    @if($errors->has('username'))
                                        <div class="text-danger">
                                            {{ $errors->first('username') }}
                                        </div> 
                                    @endif
                            </div>
                            <div class="single-input mt-4">
                                <label class="label-title mb-3">Password</label>
                                <input name="password" type="password" class="form--control" placeholder="Enter Password" />
                                <div class="icon toggle-password">
                                    <div class="show-icon">
                                        <i class="las la-eye-slash"></i>
                                    </div>
                                    <span class="hide-icon"> <i class="las la-eye"></i> </span>
                                </div>
                                @if($errors->has('password'))
                                <div class="text-danger fw-light">
                                    {{ $errors->first('password') }}
                                </div>
                                @endif
                            </div>
                            <div class="single-input mt-4">
                                <div class="captcha" id="captcha">
                                    <span style="min-width: 200px">{!! captcha_img() !!}</span>
                                    <button type="button" class="submit-btn" class="reload" id="reload" >
                                        &#x21bb;
                                    </button>
                                </div>
                            </div>
                            <div class="single-input mt-4">
                                <input name="captcha" class="form--control" type="text" placeholder="Enter Captcha text " />
                                @if($errors->has('captcha'))
                                    <div class="text-danger">
                                        {{ $errors->first('captcha') }}
                                    </div>
                                @endif
                            </div>
                            <button class="submit-btn w-100 mt-4" type="submit">

                                Login

                            </button>

                        </form>

                        <div class="single-checkbox mt-3">

                            <div class="forgot-password">

                                <a href="{{route('forgot_password')}}" class="forgot-btn color-one">

                                    Forgot Password?

                                </a>

                            </div>

                        </div>

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



