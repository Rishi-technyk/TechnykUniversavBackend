@extends('backend.layouts.app')

@section('title', 'Admin | Update Table')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Table</h1>   
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.tables') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.table.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Meal <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="meal_id" required>
                                        <option value="">Select Meal</option>
                                        @foreach($meals as $meal)
                                        <option value="{{ $meal->id }}" {{ old('meal_id', $data->meal_id) == $meal->id ? 'selected' : '' }}>{{ $meal->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name', $data->name) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Table</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>

    </section>

</div>


@endsection

@section('script')

@endsection