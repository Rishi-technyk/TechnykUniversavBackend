@extends('backend.layouts.app')

@section('title', 'Admin | Create Room Charges ')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Room Charges </h1>
                </div>
                <div class="col-sm-6 text-right">
                    @can('room-charges.manage')
                    <a href="{{ route('admin.room_charges') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
                    @endcan
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
                    <form action="{{ route('admin.room_charge.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name">Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach($catgeory as $cate)
                                        <option value="{{ $cate->Catg_Code }}" {{ old('category_id') == $cate->Catg_Code ? 'selected' : '' }}>{{ $cate->Catg_Name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <!-- /.col -->

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Category Type <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category_type_id" required>
                                        <option value="">Select Category Type</option>
                                        @foreach($catgeory_type as $cate_type)
                                        <option value="{{ $cate_type->Code }}" {{ old('category_type_id') == $cate_type->Code ? 'selected' : '' }}>{{ $cate_type->CategoryType }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Occupant Type <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="occupant_type_id" required>
                                        <option value="">Select Occupant Type</option>
                                        @foreach($occupants as $occu)
                                        <option value="{{ $occu->id }}" {{ old('occupant_type_id') == $occu->id ? 'selected' : '' }}>{{ $occu->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Room Category <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="room_category_id" required>
                                        <option value="">Select Room Category</option>
                                        @foreach($room_cates as $room_c)
                                        <option value="{{ $room_c->id }}" {{ old('room_category_id') == $room_c->id ? 'selected' : '' }}>{{ $room_c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Charges / Nite <span class="text-danger">*</span></label>
                                    <input type="number" name="charges" class="form-control" min="1" placeholder="Enter Charges / Nite" value="{{ old('charges') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>No. Of Rooms <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_booked_room" class="form-control" placeholder="Enter No. Of Rooms" value="{{ old('no_of_booked_room') }}" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Max No. Of Nites <span class="text-danger">*</span></label>
                                    <input type="number" name="max_no_of_nites" class="form-control" placeholder="Enter Max No. Of Nites" value="{{ old('max_no_of_nites') }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Room Charges</button>
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