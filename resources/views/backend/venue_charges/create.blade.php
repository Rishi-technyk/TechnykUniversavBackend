@extends('backend.layouts.app')

@section('title', 'Admin | Create Venue Charge')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Create Venue Charge</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.venue_charges') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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
                    <form action="{{ route('admin.venue_charge.store') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Occupant Master <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="occupant_id" required>
                                        <option value="">Select Occupant Master</option>
                                        @foreach($occupant as $occ)
                                        <option value="{{ $occ->id }}" {{ old('occupant_id') == $occ->id ? 'selected' : '' }}>{{ $occ->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Venue Master <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="venue_id" required>
                                        <option value="">Select Venue Master</option>
                                        @foreach($venue as $ven)
                                        <option value="{{ $ven->id }}" {{ old('venue_id') == $ven->id ? 'selected' : '' }}>{{ $ven->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Session <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="session_id" required>
                                        <option value="">Select Session</option>
                                        @foreach($session as $sess)
                                        <option value="{{ $sess->id }}" {{ old('session_id') == $sess->id ? 'selected' : '' }}>{{ $sess->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label>Rate <span class="text-danger">*</span></label>
                                    <input type="number" name="rate" class="form-control" placeholder="Enter Rate" value="{{ old('rate') }}" required>
                                </div>
                            </div>
                            <!-- /.col -->

                        </div>
                        <!-- /.row -->

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-outline-info">Create Venue Charge</button>
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