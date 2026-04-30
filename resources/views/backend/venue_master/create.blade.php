@extends('backend.layouts.app')

@section('title', 'Admin | Create Venue ')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Venue </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.venue_masters') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.venue_master.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Capacity <span class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control" placeholder="Enter Capacity" value="{{ old('capacity') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>GST % <span class="text-danger">*</span></label>
                                    <input type="number" name="gst" class="form-control" placeholder="Enter GST %" value="{{ old('gst') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Security Deposit <span class="text-danger">*</span></label>
                                    <input type="number" name="security_deposit" class="form-control" placeholder="Enter Security Deposit" value="{{ old('security_deposit') }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Venue</button>
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