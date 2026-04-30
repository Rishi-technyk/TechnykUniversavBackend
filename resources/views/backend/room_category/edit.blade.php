@extends('backend.layouts.app')

@section('title', 'Admin | Update Room Category ')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Room Category </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.room_categories') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">
            
            <div class="card card-outline card-info">

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('admin.room_category.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ $data->name }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Number of Room <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_rooms" class="form-control" placeholder="Enter Number of Room" value="{{ $data->no_of_rooms }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">GST (%) <span class="text-danger">*</span></label>
                                    <input type="number" name="gst" class="form-control" placeholder="Enter GST %" value="{{ $data->GST }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Size <span class="text-danger">*</span></label>
                                    <input type="text" name="size" class="form-control" placeholder="Enter Room Size" value="{{ old('size', $data->size) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Capacity <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control" placeholder="Enter Room Capacity" value="{{ old('capacity', $data->capacity) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Bed Type <span class="text-danger">*</span></label>
                                    <input type="text" name="bed_type" class="form-control" placeholder="Enter Bed Type" value="{{ old('bed_type', $data->bed_type) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Services <span class="text-danger">*</span></label>
                                    <input type="text" name="services" class="form-control" placeholder="Enter Services Like Wifi, Television, Bathroom etc" value="{{ old('services', $data->services) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Room Image <span class="text-danger">*</span></label>
                                    <input type="file" name="room_image" class="form-control">
                                </div>
                                @if($data->room_image && file_exists(public_path($data->room_image)))
                                <a href="{{ asset($data->room_image) }}" target="_blank"><img src="{{ asset($data->room_image) }}" alt="Room Image" style="width: 100px; height: auto;"></a>
                                @endif
                            </div>
                            <!-- /.col -->

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Description</label>
                                    <textarea name="description" class="form-control" placeholder="Enter Description">{{ $data->description }}</textarea>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Room Category</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>

@endsection

@section('script')

@endsection