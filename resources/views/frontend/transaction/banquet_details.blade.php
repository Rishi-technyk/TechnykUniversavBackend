@extends('frontend.layouts.app')

@section('title', 'Banquet Booking Details')

@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Banquet Booking Details</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">
        
        <div class="row">

            <div class="col-lg-12 text-center mt-2 mb-4">
                <h5>Invoice</h5>
            </div>

            @if(isset($transaction))

                <div class="col-lg-2 col-6">
                
                    <span class="text-muted">Booking ID</span>

                </div>

                <div class="col-lg-4 col-6">

                    {{ $transaction->transID }}
                    
                </div>

                <div class="col-lg-2 col-6">
                
                    <span class="text-muted">Booking Date</span>

                </div>

                <div class="col-lg-4 col-6">

                    {{ date("d-m-Y", strtotime($transaction->created_at)); }}
                    
                </div>

            @endif

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Occupant Type</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->occupant->name ?? '' }}
                
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

                {{ $datas->memberName }}
                
            </div>


            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Card ID</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->cardID }}
                
            </div>
            

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Mobile</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->memberMobile }}
                
            </div>


            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Function Date</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->funDate ? date("d-m-Y", strtotime($datas->funDate)) : '' }}
                
            </div>


            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Email</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->memberEmail }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Function Type</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->function->name ?? '' }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">No. Of Person</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->noofPerson }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Address</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->address ?? 'NA' }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Payment Status</span>

            </div>

            <div class="col-lg-4 col-6">

                @if(isset($transaction))

                    @if(isset($transaction) && $transaction->payment_status=='Paid')

                    <b class="text-success">{{ $transaction->payment_status }}</b>

                    @elseif(isset($transaction) && $transaction->payment_status=='Paid' || $transaction->payment_status=='Not Paid')

                    <span class="text-danger">{{ $transaction->payment_status }}</span>

                    @endif

                @else

                    <span class="text-danger">Not Paid</span>

                @endif
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Remark</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ $datas->remark ?? 'NA' }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Paid Payment</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ format_price(getVenueTotal($datas->id), 2) }}
                
            </div>                            

        </div>

        <table class="table table-striped" style="margin-top: 7%;">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Venue</th>
                    <th scope="col">Status</th>
                    <th scope="col">Session</th>
                    <th scope="col">GST Per.</th>
                    <th scope="col">GST Amount</th>
                    <th scope="col">Charges</th>
                    <th scope="col">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = '0'; ?>
                @foreach($bookings as $key => $booking)
                <tr>
                    <?php
                        $session = DB::table('sessions')->where('id', $booking->session_id)->first();
                        $total += $booking->total;
                    ?>
                    <th scope="row">{{ ++$key }}</th>
                    <td>{{ $booking->venue->name ?? '' }}</td>
                    <td>
                        @if($booking->status=='Active')
                            <b class="text-success">Active</b>
                        @elseif($booking->status=='Cancelled')
                            <span class="text-danger">Cancelled</span>
                        @else
                            <span class="text-warning">Pending</span>
                        @endif
                    </td>
                    <td>{{ $session->name }}</td>
                    <td>{{ $booking->gst_per }}%</td>
                    <td>{{ format_price($booking->gst_amount, 2) }}</td>
                    <td>{{ format_price($booking->charges, 2) }}</td>
                    <td>{{ format_price($booking->total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="6"></td>
                    <td> <b>Total</b> </td>
                    <td> <b>{{ format_price($total, 2) }}</b> </td>
                </tr>
                
            </tbody>
        </table>
        @if(isset($transaction) && $transaction->payment_status=='Paid')
        <div style="text-align: end; margin-top: 6%;">
            <a href="{{ route('banquet.details.download', $datas->id) }}"><button type="button">Download</button></a>
        </div>
        @endif

    </div>

</div>

@endsection

@section('script')

@endsection