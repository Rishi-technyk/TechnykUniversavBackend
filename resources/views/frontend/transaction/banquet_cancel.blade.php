@extends('frontend.layouts.app')

@section('title', 'Banquet Booking Cancellation')

@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Banquet Booking Cancellation</h4>
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

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Booking Status</span>

            </div>

            <div class="col-lg-4 col-6">

                <?php $checkVenues = App\Models\BanquetBookingCharges::where('banquet_booking_id', $datas->id)->where('status','Active')->exists(); ?>

                @if($checkVenues)
                    <b class="text-success">Active</b>
                @else
                    <b class="text-danger">Cancelled</b>
                @endif
                
            </div>                            

        </div>

        <div class="venue_table table-responsive">
            <table class="table table-bordered" style="margin-top: 7%;">
                <thead>
                    <tr>
                        <th scope="col">Status</th>
                        <th scope="col">Venue</th>
                        <th scope="col">Session</th>
                        <th scope="col">GST Per.</th>
                        <th scope="col">GST Amount</th>
                        <th scope="col">Security Deposit</th>
                        <th scope="col">Charges</th>
                        <th scope="col">Total</th>
                        <th scope="col">Cancellation (%)</th>
                        <th scope="col">Cancellation Amt</th>
                        <th scope="col">GST</th>
                        <th scope="col">Net Deducation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $total = '0'; $deducation_amt = '0'; ?> 
                    @foreach($bookings as $key => $booking)
                    <tr>
                        <?php
                            $session = DB::table('sessions')->where('id', $booking->session_id)->first();
                            $total += $booking->total;
                            $deducation_amt += $booking->cancellation_deducation;
                        ?>
                        <td scope="row">
                            @if($booking->status=='Active' && $booking->funDate >= date('Y-m-d'))
                            <button class="btn btn-sm btn-outline-danger" onclick="cancelVenue({{ $booking->id }}, {{ $booking->vanue_id }})">Cancel</button>
                            @elseif($booking->status=='Cancelled')
                            <b class="text-danger">Cancelled</b>
                            @elseif($booking->status=='Active')
                            <b class="text-success">Active</b>
                            @endif
                        </td>
                        <td>{{ $booking->venue->name ?? '' }}</td>
                        <td>{{ $session->name }}</td>
                        <td>{{ $booking->gst_per }}%</td>
                        <td>{{ format_price($booking->gst_amount, 2) }}</td>
                        <td>{{ format_price($booking->security_deposit, 2) }}</td>
                        <td>{{ format_price($booking->charges, 2) }}</td>
                        <td>{{ format_price($booking->total, 2) }}</td>
                        <td>{{ $booking->cancellation_per }}{{ $booking->cancellation_per ? '%' : '' }}</td>
                        <td>{{ format_price($booking->cancellation_amt, 2) }}</td>
                        <td>{{ format_price($booking->cancellation_GST_amt, 2) }}</td>
                        <td>{{ format_price($booking->cancellation_deducation, 2) }}</td>
                    </tr>
                    @endforeach
                    
                    
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="6"></td>
                        <td> <b>Total</b> </td>
                        <td class="flow-right"> <b>{{ format_price($total, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="10"></td>
                        <td> <b>Total Advance Paid</b> </td>
                        <td class="flow-right"> <b>{{ format_price($total, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="10"></td>
                        <td> <b>Previous Cancelaltion</b> </td>
                        <?php $prev_de = $prev_bookings - ($latest_bookings->cancellation_deducation??'0'); ?>
                        <td class="flow-right"> <b> <span class="text-danger">(-)</span> {{ format_price($prev_de, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="10"></td>
                        <td> <b>Deduction</b> </td>
                        <td class="flow-right"> <b><span class="text-danger">(-)</span> {{ format_price($latest_bookings->cancellation_deducation ?? '0', 2) }}</b> </td>
                    </tr>
                    <?php $refund = $total-$deducation_amt; ?>
                    <tr>
                        <td colspan="10"></td>
                        <td> <b>Refund</b> </td>
                        <td class="flow-right"> <b>{{ format_price($refund, 2) }}</b> </td>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>

</div>

@endsection

@section('script')
<script>
    $(document).ready(function() {

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

    });
</script>

<script>
    function cancelVenue(bookingID, venue_id) {
        if(confirm("Are you sure?")){

            $.ajax({

                type:'POST',

                url:"{{ route('cancelVenue') }}",

                data:{bookingID:bookingID, venue_id:venue_id},

                success:function(data){
                    console.log(data);

                    if(data.status){
                        
                        getVenueDetails();
                    } else {
                        alert(data.msg);
                    }
                    
                }

            });

        }
    }
</script>

<script>
    function getVenueDetails() {

        var booking_id = '<?php echo $datas->id; ?>';
        
        $.ajax({

            type:'POST',

            url:"{{ route('get.booking.venues') }}",

            data:{booking_id:booking_id},

            success:function(data){
                console.log(data);
                $('.venue_table').html(data);
            }

        });
    }
</script>
@endsection