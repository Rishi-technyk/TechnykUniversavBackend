@extends('layouts.admin_web')

@section('content')

<style>

    table td {

        text-align: center !important;

    }



    .dataTables_filter {

        display: none !important;

    }



    .mb5 {

        margin-bottom: 5%;

    }



    .buttons-excel {

        background-color: #198754 !important;

        color: white !important;

    }



    .buttons-excel {

        display: block !important;

    }

</style>

<div class="card mb-1 h-100">

    <div class="card-header">                       

        

        <div class="row">

            <div class="col-lg-6">

                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>

               Room Bookings

            </div>

            

        </div>

    </div>

    <div class="card-body">



        <div class="row">

            

            <div class="col-lg-12">

                

                <div class="row d-lg-flex mb-1 h-100">



                    <div class="">



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



                                <b class="text-success">Paid</b>

                                

                            </div>



                            <div class="col-lg-2 col-6">

                                

                                <span class="text-muted">Paid Payment</span>



                            </div>



                            <div class="col-lg-4 col-6">



                                {{ number_format(getBookingTotal($datas->id), 2) }}

                                

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

                                    <td>

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

                                    <td>{{ number_format($item->room_charges, 2) }}</td>

                                    <?php 

                                        $g_total += $item->room_charge_total;

                                    ?>

                                    <td>{{ number_format($item->room_charge_total, 2) }}</td>

                                </tr>

                                @endforeach

                                <tr>

                                    <td colspan="7"></td>

                                    <td> <b>Total</b> </td>

                                    <td> <b>{{ number_format($g_total, 2) }}</b> </td>

                                </tr>

                                

                            </tbody>

                        </table>



                    </div>



                </div>

                

            </div>



        </div>

    </div>

</div>

@push('js')



@endpush()

@endsection