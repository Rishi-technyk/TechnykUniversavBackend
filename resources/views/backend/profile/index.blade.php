@extends('backend.layouts.app')

@section('title', 'Admin | Update Profile')

@section('style')
<style>
    .toggle-password {
        float: right;
        margin-top: -9%;
        margin-right: 3%;

    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Profile</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <div class="col-12">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="card card-outline card-info">
                        
                        <!-- /.card-header -->
                        <div class="card-body">
                            
                            <form action="{{ route('update.profile') }}" method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-lg-6">
                                        <label for="">Name <b class="text-danger">*</b></label>
                                        <input type="text" name="name" class="form-control" value="{{ $user?$user->name:'' }}" placeholder="Enter Name" required> 
                                    </div>
                                    <div class="col-lg-6">
                                        <label for="">Email <b class="text-danger">*</b></label>
                                        <input type="email" name="email" class="form-control" value="{{ $user?$user->email:'' }}" placeholder="Enter Email" required> 
                                        <input type="hidden" name="id" value="{{ $user->id }}">
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn-sm btn btn-info">Update</button>
                                </div>
                            </form>

                        </div>
                        <!-- /.card-body -->
                    </div>

                    <div class="card card-outline card-info">
                        
                        <!-- /.card-header -->
                        <div class="card-body">
                            
                            <form action="{{ route('change.password') }}" method="post">
                            @csrf

                                <div class="row">
                                    <div class="col-lg-4">
                                        <label for="old_password">Old Password <span class="text-danger">*</span></label>
                                        <div class="password-wrapper">
                                            <input type="password" name="old_password" id="old_password" class="form-control" placeholder="Enter Old Password" required>
                                            <i class="fa fa-eye toggle-password" toggle="#old_password"></i>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="new_password">New Password <span class="text-danger">*</span></label>
                                        <div class="password-wrapper">
                                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Enter New Password" required>
                                            <i class="fa fa-eye toggle-password" toggle="#new_password"></i>
                                        </div>
                                    </div>

                                    <div class="col-lg-4">
                                        <label for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                        <div class="password-wrapper">
                                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Enter Confirm Password" required>
                                            <i class="fa fa-eye toggle-password" toggle="#confirm_password"></i>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn-sm btn btn-info">Update</button>
                                </div>
                            </form>

                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

@endsection

@section('script')
<script>
    $(document).on('click', '.toggle-password', function () {
        let input = $($(this).attr("toggle"));
        let icon = $(this);
        if (input.attr("type") === "password") {
            input.attr("type", "text");
            icon.removeClass("fa-eye").addClass("fa-eye-slash");
        } else {
            input.attr("type", "password");
            icon.removeClass("fa-eye-slash").addClass("fa-eye");
        }
    });
</script>
@endsection