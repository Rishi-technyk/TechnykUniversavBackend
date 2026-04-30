@extends('frontend.layouts.app')

@section('title', 'Member Profile')

@section('content')
<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Member Profile</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">
        <div class="row">

            <div class="col-lg-3"></div>
            <div class="col-lg-6">
                <form action="{{ route('student.update.profile') }}" method="post">
                    @csrf
                    <div class="form-group">
                        <label for="">Name <b class="text-danger">*</b> </label>
                        <input type="text" class="form-control" value="{{ $member->DisplayName }}" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="">Email <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" value="{{ $member->Email }}" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="">Phone <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" value="{{ $member->Mobile }}" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="">Address <b class="text-danger">*</b></label>
                        <textarea name="address" id="" class="form-control">{{ $member->Address }}</textarea>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="mt-2">Update Profile</button>
                    </div>
                </form>
            </div>
            
        </div>
    </div>
</div>

@endsection

@section('script')

@endsection