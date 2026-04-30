@extends('frontend.layouts.app')

@section('title', 'Room Booking Details')

@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Room Booking Details</h4>
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

        <table class="table table-striped" style="margin-top: 7%;">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Room</th>
                    <th scope="col">Occupant Type</th>
                    <th scope="col">Additional Info</th>
                    <th scope="col">Adult/Child</th>
                    <th scope="col">Room Count / Days</th>
                    <th scope="col">GST(%)</th>
                    <th scope="col">Rent/Nite</th>
                    <th scope="col">Total Amt</th>
                </tr>
            </thead>
            <tbody>
                <?php $g_total = '0'; ?>
                @foreach($data_items as $key => $item)
                <tr>
                    <th scope="row">{{ ++$key }}</th>
                    <td>{{ $item->room->name ?? '' }}</td>
                    <td>{{ $item->occupant->name ?? '' }}</td>
                    <td style="text-align: left;">
                        @if($item->guest_name)
                        <small> <b>Name : </b> {{ $item->guest_name }}</small><br>
                        @endif
                        @if($item->guest_email)
                        <small> <b>Email : </b> {{ $item->guest_email }}</small><br>
                        @endif
                        @if($item->guest_mobile)
                        <small> <b>Mobile : </b> {{ $item->guest_mobile }}</small><br>
                        @endif
                    </td>
                    <td>{{ $item->adult ?? '0' }}/{{ $item->child ?? '0' }}</td>
                    <td>{{ $item->no_of_rooms }} / {{ $item->no_of_days }}</td>
                    <td>{{ $item->gst_per }}</td>
                    <td>{{ format_price($item->room_charges, 2) }}</td>
                    <?php 
                        $GST_a = $item->gst_amount; 
                        
                        $g_total += $item->room_charge_total;
                    ?>
                    <td>{{ format_price($item->room_charge_total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="7"></td>
                    <td> <b>Total</b> </td>
                    <td> <b>{{ format_price($g_total, 2) }}</b> </td>
                </tr>
                
            </tbody>
        </table>
        @if(isset($transaction) && $transaction->payment_status=='Paid')
        <div style="text-align: end; margin-top: 6%;">
            <a href="{{ route('room.details.download', $datas->id) }}"><button type="button">Download</button></a>
        </div>
        @endif
    </div>

</div>

@endsection

@section('script')

@endsection