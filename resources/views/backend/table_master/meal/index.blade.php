@extends('backend.layouts.app')

@section('title', 'Admin | Meal')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Meal Masters</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('table-meal.create')
                    <a href="{{ route('admin.table_meal.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Meal</a>
                    @endcan
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
                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>
                                            @can('table-meal.status')
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.table_meal.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the meal?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.table_meal.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the meal?')" class="btn btn-outline-danger btn-sm">Inactive</a>
                                                @endif
                                            @else
                                                @if($data->status == "Active")
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            @endcan
                                        </td>
                                        <td>
                                            @can('table-meal.edit')
                                            <a href="{{ route('admin.table_meal.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Meal Edit">
                                                Edit 
                                            </a>
                                            @endcan
                                            
                                            @can('table-meal.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.table_meal.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Meal" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </a>
                                            @endcan
                                        </td>
                                    </tr>
                                    @endforeach
                            </table>
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