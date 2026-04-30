@extends('backend.layouts.app')

@section('title', 'Admin | Activity Booking Details')

@section('style')

@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Activity Booking Details</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.activity_bookings') }}" class="btn btn-outline-info btn-sm">Back</a>
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

                                    {{ $datas->booking_number }}
                                    
                                </div>

                                <div class="col-lg-2 col-6">
                                
                                    <span class="text-muted">Booking Date</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ date("d-m-Y", strtotime($datas->created_at)); }}
                                    
                                </div>

                                <div class="col-lg-2 col-6">
                                    
                                    <span class="text-muted">Member ID</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ $datas->memberID }}
                                    
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

                                    {{ $datas->chartID }}
                                    
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
                                    
                                    <span class="text-muted">Payment Status</span>

                                </div>

                                <div class="col-lg-4 col-6">
                                    @if($datas->payment_status=='Paid')
                                    <b class="text-success">Paid</b>
                                    @else
                                    <b class="text-success">Not Paid</b>
                                    @endif
                                    
                                </div>

                                <div class="col-lg-2 col-6">
                                    
                                    <span class="text-muted">Paid Payment</span>

                                </div>

                                <div class="col-lg-4 col-6">

                                    {{ number_format($datas->facility_total, 2) }}
                                    
                                </div>  

                                <div class="col-lg-2 col-6">
                                    
                                    <span class="text-muted">Booking Status</span>

                                </div>

                                <div class="col-lg-4 col-6">
                                    @if($datas->status == 'Active')
                                        <span class="text-success">Active</span>
                                    @elseif($datas->status == 'Cancelled')
                                        <span class="text-danger">Cancelled</span>
                                    @else
                                        <span class="text-warning">Pending</span>
                                    @endif
                                    
                                </div>                          

                            </div>

                            <table class="table table-striped" style="margin-top: 7%;">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Facility</th>
                                        <th scope="col">Session</th>
                                        <th scope="col">No. of Guest</th>
                                        <th scope="col">No. of Slots</th>
                                        <th scope="col">Guest Charge</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">GST ({{$datas->facility_gst_per}}%)</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row">1</th>
                                        <td>{{ $datas->facility->name ?? '' }}</td>
                                        <td>{{ $datas->session->name ?? '' }}</td>
                                        <td>{{ count($guests) }}</td>
                                        <td>{{ count($items) }}</td>
                                        <td>{{ number_format($datas->guest_total_amount, 2) }}</td>
                                        <td>{{ number_format($datas->facility_amount, 2) }}</td>
                                        <td>{{ number_format($datas->facility_gst_amt, 2) }}</td>
                                        <td>{{ number_format($datas->facility_total, 2) }}</td>
                                    </tr>                            
                                </tbody>
                            </table>

                            <hr>
                            <h5 class="text-muted">Guest Info</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Player Name</th>
                                        <th scope="col">Player Email</th>
                                        <th scope="col">Player Mobile</th>
                                        <th scope="col">Occupant Charge</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($guests as $key => $guest)
                                    <tr>
                                        <th scope="row">{{ ++$key }}</th>
                                        <td>{{ $guest->player_name }}</td>
                                        <td>{{ $guest->player_email }}</td>
                                        <td>{{ $guest->player_mobile }}</td>
                                        <td>{{ $guest->occupant_charge?number_format($guest->occupant_charge, 2):'0' }}</td>
                                    </tr>
                                    @endforeach                                
                                </tbody>
                            </table>
                            
                            <hr>
                            <h5 class="text-muted">Slot Info</h5>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Slot</th>
                                        <th scope="col">Slot Date</th>
                                        <th scope="col">Option</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $key => $item)
                                    <tr>
                                        <th scope="row">{{ ++$key }}</th>
                                        <td>{{ $item->slot->name ?? '' }}</td>
                                        <td>{{ date("M d, Y", strtotime($item->slot_date)); }}</td>
                                        <td> 
                                            @if($item->status=='Cancelled')
                                            <span class="text-danger">Cancelled</span>
                                            @else
                                            <span class="text-success">Active</span>
                                            @endif
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