@extends('backend.layouts.app')

@section('title', 'Admin | Room Bookings')

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
                    <h1>Room Bookings</h1>
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
                                            <label>Member ID</label>
                                            <input type="text" name="member_id" value="{{ $request->member_id }}" class="form-control" placeholder="Member ID">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Booking No.</label>
                                            <input type="text" name="booking_no" value="{{ $request->booking_no }}" class="form-control" placeholder="Booking No.">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Check In</label>
                                            <input type="date" name="checkin" value="{{ $request->checkin }}" class="form-control" placeholder="Check In">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Check Out</label>
                                            <input type="date" name="checkout" value="{{ $request->checkout }}" class="form-control" placeholder="Check Out">
                                        </div>

                                        <div class="col-lg-3 mt-2">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="">Select Status</option>
                                                <option value="Pending" {{ $request->status=='Pending'?'selected':'' }}>Pending</option>
                                                <option value="Active" {{ $request->status=='Active'?'selected':'' }}>Active</option>
                                                <option value="Cancelled" {{ $request->status=='Cancelled'?'selected':'' }}>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mt-2 align-self-end">
                                            <button class="btn btn-sm btn-outline-info mt-4" type="submit">Search</button>
                                            <a href="{{ route('admin.room_bookings') }}" class="btn btn-sm btn-outline-danger mt-4">Reset</a>
                                        </div>

                                    </div>

                                </form>
                            </div>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Member ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Room</th>
                                        <th scope="col">Nites</th>
                                        <th scope="col">Booking No</th>
                                        <th scope="col">Booking Date</th>
                                        <th scope="col">Check IN</th>
                                        <th scope="col">Check OUT</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">GST Amt</th>
                                        <th scope="col">Total Amt</th>
                                        <th scope="col">From</th>
                                        @can('room-booking.view')
                                        <th scope="col">Action</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $data)
                                        <?php $items = App\Models\RoomBookingItem::where('booking_id', $data->id)->get(); ?>
                                        @foreach($items as $key => $itms)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td title="{{ $data->memberID }}">{{ $data->memberID }}</td>
                                            <td title="{{ $data->memberName }}">{{ $data->memberName }}</td>
                                            <td>{{ $itms->room->name }}</td>
                                            <td>{{ $itms->no_of_days }}</td>
                                            <td title="{{ $data->booking_number }}">{{ $data->booking_number }}</td>
                                            <td>{{ $data->created_at ? date("M d, Y", strtotime($data->created_at)) : '' }}</td>
                                            <td>{{ $data->checkin ? date("M d, Y", strtotime($data->checkin)) : '' }}</td>
                                            <td>{{ $data->checkout ? date("M d, Y", strtotime($data->checkout)) : '' }}</td>
                                            <td>{{ $data->status }}</td>
                                            <td>{{ format_price($itms->room_charges*$itms->no_of_days) }}</td>
                                            <td>{{ format_price($itms->gst_amount) }}</td>
                                            <td>{{ format_price(($itms->room_charges*$itms->no_of_days)+$itms->gst_amount) }}</td>  
                                            <td>{{ $data->booking_from }}</td>
                                            @can('room-booking.view') 
                                            <td>
                                                <a href="{{ route('admin.room_booking.details', encrypt($data->id)) }}" class="btn btn-outline-info btn-sm" data-toggle="tooltip" data-original-title="Block Room Edit">
                                                    View 
                                                </a>
                                            </td>
                                            @endcan
                                        </tr>
                                    @endforeach
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