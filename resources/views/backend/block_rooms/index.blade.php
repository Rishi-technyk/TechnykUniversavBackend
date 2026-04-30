@extends('backend.layouts.app')

@section('title', 'Admin | Block Room')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Block Room List</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('room-block.create')
                    <a href="{{ route('admin.block_room.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Block Room</a>
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
                                        <th>Category</th>
                                        <th>No Of Rooms</th>
                                        <th>From Date</th>
                                        <th>To Date</th>
                                        <th>Remark</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->room_category->name ?? '' }}</td>
                                        <td>{{ $data->blocked_room }}</td>
                                        <td>{{ date("d-m-Y", strtotime($data->from_date)) }}</td>
                                        <td>{{ date("d-m-Y", strtotime($data->to_date)) }}</td>
                                        <td>{{ $data->remark }}</td>
                                        <td>
                                            @can('room-block.edit')
                                            <a href="{{ route('admin.block_room.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Block Room Edit">
                                                Edit 
                                            </a>
                                            @endcan

                                            @can('room-block.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.block_room.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Block Room" onclick="return confirm('Are you sure?')">
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