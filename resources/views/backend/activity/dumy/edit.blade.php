@extends('backend.layouts.app')

@section('title', 'Admin | Update Function')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Function</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.functions') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.function.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name', $data->name) }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Description <span class="text-danger">*</span></label>
                                    <input type="text" name="description" class="form-control" placeholder="Enter Description" value="{{ old('description', $data->description) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Function</button>
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