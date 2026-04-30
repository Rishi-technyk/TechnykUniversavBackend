@extends('backend.layouts.app')

@section('title', 'Admin | Activity Bookings')

@section('style')
<style>
    .buttons-excel {
        display: block;
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Activity Bookings</h1>
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

                            <div class="mb-2 mt-2">
                                <form action="" method="Get" class="mb-4">

                                    <div class="row">

                                        <div class="col-lg-3">
                                            <label>Session</label>
                                            <select class="form-control select2" name="session">
                                                <option value="">Select Session</option>
                                                @foreach($session as $sess)
                                                    <option value="{{ $sess->id }}" {{ $request->session==$sess->id?'selected':'' }}>{{ $sess->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Facility</label>
                                            <select class="form-control select2" name="facility_id">
                                                <option value="">Select Facility</option>
                                                @foreach($facility as $fc)
                                                    <option value="{{ $fc->id }}" {{ $request->facility_id==$fc->id?'selected':'' }}>{{ $fc->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    
                                        <div class="col-lg-3">
                                            <label>Slot Date</label>
                                            <input type="date" name="slot_date" value="{{ $request->slot_date }}" class="form-control" placeholder="Slot Date">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Status</label>
                                            <select class="form-control select2" name="status">
                                                <option value="">Select Status</option>
                                                <option value="Active" {{ $request->status=='Active'?'selected':'' }}>Active</option>
                                                <option value="Cancelled" {{ $request->status=='Cancelled'?'selected':'' }}>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-12 mt-2 text-right">
                                            <button class="btn btn-sm btn-outline-info mt-4" type="submit">Search</button>
                                            <a href="{{ route('admin.room_bookings') }}" class="btn btn-sm btn-outline-danger mt-4">Reset</a>
                                        </div>

                                    </div>

                                </form>
                            </div>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">Booking No.</th>
                                        <th scope="col">Member ID</th>
                                        <th scope="col">Facility</th>
                                        <th scope="col">Slot</th>
                                        <th scope="col">Session</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Booking Date</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                    <tr>
                                        <td>{{ $data->booking_number }}</td>
                                        <td>{{ $data->memberID ?? '' }}</td>
                                        <td>{{ $data->facility->name ?? '' }}</td>
                                        <td>
                                            <small>
                                                @if($data->game_item)
                                                    @foreach($data->game_item as $slot)
                                                        <small>{{ $slot->slot_date }}, {{ $slot->slot->label }}</small><br>
                                                    @endforeach
                                                @endif
                                            </small>
                                        </td>
                                        <td>{{ $data->session->name ?? '' }}</td>
                                        <td>
                                            @if($data->status=='Active')
                                            <span class="text-success">Active</span>
                                            @elseif($data->status=='Cancelled')
                                            <span class="text-danger">Cancelled</span>
                                            @else
                                            <span class="text-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td>{{ $data->created_at ? date("d-m-Y", strtotime($data->created_at)) : '' }}</td>
                                        <td>
                                            <a href="{{ route('booking.details', $data->id) }}"><button class="btn-sm btn btn-outline-info" type="button">View</button></a>
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