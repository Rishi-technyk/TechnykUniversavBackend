@extends('backend.layouts.app')

@section('title', 'Admin | Venue Charges')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Venue Charges</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('banquet-venue-charge.create')
                    <a href="{{ route('admin.venue_charge.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Venue Charge</a>
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
                                        <th scope="col">Venue</th>
                                        <th scope="col">Session</th>
                                        <th scope="col">Occupant</th>
                                        <th scope="col">Rate</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->venue->name ?? '' }}</td>
                                        <td>{{ $data->session?$data->session->name:'' }}</td>
                                        <td>{{ $data->occupant->name ?? '' }}</td>
                                        <td>{{ format_price($data->rate, 2) }}</td>
                                        <td>
                                            @can('banquet-venue-charge.edit')
                                            <a href="{{ route('admin.venue_charge.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Venue Edit">
                                                Edit 
                                            </a>
                                            @endcan

                                            @can('banquet-venue-charge.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.venue_charge.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Venue" onclick="return confirm('Are you sure?')">
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