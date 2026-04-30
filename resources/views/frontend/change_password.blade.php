@extends('frontend.layouts.app')

@section('title', 'Change Password')

@section('content')
<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Change Password</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">

        <div class="row">

            <div class="col-lg-3"></div>
            <div class="col-lg-6">
                <form action="{{ route('student.update.password') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="">Old Password <b class="text-danger">*</b> </label>
                        <input type="text" class="form-control" name="old_password" placeholder="Enter old password" required>
                    </div>
                    <div class="form-group">
                        <label for="">New Password <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" name="new_password" placeholder="Enter new password" required>
                    </div>
                    <div class="form-group">
                        <label for="">Confirm Password <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" name="confirm_password" placeholder="Confirm new password" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="mt-2">Change Password</button>
                    </div>
                </form>
            </div>
            
        </div>

    </div>

</div>

@endsection

@section('script')

@endsection