
@extends('layouts.web')
@section('content')
<style>
    .table {
        width: 100%;
    }
</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Room Booking Transactions</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="row d-lg-flex card mb-1 h-100">
                    <div class="card-body">
                        
                        <form action="" method="Get">

                            <div class="row">

                                <div class="col-lg-2 col-12">
                                    <div class="form-group">
                                        <label>Member ID</label>
                                        <input type="text" name="memberID" class="form-control" value="{{ $request->memberID }}" placeholder="Search By Memebr ID">
                                    </div>
                                </div>

                                <div class="col-lg-2 col-12">
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="memberName" class="form-control" value="{{ $request->memberName }}" placeholder="Search By Name">
                                    </div>
                                </div>

                                <div class="col-lg-2 col-12">
                                    <div class="form-group">
                                        <label>Booking No.</label>
                                        <input type="text" name="booking_no" class="form-control" value="{{ $request->booking_no }}" placeholder="Search By Booking Number">
                                    </div>
                                </div>

                                <div class="col-lg-2 col-12">
                                    <div class="form-group">
                                        <label>Check IN</label>
                                        <input type="date" name="checkIn" id="fromDate" value="{{ $request->checkIn }}" class="form-control" placeholder="Booking Number">
                                    </div>
                                </div>

                                <div class="col-lg-2 col-12">
                                    <div class="form-group">
                                        <label>Check Out</label>
                                        <input type="date" name="checkOut" id="toDate" value="{{ $request->checkOut }}" class="form-control" placeholder="Booking Number">
                                    </div>
                                </div>

                                <div class="col-lg-2 col-12 mt-4">
                                    <button class="btn btn-success btn-sm" type="submit">Search</button>
                                    <a href="{{ route('room.traction') }}"><button class="btn btn-danger btn-sm" type="button">Clear</button></a>
                                </div>

                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-lg-12">
                <div class="row d-none d-lg-flex card mb-1 h-100">
                    <div class="card-body">
                        <!-- List View for Larger Screens -->
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>                                    
                                    <th scope="col">Member ID</th>
                                    <th scope="col">Booking No</th>
                                    <th scope="col">Booking Date</th>
                                    <th scope="col">Check IN</th>
                                    <th scope="col">Check OUT</th>
                                    <th scope="col">Payment Status</th>
                                    <th scope="col">Booking Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($datas))
                                @foreach($datas as $key => $data)
                                    <tr>
                                        <?php $transaction = DB::table('transactions')->where('transID', $data->booking_number)->first(); ?>
                                        <th scope="row">{{ ++$key }}</th>                                        
                                        <td>{{ $data->memberID }}</td>
                                        <td>{{ $data->booking_number }}</td>
                                        <td>{{ $data->created_at ? date("M d, Y", strtotime($data->created_at)) : '' }}</td>
                                        <td>{{ $data->checkin ? date("M d, Y", strtotime($data->checkin)) : '' }}</td>
                                        <td>{{ $data->checkout ? date("M d, Y", strtotime($data->checkout)) : '' }}</td>
                                        <td>
                                            
                                            @if($transaction && $transaction->payment_status=='Paid')
                                            <span class="text-success">{{ $transaction->payment_status }}</span>
                                            @else
                                            <span class="text-danger">Not Paid</span>
                                            @endif
                                        </td>
                                        <td>
                                           
                                            @if($data->status == 'Active')
                                                <span class="text-success">Active</span>
                                            @elseif($data->status == 'Cancelled')
                                                <span class="text-danger">Cancelled</span>
                                            @else
                                                <span class="text-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td style="text-align: left; padding-left: 1% !important;">
                                            <a href="{{ route('room.booking.details', encrypt($data->id)) }}" class="btn btn-sm btn-secondary">View</a>
                                            
                                            @if($transaction && $transaction->payment_status=='Paid')
                                            <a href="{{ route('room.booking.cancel', encrypt($data->id)) }}" class="btn btn-sm btn-danger">Cancel</a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                @else
                                    <tr>
                                        <td colspan="9" class="text-center">No Booking</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>

                        <div >
                            {!! $datas->links() !!}
                        </div>
                    </div>
                </div>

                <div class="row d-lg-none mt-3">
                    <!-- Card View for Mobile Screens -->
                    @foreach($datas as $key => $val)
                        <div class="col-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <p><strong>#{{ ++$key }}</strong></p>
                                    <h5 class="card-title">Member ID: {{ $val->memberID }}</h5>
                                    <p class="card-text"><strong>Booking No:</strong> {{ $val->booking_number }}</p>
                                    <p class="card-text"><strong>Check IN:</strong> {{ $data->checkin ? date("M d, Y", strtotime($data->checkin)) : '' }}</p>
                                    <p class="card-text"><strong>Check OUT:</strong> {{ $data->checkout ? date("M d, Y", strtotime($data->checkout)) : '' }}</p>

                                    <p class="card-text"><strong>Payment Status:</strong>
                                        <?php $transaction = DB::table('transactions')->where('room_booking_id', $val->id)->first(); ?>

                                        @if($transaction && $transaction->payment_status == 'Paid')
                                            <span class="text-success">Paid</span>
                                        @elseif($transaction && $transaction->payment_status == 'Failed')
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span class="text-danger">Not Paid</span>
                                        @endif
                                    </p>
                                    <p class="card-text"><a href="{{ route('room.booking.details', encrypt($val->id)) }}" class="btn btn-sm btn-secondary">View</a></p>
                                    <?php $checkVenues = App\Models\BanquetBookingCharges::where('banquet_booking_id', $val->id)->where('status','Active')->exists(); ?>
                                    @if($transaction && $transaction->payment_status=='Paid' && $val->funDate >= date('Y-m-d') && $checkVenues)
                                    <!-- <p class="card-text"><a href="{{ route('banquet.cancel', encrypt($val->id)) }}" class="btn btn-sm btn-danger mt-2">Cancel</a></p> -->
                                    
                                    @endif
                                    
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

<script>    
    $(document).ready(function() {
        document.getElementById("fromDate").min = new Date().toISOString().split("T")[0];
        document.getElementById("toDate").min = new Date().toISOString().split("T")[0];
    });
</script>

@endpush()
@endsection