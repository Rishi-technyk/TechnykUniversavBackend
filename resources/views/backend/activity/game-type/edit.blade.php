@extends('backend.layouts.app')

@section('title', 'Admin | Update Game Type')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Game Type</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.game_types') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">
            
            <div class="card card-outline card-info">

                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <form action="{{ route('admin.game_type.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Label</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $data->name) }}" placeholder="Enter Label" required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>No. Of Players</label>
                                    <input type="number" name="no_of_players" class="form-control" value="{{ old('no_of_players', $data->no_of_players) }}" placeholder="Enter No. Of Players" required>
                                </div>
                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Game Type</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->

            </div>
            
        </div>

    </section>
    <!-- /.content -->
</div>

@endsection

@section('script')

@endsection