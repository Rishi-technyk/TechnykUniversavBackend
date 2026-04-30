@extends('backend.layouts.app')

@section('title', 'Admin | Update Slot')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Slot</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.slots') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.slot.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Slot Value <small class="text-muted">(Exp:- 1030-1100)</small> <b class="text-danger">*</b> </label>
                                    <input type="text" name="value" class="form-control" value="{{ old('value', $data->name) }}" placeholder="Enter Value (Exp:- 1030-1100)" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Slot Label <b class="text-danger">*</b> </label>
                                    <input type="text" name="label" class="form-control" value="{{ old('label', $data->label) }}" placeholder="Enter Label" required>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Slot</button>
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