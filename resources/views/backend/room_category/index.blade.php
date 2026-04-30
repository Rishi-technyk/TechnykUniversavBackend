@extends('backend.layouts.app')

@section('title', 'Admin | Room Category')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Room Category List</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('room-category.manage')
                    <a href="{{ route('admin.room_category.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Room Category</a>
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
                                        <th>Name</th>
                                        <th>GST (%)</th>
                                        <th>No Of Rooms</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->GST }}{{ $data->GST ? "%" : ""}}</td>
                                        <td>{{ $data->no_of_rooms }}</td>
                                        <td>
                                            @can('room-category.status')
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.room_category.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the room category?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.room_category.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the room category?')" class="btn btn-outline-danger btn-sm">Inactive</a>
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
                                            @can('room-category.edit')
                                            <a href="{{ route('admin.room_category.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Room Category Edit">
                                                Edit 
                                            </a>
                                            @endcan

                                            @can('room-category.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.room_category.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Room Category" onclick="return confirm('Are you sure?')">
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