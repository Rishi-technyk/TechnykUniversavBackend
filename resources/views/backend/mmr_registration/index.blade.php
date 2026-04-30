@extends('backend.layouts.app')

@section('title', 'Admin | MMR Registration')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>MMR Registration</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <div class="col-12">

                    <div class="card card-outline card-info">
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="{{ route('admin.mmr_registration.update') }}" method="post">
                                @csrf

                                <div class="row">
                                    <div class="col-md-3">
                                        <label for="">Start Date <b class="text-danger">*</b> </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="datetime-local" name="start_date" value="{{ $data ? $data->start_date : '' }}" placeholder="Enter Start Date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-md-3">
                                        <label for="">End Date <b class="text-danger">*</b> </label>
                                    </div>
                                    <div class="col-md-4">
                                        <input type="datetime-local" name="end_date" value="{{ $data ? $data->end_date : '' }}" placeholder="Enter End Date" class="form-control" required>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="submit" class="btn btn-outline-info">Update</button>
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

@endsection