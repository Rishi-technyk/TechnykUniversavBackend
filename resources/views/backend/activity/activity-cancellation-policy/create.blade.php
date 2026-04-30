@extends('backend.layouts.app')

@section('title', 'Admin | Create Cancellation Policy')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Cancellation Policy</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.activity_cancellation_policies') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.activity_cancellation_policy.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                    
                                <div class="form-group">
                                    
                                    <label>Facility <b class="text-danger">*</b> </label>
                                    <select class="form-control" name="facility_id" required>
                                        <option value="">Select Facility</option>
                                        @foreach($facility as $faci)
                                        <option value="{{ $faci->id }}">{{ $faci->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>
                        
                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>From Days <b class="text-danger">*</b></label>
                                    <input type="number" name="from_days" class="form-control" placeholder="Enter From Days" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>To Days <b class="text-danger">*</b></label>
                                    <input type="number" name="to_days" class="form-control" placeholder="Enter To Days" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Deduction <b class="text-danger">*</b></label>
                                    <input type="number" name="deduction" class="form-control" placeholder="Enter Deduction" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>GST (%) <b class="text-danger">*</b></label>
                                    <input type="number" name="GST" class="form-control" placeholder="Enter GST in Percentage" required>

                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Cancellation Policy</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->

            </div>
            
        </div>

    </section>
    <!-- /.content -->
</div>

@endsection

@section('script')

@endsection