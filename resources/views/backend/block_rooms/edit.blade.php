@extends('backend.layouts.app')

@section('title', 'Admin | Update Block Room ')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Block Room </h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.block_rooms') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.block_room.update', encrypt($data->id)) }}" method="post">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Room Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="room_category_id" required>
                                        <option value="">Select Room Category</option>
                                        @foreach($rooms as $room)
                                        <option value="{{ $room->id }}" {{ old('room_category_id', $data->room_category_id) == $room->id ? 'selected' : '' }}>{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">No. of Blocked Rooms <span class="text-danger">*</span></label>
                                    <input type="number" name="blocked_room" class="form-control" placeholder="Enter No. of Blocked Rooms" value="{{ old('blocked_room', $data->blocked_room) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">From Date <span class="text-danger">*</span></label>
                                    <input type="date" name="from_date" class="form-control" value="{{ old('from_date', date('Y-m-d', strtotime($data->from_date))) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">To Date <span class="text-danger">*</span></label>
                                    <input type="date" name="to_date" class="form-control" value="{{ old('to_date', date('Y-m-d', strtotime($data->to_date))) }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="name">Remark</label>
                                    <textarea name="remark" class="form-control" placeholder="Enter Remark">{{ old('remark', $data->remark) }}</textarea>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Block Room</button>
                        </div>
                    </form>
                </div>
                <!-- /.container-fluid -->
            </section>
            <!-- /.content -->
        </div>

@endsection

@section('script')

@endsection