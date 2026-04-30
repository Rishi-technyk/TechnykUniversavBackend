<div class="card">
    <div class="card-body card-hight">
        <div class="summary-box">
            
            <h5 class="facility-title mt-2">{{ $data->facility->name ?? '' }}</h5>

            <p class="facility-sub-title mt-2">{{ $data->facility->short_description ?? '' }}</p>

        </div>
    
        <div class="summary-box mt-4">
            
            <h5 class="payment-title mt-2">Payment Summary</h5>

            <div class="row mt-2">
                <div class="col-lg-3"></div>
                <div class="col-lg-5">
                    <p class="fz-14"> <small>SLOTS</small> </p>
                </div>
                <div class="col-lg-4">
                    <p class="fz-14 f-r"> <small>PRICE</small> </p>
                </div>

                @foreach($items as $item)

                <div class="col-lg-3">
                    @if($item->status=='Active')
                    <a href="javascript:" title="Cancel Slot" onclick="cancelSlot({{ $item->id }}, {{$data->facility_id}})" class="cancel-btn btn-sm btn btn-outline-danger">Cancel</a> 
                    @else
                        <small class="text-danger fz-11"> <b>Cancelled</b> </small>
                    @endif                
                </div>
                <div class="col-lg-5">
                    <p class="txt-black fz-14 -m-b">{{ date("d M Y", strtotime($item->slot_date)); }}  <br> <small class="fz-11">{{ $item->slot->label ?? '' }}</small> </p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> ₹{{ number_format($data->facility_amount, 2) }} </p>
                </div>

                @endforeach

            </div>

            <hr>

            <div class="row">

                <div class="col-lg-8">
                    <p class="fz-14"> <small>PLAYER NAME</small> </p>
                </div>
                <div class="col-lg-4">
                    <p class="fz-14 f-r"> <small>CHARGE</small> </p>
                </div>

                @foreach($guests as $guest)

                    <div class="col-lg-12">
                        <p class="txt-black fz-14">
                            <small> 
                                @if($guest->status=='Cancelled')
                                <del>{{ date("d", strtotime($guest->slot_date)) }} {{ date("D", strtotime($guest->slot_date)) }} {{ $guest->slot->label ?? '' }}</del>
                                @else
                                {{ date("d", strtotime($guest->slot_date)) }} {{ date("D", strtotime($guest->slot_date)) }} {{ $guest->slot->label ?? '' }}
                                @endif
                            </small>
                        </p>
                    </div>

                    <?php $plyrs = App\Models\GameBookingGuest::where('game_booking_id', $guest->game_booking_id)->where('slot_id', $guest->slot_id)->whereDate('slot_date', $guest->slot_date)->get(); ?>

                    @foreach($plyrs as $plyr)
                    
                    <div class="col-lg-8">
                        <p class="txt-black fz-14"> 
                            @if($guest->status=='Cancelled')
                            <del>{{ $plyr->player_name }}</del>
                            @else
                            {{ $plyr->player_name }} 
                            @endif
                        </p>
                    </div>
                    <div class="col-lg-4">
                        <p class="txt-black fz-14 f-r"> 
                            @if($guest->status=='Cancelled')
                            <del>₹{{ $plyr->occupant_charge?number_format($plyr->occupant_charge, 2):'0' }}</del>
                            @else
                            ₹{{ $plyr->occupant_charge?number_format($plyr->occupant_charge, 2):'0' }} 
                            @endif
                        </p>
                    </div>

                    @endforeach

                @endforeach

            </div>

            <hr>

            <div class="row">
                
                <div class="col-lg-8">
                    <p class="txt-black fz-14"> Sub Total</p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> ₹{{ number_format(($data->facility_amount)*count($items)+($data->guest_total_amount), 2) }} </p>
                </div>

                <div class="col-lg-8">
                    <p class="txt-black fz-14"> GST ({{$data->facility_gst_per}}%) </p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> ₹{{ number_format($data->facility_gst_amt, 2) }} </p>
                </div>

            </div>

            <hr>

            <div class="row">

                <div class="col-lg-8">
                    <p class="txt-black fz-14"> <b>Total Amount Paid</b> </p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> <b>₹{{ number_format($data->facility_total, 2) }}</b> </p>
                </div>

                <div class="col-lg-8">
                    <p class="txt-black fz-14"> <b>Cancellation Charges <span class="text-danger">(-)</span> </b> </p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> <b>₹{{ number_format($cancel_sum, 2) }}</b> </p>
                </div>

                <div class="col-lg-8">
                    <p class="txt-black fz-14"> <b>Total Amount</b> </p>
                </div>
                <div class="col-lg-4">
                    <p class="txt-black fz-14 f-r"> <b>₹{{ number_format(($data->facility_total)-($cancel_sum), 2) }}</b> </p>
                </div>

            </div>

        </div>

        <div class="summary-box mt-4">

            <h5 class="payment-title mt-2">Booked By</h5>

            <div class="row mt-2">

                <div class="col-lg-6">
                    <p class="fz-14"> Booking No. </p>
                </div>
                <div class="col-lg-6">
                    <p class="fz-14">: {{ $data->booking_number }} </p>
                </div>

                <div class="col-lg-6">
                    <p class="fz-14"> Booking Created Date </p>
                </div>
                <div class="col-lg-6">
                    <p class="fz-14">: {{ date("d-m-Y", strtotime($data->created_at)) }} </p>
                </div>

                <div class="col-lg-6">
                    <p class="fz-14"> Name </p>
                </div>
                <div class="col-lg-6">
                    <p class="fz-14">: {{ $member->DisplayName }} </p>
                </div>

                <div class="col-lg-6">
                    <p class="fz-14"> Mobile </p>
                </div>
                <div class="col-lg-6">
                    <p class="fz-14">: {{ $member->Mobile }} </p>
                </div>

                <div class="col-lg-6">
                    <p class="fz-14"> Email </p>
                </div>
                <div class="col-lg-6">
                    <p class="fz-14">: {{ $member->Email }} </p>
                </div>

            </div>

        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-lg-6">
                <p class="txt-black fz-14"> <b>₹{{ number_format(($data->facility_total)-($cancel_sum), 2) }}</b> </p>
            </div>
            <div class="col-lg-6">
                <a href="{{ route('activity.booking') }}"><button class="btn-sm btn btn-success f-e">View Venue</button></a>
            </div>
        </div>
    </div>
</div>