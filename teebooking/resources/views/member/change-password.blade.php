@extends('layouts.web')
@section('content')
<style>
.border-left {
    border-left: 6px solid #fe0100;
}
</style>

{{-- .sidebar --}}



<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Member Profile</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                </div>
                <div class="col-lg-6">
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="single-reservation bg-white base-padding">
                                <h3 class="single-reservation-title">Change Password</h3>
                                <div class="custom--form dashboard-form">
                                    <form id="changePasswordFrom" action="{{ route('changePassword') }}" method="post">
                                        @csrf
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> Current Password </label>
                                            <input type="password" name="current_password" class="form--control"
                                                placeholder="Current Password" required />
                                            <div class="toggle-password">
                                                <span class="eye-icon"> </span>
                                            </div>
                                        </div>
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> New Password </label>
                                            <input type="password" name="password" class="form--control"
                                                placeholder="New Password" required />
                                            <div class="toggle-password">
                                                <span class="eye-icon"> </span>
                                            </div>
                                        </div>
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> Confirm Password </label>
                                            <input type="password" name="password_confirmation" class="form--control"
                                                placeholder="Confirm Password" required />
                                            <div class="toggle-password">
                                                <span class="eye-icon"> </span>
                                            </div>
                                        </div>

                                        <div class="btn-wrapper mt-4">
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