@extends('layouts.web')
@section('content')
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Change Password</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/avatar.png') }}" alt="avatar"
                            class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{auth()->user()->DisplayName}}</h5>
                        <p class="text-muted mb-3"> {{auth()->user()->Email}}</p>
                        <div class="btn-wrapper mt-1">
                            <a class="cmn-btn btn-bg-1" href="{{route('member_edit')}}"> Edit </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card mb-4  h-100">
                    <div class="card-body">
                        <div class="single-reservation bg-white base-padding">
                            <h3 class="single-reservation-title"></h3>
                            <div class="custom--form dashboard-form mt-5">
                                <form id="changePasswordFrom" action="{{ route('changePassword') }}" method="post">
                                    @csrf
                                    <div class="dashboard-input mt-1">

                                        <input type="password" name="current_password" class="form--control"
                                            placeholder="Current Password" required />
                                        <div class="toggle-password">
                                            <span class="eye-icon"> </span>
                                        </div>
                                    </div>
                                    <div class="dashboard-input mt-1">

                                        <input type="password" name="password" class="form--control"
                                            placeholder="New Password" required />
                                        <div class="toggle-password">
                                            <span class="eye-icon"> </span>
                                        </div>
                                    </div>
                                    <div class="dashboard-input mt-1 mb-2">

                                        <input type="password" name="password_confirmation" class="form--control"
                                            placeholder="Confirm Password" required />
                                        <div class="toggle-password">
                                            <span class="eye-icon"> </span>
                                        </div>
                                    </div>

                                    <div class="btn-wrapper mt-1">
                                        <button type="submit" class="cmn-btn btn-bg-1">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->

@push('js')
<script>
$(document).ready(function() {
    $("#changePasswordFrom").submit(function(event) {
        console.log('id');
        $.ajax({
            url: 'change-password',
            type: 'POST',
            data: $('#changePasswordFrom').serialize(),
            success: function(response) {
                toastr.success(response.success);
                $('#changePasswordFrom').trigger("reset");
            },
            error: function(errors) {
                toastr.error('Somthing went wrong.');
            }
        });
        event.preventDefault();
    });
});
</script>

@endpush()
@endsection