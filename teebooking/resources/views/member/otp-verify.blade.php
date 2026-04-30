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
                    <img src="public/member/assets/img/dsoi-logo-name.png" alt="dsoi-logo" style="height: 50px"
                        class="mb-4" />
                    <h2 class="single-title">Password change</h2>

                    @if ($message = Session::get('error'))
                    <p class="text-danger pt-4">{{ $message }}</p>
                    @endif
                    <form action="{{ route('otp-verify') }}" method="post"
                        class="login-wrapper-contents-form custom-form">
                        @csrf
                        <input type="hidden" name="member_id" value="{{$member->MemberID}}"/>
                        <input type="hidden" name="email" value="{{$member->Email}}"/>
                        <div class="single-input mt-4">
                            <input name="otp" class="form--control" type="text" placeholder="Enter OTP" />
                            @if($errors->has('otp'))
                            <div class="text-danger">
                                {{ $errors->first('otp') }}
                            </div>
                            @endif
                        </div>
                        <div class="single-input mt-4">
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
                            <input name="password_confirmation" type="password" class="form--control"
                                placeholder="Enter Confirm Password" />
                            <div class="icon toggle-password">
                                <div class="show-icon">
                                    <i class="las la-eye-slash"></i>
                                </div>
                                <span class="hide-icon"> <i class="las la-eye"></i> </span>
                            </div>
                            @if($errors->has('password_confirmation'))
                            <div class="text-danger fw-light">
                                {{ $errors->first('password_confirmation') }}
                            </div>
                            @endif
                        </div>


                        <button class="submit-btn w-100 mt-4" type="submit">

                            Login

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</section>

@endsection



@section('script')

<script>
$(document).ready(function() {
    // Intercept the form submission
    $('form').submit(function(e) {
        e.preventDefault();

        // Serialize form data
        var formData = $(this).serialize();

        // Make an AJAX request
        $.ajax({
            type: 'POST',
            url: '{{ route("otp-verify") }}',
            data: formData,
            success: function(response) {
                // Handle success response (if needed)
                console.log(response.success);
                toastr.success(response.success);
                window.location.href='/login';
            },
            error: function(xhr) {
              
                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        toastr.error(value);
                    });
                } else if (xhr.status === 400) {

                    toastr.error(xhr.responseJSON.error);

                } else {
                    toastr.error('An error occurred. Please try again.');
                }
                $('#password-confirmation-error').html('');
            }
        });
    });

});
</script>

@endsection