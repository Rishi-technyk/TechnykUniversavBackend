@extends('backend.layouts.app')

@section('title', 'Admin | Create Venue Block')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Venue Block</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.venue_blocks') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.venue_block.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Venue Master <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="venue_id" required>
                                        <option value="">Select Venue Master</option>
                                        @foreach($venue as $ven)
                                        <option value="{{ $ven->id }}" {{ old('venue_id') == $ven->id ? 'selected' : '' }}>{{ $ven->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Session <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="session_id" required>
                                        <option value="">Select Session</option>
                                        @foreach($session as $ses)
                                        <option value="{{ $ses->id }}" {{ old('session_id') == $ses->id ? 'selected' : '' }}>{{ $ses->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>From Date <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" class="form-control" placeholder="Enter From Date" value="{{ old('from_date') }}" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>To Date <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" class="form-control" placeholder="Enter To Date" value="{{ old('to_date') }}" required>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Remark</label>
                                    <textarea class="form-control" name="remark" placeholder="Enter Remark">{{ old('remark') }}</textarea>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Venue Block</button>
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