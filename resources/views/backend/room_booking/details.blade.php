@extends('backend.layouts.app')

@section('title', 'Admin | Room Booking Details')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Room Booking Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.room_bookings') }}" class="btn btn-outline-info btn-sm"><i class="nav-icon fas fa-arrow-left"></i> &nbsp;Back</a>
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

                            <div class="row">
                                <div class="col-lg-12 text-center mt-2 mb-4">
                                    <h5>Invoice</h5>
                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Booking ID</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $data->booking_number }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Booking Date</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ date("d-m-Y", strtotime($data->created_at)); }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Member ID</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $data->memberID }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Name</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $member->DisplayName }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Card ID</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $data->chartID }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Mobile</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $member->Mobile }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Email</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $member->Email }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Address</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $member->Address ?? 'NA' }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Check IN</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ date("M d, Y", strtotime($data->checkin)); }}

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Check OUT</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ date("M d, Y", strtotime($data->checkout)); }}

                                </div> 

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Payment Status</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    <b class="text-success">Paid</b>

                                </div>

                                <div class="col-lg-2 col-6">

                                    <span class="text-muted">Paid Payment</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ number_format(getBookingTotal($data->id), 2) }}

                                </div>                            

                            </div>
        
                            <table class="table table-bordered mt-4">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Room Name</th>
                                        <th scope="col">Nites</th>
                                        <th scope="col">Room Charges</th>
                                        <th scope="col">GST Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $key => $itms)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $itms->room->name }}</td>
                                        <td>{{ $itms->no_of_days }}</td>
                                        <td>{{ $itms->room_charges }}</td>
                                        <td>{{ format_price($itms->gst_amount) }}</td>
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