@extends('frontend.layouts.app')

@section('title', 'Room Cancellation Invoice')

@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Room Cancellation Invoice</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">
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
                
                <span class="text-muted">Check IN</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ date("M d, Y", strtotime($datas->checkin)); }}
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Check OUT</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ date("M d, Y", strtotime($datas->checkout)); }}
                
            </div> 

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Payment Status</span>

            </div>

            <div class="col-lg-4 col-6">
                @if(isset($transaction) && $transaction->payment_status=='Paid')
                <b class="text-success">Paid</b>
                @else
                <b class="text-danger">Not Paid</b>
                @endif
                
            </div>

            <div class="col-lg-2 col-6">
                
                <span class="text-muted">Paid Payment</span>

            </div>

            <div class="col-lg-4 col-6">

                {{ format_price(getBookingTotal($datas->id), 2) }}
                
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

        <div class="room_table">
            <table class="table table-bordered" style="margin-top: 7%;">
                <thead>
                    <tr>
                        <th scope="col">Status</th>
                        <th scope="col">Room</th>
                        <th scope="col">Occupant Type</th>
                        <th scope="col">Adult/Child</th>
                        <th scope="col">Room Count / Days</th>
                        <th scope="col">GST(%)</th>
                        <th scope="col">Rent/Nite</th>
                        <th scope="col">Total</th>
                        <th scope="col">Cancellation GST</th>
                        <th scope="col">Cancellation Amt</th>
                        <th scope="col">Net Deducation</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $g_total = '0'; $deducation_amt = '0'; ?>
                    @foreach($data_items as $key => $item)
                    <tr>
                        <td>
                            @if($item->status=='Active' && $datas->checkout >= date('Y-m-d'))
                                <button class="btn btn-sm btn-outline-danger" onclick="cancelRoom({{ $item->id }})">Cancel</button>
                            @elseif($item->status=='Cancelled')
                                <b class="text-danger">Cancelled</b>
                            @elseif($item->status=='Active')
                                <b class="text-success">Active</b>
                            @endif
                        </td>
                        <td>{{ $item->room->name ?? '' }}</td>
                        <td>{{ $item->occupant->name ?? '' }}</td>
                        <td>{{ $item->adult ?? '0' }}/{{ $item->child ?? '0' }}</td>
                        <td>{{ $item->no_of_rooms }} / {{ $item->no_of_days }}</td>
                        <td>{{ $item->gst_per }}</td>
                        <td>{{ format_price($item->room_charges, 2) }}</td>
                        <?php 
                            $g_total += $item->room_charge_total;
                            $deducation_amt += $item->cancellation_deducation;
                        ?>
                        <td>{{ format_price($item->room_charge_total, 2) }}</td>
                        <td>{{ $item->cancellation_GST }}</td>
                        <td>{{ format_price($item->cancellation_amt, 2) }}</td>
                        <td>{{ format_price($item->cancellation_deducation, 2) }}</td>
                    </tr>

                    @endforeach
                    
                </tbody>

                <tfoot>
                    <tr>
                        <td colspan="6"></td>
                        <td> <b>Total</b> </td>
                        <td class="flow-right"> <b>{{ format_price($g_total, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="9"></td>
                        <td> <b>Total Advance Paid</b> </td>
                        <td class="flow-right"> <b>{{ format_price($g_total, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="9"></td>
                        <td> <b>Previous Cancelaltion</b> </td>
                        <?php $prev_de = $prev_bookings - ($latest_bookings->cancellation_deducation??'0'); ?>
                    <td class="flow-right"> <b> <span class="text-danger">(-)</span> {{ format_price($prev_de, 2) }}</b> </td>
                    </tr>

                    <tr>
                        <td colspan="9"></td>
                        <td> <b>Deduction</b> </td>
                        <td class="flow-right"> <b><span class="text-danger">(-)</span> {{ format_price($latest_bookings->cancellation_deducation ?? '0', 2) }}</b> </td>
                    </tr>
                    <?php $refund = $g_total-$deducation_amt; ?>
                    <tr>
                        <td colspan="9"></td>
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
    function cancelRoom(bookingID) {
        if(confirm("Are you sure?")){

            $.ajax({

                type:'POST',

                url:"{{ route('cancelRoom') }}",

                data:{bookingID:bookingID},

                success:function(data){
                    console.log(data);
                    getRoomDetails();
                }

            });

        }
    }
</script>

<script>
    function getRoomDetails() {

        var booking_id = '<?php echo $datas->id; ?>';
        
        $.ajax({

            type:'POST',

            url:"{{ route('get.room.item') }}",

            data:{booking_id:booking_id},

            success:function(data){
                console.log(data);
                $('.room_table').html(data);
            }

        });
    }
</script>
@endsection