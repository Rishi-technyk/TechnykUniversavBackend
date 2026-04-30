@extends('backend.layouts.app')

@section('title', 'Admin | Game Types')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Game Types</h1>
                </div>
                <div class="col-sm-6 text-right">
              
                    <a href="{{ route('admin.game_type.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Game Type</a>
                   
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
                                        <th scope="col">Label</th>
                                        <th scope="col">No. Of Players</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->name }}</td>
                                        <td>{{ $data->no_of_players }}</td>
                                        <td>
                                         
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.game_type.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the game type?')" class="btn btn-outline-success btn-sm">Active</a>
                                                    @else
                                                    <a href="{{ route('admin.game_type.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the game type?')" class="btn btn-outline-danger btn-sm">Inactive</a>
                                                @endif
                                        </td>
                                        <td>
                                    
                                            <a href="{{ route('admin.game_type.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Game Type Edit">
                                                Edit 
                                            </a>
                                            
                                            &nbsp;
                                            <a href="{{ route('admin.game_type.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Game Type" onclick="return confirm('Are you sure?')">
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