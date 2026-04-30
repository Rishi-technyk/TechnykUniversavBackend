@extends('backend.layouts.app')

@section('title', 'Admin | Cancellation Policy')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Cancellation Policy</h1>
                </div>
                <div class="col-sm-6 text-right">
              
                    <a href="{{ route('admin.activity_cancellation_policy.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Cancellation Policy</a>
                   
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
                                        <th scope="col">Facility</th>
                                        <th scope="col">From Days</th>
                                        <th scope="col">To Days</th>
                                        <th scope="col">Deduction</th>
                                        <th scope="col">GST %</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->facility->name ?? '' }}</td>
                                        <td>{{ $data->from_days }}</td>
                                        <td>{{ $data->to_days }}</td>
                                        <td>{{ $data->deduction }}</td>
                                        <td>{{ $data->GST ?? '0' }}%</td>
                                        <td>
                                    
                                            <a href="{{ route('admin.activity_cancellation_policy.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Cancellation Policy Edit">
                                                Edit 
                                            </a>
                                            
                                            &nbsp;
                                            <a href="{{ route('admin.activity_cancellation_policy.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Cancellation Policy" onclick="return confirm('Are you sure?')">
                                                Delete
                                            </a>
                                        
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
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