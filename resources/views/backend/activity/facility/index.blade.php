@extends('backend.layouts.app')

@section('title', 'Admin | Facility')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Facility</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.facility.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Facility</a>
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
                                        <th scope="col">#</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Inventory</th>
                                        <th scope="col">Charge</th>
                                        <th scope="col">GST</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->inventory }}</td>
                                        <td>{{ $data->charge }}</td>
                                        <td>{{ $data->GSTper }}%</td>
                                        <td>
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.facility.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the facility?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.facility.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the facility?')" class="btn btn-outline-danger btn-sm">Inactive</a>
                                                @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.facility.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Facility Edit">
                                                Edit 
                                            </a>

                                            &nbsp;
                                            <a href="{{ route('admin.facility.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Facility" onclick="return confirm('Are you sure?')">
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