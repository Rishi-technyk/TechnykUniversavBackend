@extends('backend.layouts.app')

@section('title', 'Admin | Block Slot')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Block Slot</h1>
                </div>
                <div class="col-sm-6 text-right">
                    
                    <a href="{{ route('admin.block_slot.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Block Slot</a>
                   
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
                                        <th scope="col">Slot</th>
                                        <th scope="col">Facility</th>
                                        <th scope="col">Date</th>
                                        <th scope="col">Remark</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ ++$key }}</td>
                                        <td>{{ $data->facility->name ?? '' }}</td>
                                        <td>{{ $data->slot->label ?? '' }}</td>
                                        <td>{{ date("d-m-Y", strtotime($data->date)) }}</td>
                                        <td>{{ $data->remark }}</td>
                                        <td>
                                            
                                            <a href="{{ route('admin.block_slot.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Edit block slot">
                                                Edit 
                                            </a>
                                            
                                            &nbsp;
                                            <a href="{{ route('admin.block_slot.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete block slot" onclick="return confirm('Are you sure?')">
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