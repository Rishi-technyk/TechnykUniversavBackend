@extends('backend.layouts.app')

@section('title', 'Admin | Occupant Master')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Occupant Masters</h1>
                </div>
                <div class="col-sm-6 text-right">
                    
                    <a href="{{ route('admin.activity_occupant_master.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Occupant</a>
                   
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
                                        <th>Charge</th>
                                        <th>Additional Info</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->charge }}</td>
                                        <td>{{ $data->additional_info }}</td>
                                        <td>
                                           
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.activity_occupant_master.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the occupant?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.activity_occupant_master.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the occupant?')" class="btn btn-outline-danger btn-sm">Inactive</a>
                                                @endif
                                            
                                        </td>
                                        <td>
                                                <a href="{{ route('admin.activity_occupant_master.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Occupant Edit">
                                                    Edit 
                                                </a>
                                            
                                                &nbsp;
                                                <a href="{{ route('admin.activity_occupant_master.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Occupant" onclick="return confirm('Are you sure?')">
                                                    Delete
                                                </a>
                                            
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