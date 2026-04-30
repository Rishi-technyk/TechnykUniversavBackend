@extends('backend.layouts.app')

@section('title', 'Admin | Update Facility Slot')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Update Facility Slot</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.facility_slots') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.facility_slot.update', encrypt($data->id)) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                    
                                <div class="form-group">
                                    
                                    <label>Session <b class="text-danger">*</b></label>
                                    <select class="form-control" name="session_id" required>
                                        <option value="">Select Session</option>
                                        @foreach($session as $key => $sess)
                                        <option value="{{ $sess->id }}" {{ $data->session_id==$sess->id?'selected':'' }}>{{ $sess->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Facility <b class="text-danger">*</b></label>
                                    <select class="form-control" name="facility_id" required>
                                        <option value="">Select Facility</option>
                                        @foreach($facility as $key => $flt)
                                        <option value="{{ $flt->id }}" {{ $data->facility_id==$flt->id?'selected':'' }}>{{ $flt->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>

                            <div class="col-lg-6">
                                
                                <div class="form-group">
                                    
                                    <label>Slot <b class="text-danger">*</b></label>
                                    <select class="form-control" name="slot_id" required>
                                        <option value="">Select Slot</option>
                                        @foreach($slot as $key => $slt)
                                        <option value="{{ $slt->id }}" {{ $data->slot_id==$slt->id?'selected':'' }}>{{ $slt->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Update Facility Slot</button>
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