@extends('backend.layouts.app')

@section('title', 'Admin | Create Document')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Document </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.documents') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.document.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File Label <span class="text-danger">*</span></label>
                                    <input type="text" name="label" class="form-control" placeholder="Enter Label" value="{{ old('label') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>File <span class="text-danger">*</span></label>
                                    <input type="file" name="file" class="form-control" accept="application/pdf" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Document</button>
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