@extends('backend.layouts.app')

@section('title', 'Admin | Venue Blocks')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Venue Blocks</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('banquet-venue-block.manage')
                    <a href="{{ route('admin.venue_block.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Venue Block</a>
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
                                        <th scope="col">From Date</th>
                                        <th scope="col">To Date</th>
                                        <th scope="col">Remark</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->venue->name ?? '' }}</td>
                                        <td>{{ $data->session->name ?? '' }}</td>
                                        <td>{{ date("d-m-Y", strtotime($data->from_date)) }}</td>
                                        <td>{{ date("d-m-Y", strtotime($data->to_date)) }}</td>
                                        <td>{{ $data->remark }}</td>
                                        <td>
                                            @can('banquet-venue-block.edit')
                                            <a href="{{ route('admin.venue_block.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Venue Block Edit">
                                                Edit 
                                            </a>
                                            @endcan
                                            
                                            @can('banquet-venue-block.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.venue_block.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Venue Block" onclick="return confirm('Are you sure?')">
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