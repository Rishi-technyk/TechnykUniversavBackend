
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
                   <h5>Transactions</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="row d-lg-flex card mb-1 h-100">
                    <div class="card-body">
                        
                        <form action="" method="Get">

                            <div class="row">

                                <div class="col-lg-3 col-12">
                                    <div class="form-group">
                                        <label>Function Date</label>
                                        <input type="date" name="function_date" value="{{ $request->function_date }}" class="form-control">
                                    </div>
                                </div>

                                <div class="col-lg-3 col-12">
                                    <div class="form-group">
                                        <label>Booking No.</label>
                                        <input type="text" name="booking_no" value="{{ $request->booking_no }}" class="form-control" placeholder="Booking Number">
                                    </div>
                                </div>

                                <div class="col-lg-3 col-12 mt-4">
                                    <button class="btn btn-success btn-sm" type="submit">Search</button>
                                    <a href="{{ route('banquet.traction') }}"><button class="btn btn-danger btn-sm" type="button">Clear</button></a>
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
                                    <th scope="col">Name</th>
                                    <th scope="col">Booking No</th>
                                    <th scope="col">Booking Date</th>
                                    <th scope="col">No. of Person</th>
                                    <th scope="col">Function Date</th>
                                    <th scope="col">Payment Status</th>
                                    <th scope="col">Booking Status</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($datas))
                                @foreach($datas as $key => $data)
                                    <tr>
                                        <?php $transaction = DB::table('transactions')->where('transID', $data->booking_ID)->where('banquet_booking_id', $data->id)->latest()->first(); ?>
                                        <th scope="row">{{ ++$key }}</th>                                        
                                        <td>{{ $data->memberName }}</td>
                                        <td>{{ $transaction->transID }}</td>
                                        <td>{{ $transaction->created_at ? date("d-m-Y", strtotime($transaction->created_at)) : '' }}</td>
                                        <td>{{ $data->noofPerson }}</td>
                                        <td>{{ $data->funDate ? date("d-m-Y", strtotime($data->funDate)) : '' }}</td>
                                        <td>
                                            
                                            @if($transaction && $transaction->payment_status=='Paid')
                                            <span class="text-success">{{ $transaction->payment_status }}</span>
                                            @else
                                            <span class="text-danger">{{ $transaction->payment_status }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <?php $checkVenues = App\Models\BanquetBookingCharges::where('banquet_booking_id', $data->id)->where('status','Active')->exists(); ?>

                                            @if($data->status != 'Pending')
                                                @if($checkVenues)
                                                    <span class="text-success">Active</span>
                                                @else
                                                    <span class="text-danger">Cancelled</span>
                                                @endif
                                            @else
                                                <span class="text-warning">Pending</span>
                                            @endif
                                        </td>
                                        <td style="text-align: left; padding-left: 1% !important;">
                                            <a href="{{ route('banquet.details', encrypt($data->id)) }}" class="btn btn-sm btn-secondary">View</a>
                                            
                                            @if($transaction && $transaction->payment_status=='Paid')
                                                @if($data->status == 'Cancelled')

                                                    <a href="{{ route('banquet.cancel', encrypt($data->id)) }}" class="btn btn-sm btn-danger">Cancel</a>

                                                @else

                                                    @if($data->funDate >= date('Y-m-d'))
                                                        <a href="{{ route('cancelVenue', encrypt($data->id)) }}" onclick="return confirm('Do you want to cancel this booking?')" class="btn btn-sm btn-danger">Cancel</a>
                                                    @endif

                                                @endif
                                            
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
                                    <h5 class="card-title">Member Name: {{ $val->memberName }}</h5>
                                    <p class="card-text"><strong>Member Email:</strong> {{ $val->memberEmail }}</p>
                                    <p class="card-text"><strong>Member Mobile:</strong> {{ $val->memberMobile }}</p>
                                    <p class="card-text"><strong>No. Of Person:</strong> {{ $val->noofPerson }}</p>
                                    <p class="card-text"><strong>Function Date:</strong> {{ $val->funDate ? date("d-m-Y", strtotime($val->funDate)) : '' }}</p>

                                    <p class="card-text"><strong>Payment Status:</strong>
                                        <?php $transaction = DB::table('transactions')->where('banquet_booking_id', $val->id)->first(); ?>

                                        @if($transaction && $transaction->payment_status == 'Paid')
                                            <span class="text-success">Paid</span>
                                        @elseif($transaction && $transaction->payment_status == 'Failed')
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span class="text-danger">Not Paid</span>
                                        @endif
                                    </p>
                                    <p class="card-text"><a href="{{ route('banquet.details', encrypt($val->id)) }}" class="btn btn-sm btn-secondary">View</a></p>

                                    @if($transaction && $transaction->payment_status=='Paid')
                                    <p class="card-text"><a href="{{ route('banquet.cancel', encrypt($val->id)) }}" class="btn btn-sm btn-danger mt-2">Cancel</a></p>
                                    
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

@endpush()
@endsection