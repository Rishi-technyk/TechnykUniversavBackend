@extends('backend.layouts.app')

@section('title', 'Admin | Settings')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Settings</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                @if($login_user->role == 'Room Admin' || $login_user->role == 'Banquet Admin')
                <div class="col-12">

                    <div class="card card-outline card-info">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="{{ route('admin.admin_setting.update') }}" method="post">
                                @csrf

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Minimum Days</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="number" name="min_days" value="{{ $data ? $data->min_days : '' }}" class="form-control">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Maximum Days</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="number" name="max_days" value="{{ $data ? $data->max_days : '' }}" class="form-control">
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-outline-info">Save</button>
                                </div>

                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                @endif

                @if($login_user->role == 'Super Admin')
                <div class="col-12">

                    <div class="card card-outline card-info">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="{{ route('admin.admin_setting.update') }}" method="post" enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Heading</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="heading" value="{{ $data ? $data->heading : '' }}" placeholder="Enter Invoice Heading" class="form-control">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Sub Heading</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="sub_heading" value="{{ $data ? $data->sub_heading : '' }}" class="form-control" placeholder="Enter Invoice Sub Heading">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Phone No.</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="phone" value="{{ $data ? $data->phone : '' }}" class="form-control" placeholder="Enter Phone No.">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Email</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="email" name="email" value="{{ $data ? $data->email : '' }}" class="form-control" placeholder="Enter Email">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Address</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="address" value="{{ $data ? $data->address : '' }}" class="form-control" placeholder="Enter Address">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Project Name</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="project_name" value="{{ $data ? $data->project_name : '' }}" class="form-control" placeholder="Enter Project Name">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Member Header Message</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="text" name="student_header_message" value="{{ $data ? $data->student_header_message : '' }}" class="form-control" placeholder="Enter Member Header Message">
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Room Booking Module</label>
                                    </div>
                                    <div class="col-md-9 mt-2">
                                        <input type="checkbox" name="room_booking_module" value="1" {{ $data && $data->room_booking_module=='Active' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Banquet Booking Module</label>
                                    </div>
                                    <div class="col-md-9 mt-2">
                                        <input type="checkbox" name="banquest_booking_form" value="1" {{ $data && $data->banquest_booking_form=='Active' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Table Booking Module</label>
                                    </div>
                                    <div class="col-md-9 mt-2">
                                        <input type="checkbox" name="table_booking_form" value="1" {{ $data && $data->table_booking_form=='Active' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Activity Booking Module</label>
                                    </div>
                                    <div class="col-md-9 mt-2">
                                        <input type="checkbox" name="activity_booking_form" value="1" {{ $data && $data->activity_booking_form=='Active' ? 'checked' : '' }}>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <label for="">Logo</label>
                                    </div>
                                    <div class="col-md-9">
                                        <input type="file" name="logo" class="form-control">
                                        @if($data->logo && file_exists(public_path($data->logo)))
                                        <a href="{{ asset($data->logo) }}" target="_blank"><img src="{{ asset($data->logo) }}" alt="Project Logo" class="mt-4" style="width: 100px; height: auto;"></a>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-outline-info">Save</button>
                                </div>

                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
                @endif
                
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

@endsection

@section('script')

@endsection