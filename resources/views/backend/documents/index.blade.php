@extends('backend.layouts.app')

@section('title', 'Admin | Documents')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Documents</h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('document.create')
                    <a href="{{ route('admin.document.create') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-plus"></i> &nbsp;Create Document</a>
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
                                        <th scope="col">Label</th>
                                        <th scope="col">File</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $data->label }}</td>
                                        <td>
                                            @if ($data->file_path && File::exists(public_path($data->file_path)))
                                                <a href="{{ asset($data->file_path) }}" target="_blank" title="View File" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-file-pdf"></i></a>
                                            @endif
                                        </td>
                                        <td>
                                            @can('document.status')
                                                @if($data->status == "Active")
                                                    <a href="{{ route('admin.document.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to deactivate the document?')" class="btn btn-outline-success btn-sm">Active</a>
                                                @else
                                                    <a href="{{ route('admin.document.status', encrypt($data->id)) }}" onclick="return confirm('Do you want to activate the document?')" class="btn btn-outline-danger btn-sm">Inactive</a>
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
                                            @can('document.edit')
                                            <a href="{{ route('admin.document.edit', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Document Edit">
                                                Edit 
                                            </a>
                                            @endcan

                                            @can('document.delete')
                                            &nbsp;
                                            <a href="{{ route('admin.document.delete', encrypt($data->id)) }}" class="btn btn-outline-danger btn-sm" data-toggle="tooltip" data-original-title="Delete Document" onclick="return confirm('Are you sure?')">
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