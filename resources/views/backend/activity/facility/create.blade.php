@extends('backend.layouts.app')

@section('title', 'Admin | Create Facility')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Facility</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.facilities') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.facility.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">
                            
                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Name <b class="text-danger">*</b> </label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Enter Name" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Inventory <b class="text-danger">*</b></label>
                                    <input type="number" name="inventory" class="form-control" value="{{ old('inventory') }}" placeholder="Enter Inventory" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Charge <b class="text-danger">*</b></label>
                                    <input type="number" name="charge" class="form-control" value="{{ old('charge') }}" placeholder="Enter Charge" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>GST (%) <b class="text-danger">*</b></label>
                                    <input type="number" name="gst" class="form-control" value="{{ old('gst') }}" placeholder="Enter GST" required>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Image <b class="text-danger">*</b></label>
                                    <input type="file" name="image_1" class="form-control" required>

                                </div>

                            </div>

                            <div class="col-lg-12">
                                
                                <div class="form-group">
                                    
                                    <label>Description</label>
                                    <textarea name="description" id="summernote" class="form-control" placeholder="Enter Description">{{ old('description') }}</textarea>

                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Facility</button>
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