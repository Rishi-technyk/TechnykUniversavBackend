@extends('backend.layouts.app')

@section('title', 'Admin | Room Charges')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Room Charges List</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('room-charges.create')
                    <a href="{{ route('admin.room_charge.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Room Charge</a>
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
                                        <th>Room</th>
                                        <th>Category</th>
                                        <th>Category Type</th>
                                        <th>Occupant</th>
                                        <th>Charges</th>
                                        <th>No. Of Rooms</th>
                                        <th>Max. Night</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->room_category?$data->room_category->name:'NA' }}</td>
                                        <td>{{ $data->category?$data->category->Catg_Name:'NA' }}</td>
                                        <td>{{ $data->categoryType?$data->categoryType->CategoryType:'NA' }}</td>
                                        <td>{{ $data->occupant?$data->occupant->name:'NA' }}</td>
                                        <td>{{ format_price($data->charges_nite) }}</td>
                                        <td>{{ $data->no_of_booked_room }}</td>
                                        <td>{{ $data->max_no_of_nites }}</td>
                                        <td>
                                            @can('room-charges.status')
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.room_charge.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the room charges?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.room_charge.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the room charges?')" class="btn btn-outline-danger btn-sm">Inactive</a>
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
                                            @can('room-charges.edit')
                                            <a href="{{ route('admin.room_charge.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Room Charges Edit">
                                                Edit 
                                            </a>
                                            @endcan

                                            @can('room-charges.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.room_charge.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Room Charges" onclick="return confirm('Are you sure?')">
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