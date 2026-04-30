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
                    <a href="{{ route('admin.block_slots') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.block_slot.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-4">
                                
                                <div class="form-group">
                                    
                                    <label>Facility <b class="text-danger">*</b></label>
                                    <select class="form-control select2" name="facility_id" required>
                                        <option value="">Select Facility</option>
                                        @foreach($facility as $key => $flt)
                                        <option value="{{ $flt->id }}">{{ $flt->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>

                            <div class="col-lg-4">
                                
                                <div class="form-group">
                                    
                                    <label>Slot <b class="text-danger">*</b></label>
                                    <select class="form-control select2" name="slot_id" required>
                                        <option value="">Select Slot</option>
                                        @foreach($slot as $key => $slt)
                                        <option value="{{ $slt->id }}">{{ $slt->name }}</option>
                                        @endforeach
                                    </select>

                                </div>

                            </div>

                            <div class="col-lg-4">
                                    
                                <div class="form-group">
                                    
                                    <label>Date <b class="text-danger">*</b> </label>
                                    <input type="date" name="date" class="form-control" value="{{ old('date') }}" placeholder="Select Date" required>

                                </div>

                            </div>

                            <div class="col-lg-12">
                                
                                <div class="form-group">
                                    
                                    <label>Remark</label>
                                    <textarea class="form-control" name="remark" placeholder="Enter Remark">{{ old('remark') }}</textarea>

                                </div>

                            </div> 

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Block Slot</button>
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