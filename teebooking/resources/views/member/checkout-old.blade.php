@extends('layouts.member')
@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-reservation">
        <div class="row">
            <div class="col-md-8">
                <div class="single-reservation bg-white base-padding">
                    <div class="single-reservation-item">
                        <div class="single-reservation-flex">
                            <div class="single-reservation-name">
                                <h5 class="single-reservation-name-title"> {{ $roomBookingData->room_type}} </h5>
                                <p class="single-reservation-name-para">
                                    Occupant Type : {{ $roomBookingData->occupant_types_name}}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="single-reservation-item">
                        <div class="single-reservation-details">
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Check in </span>
                                <h5 class="single-reservation-details-title"> {{ $roomBookingData->checkin}} </h5>
                            </div>
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Check Out </span>
                                <h5 class="single-reservation-details-title"> {{ $roomBookingData->checkout}} </h5>
                            </div>
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Occupant Type / Name 1 </span>
                                <h5 class="single-reservation-details-title"> {{ $roomBookingData->occupant_type_1}} /
                                    {{ $roomBookingData->occupant_name_1}} </h5>
                            </div>
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Occupant Type / Name 1 </span>
                                <h5 class="single-reservation-details-title"> {{ $roomBookingData->occupant_type_2}} /
                                    {{ $roomBookingData->occupant_name_2}} </h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="checkout-single bg-white radius-10 mt-4">
                    <h4 class="checkout-title"> Payment </h4>
                    <div class="checkout-contents mt-4">
                        <div class="custom-radio custom-radio-inline">
                            <div class="custom-radio-single active">
                                <input class="radio-input" type="radio" id="radio1" name="size" checked="checked">
                                <label for="radio1"> <img src="{{ asset('public/member/assets/img/icons/card.svg') }}"
                                        alt="card"> Credit/Dabit
                                    Card</label>
                            </div>
                            <div class="custom-radio-single">
                                <input class="radio-input" type="radio" name="size" id="radio2">
                                <label for="radio2"> <img src="{{ asset('public/member/assets/img/icons/paypal.svg') }}"
                                        alt="Paypal"> Paypal</label>
                            </div>
                        </div>
                        <div class="checkout-form custom-form">
                            <form action="#">
                                {{-- <div class="single-input mt-4">
                                    <label class="label-title"> Card Number </label>
                                    <input class="form--control input-padding-left" type="text" name="name"
                                        placeholder="Type Card Number">
                                    <div class="input-icon"> <img src="assets/img/icons/card.svg" alt="Icon"> </div>
                                </div>
                                <div class="input-flex-item">
                                    <div class="single-input mt-4">
                                        <label class="label-title"> Expire Date </label>
                                        <input class="form--control input-padding-left date-picker flatpickr-input"
                                            placeholder="Select Expire Date" type="hidden"><input
                                            class="form--control input-padding-left date-picker form-control input"
                                            placeholder="Select Expire Date" tabindex="0" type="text"
                                            readonly="readonly">
                                        <div class="input-icon"> <img src="assets/img/icons/calendar.svg" alt="Icon">
                                        </div>
                                    </div>
                                    <div class="single-input mt-4">
                                        <label class="label-title"> CVV/CVC </label>
                                        <input class="form--control input-padding-left" type="number" name="name"
                                            placeholder="Type CVV/CVC">
                                        <div class="input-icon"> <img src="assets/img/icons/cvc.svg" alt="Icon"> </div>
                                    </div>
                                </div>
                                <div class="lock-contents mt-4">
                                    <div class="lock-contents-icon">
                                        <img src="assets/img/icons/lock.svg" alt="Icon">
                                    </div>
                                    <span class="lock-contents-para"> Information are encrypted with 256 bit SSL </span>
                                </div>
                                <div class="guaranty-cancellation radius-10 mt-4">
                                    <h4 class="guaranty-cancellation-title"> Guarantee &amp; Cancellation </h4>
                                    <p class="guaranty-cancellation-para"> Cancel 12 hours before checking-in time for a
                                        Free
                                        Cancellation. </p>
                                </div>
                                <div class="checkbox-wrap mt-4">
                                    <div class="checkbox-inline">
                                        <input class="check-input" type="checkbox" id="agree">
                                        <label class="checkbox-label" for="agree"> I agree to the <a
                                                href="javascript:void(0)">Terms
                                                &amp; Conditions</a> of <a href="javascript:void(0)">Beyond Hotels</a>
                                        </label>
                                    </div>
                                </div> --}}
                                <div class="single-reservation-flex mt-4">
                                    <div class="btn-wrapper">
                                        <a href="javascript:void(0)" class="cmn-btn btn-bg-1 btn-small">
                                            Pay &amp; Confirm
                                        </a>
                                    </div>
                                    <div class="single-reservation-attachment">
                                        <div class="single-reservation-attachment-list">
                                            <a href="javascript:void(0)"
                                                class="single-reservation-attachment-list-item">
                                                Cancel
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="checkout-widget checkout-widget-padding widget bg-white radius-10">
                    <div class="checkout-sidebar">
                        <h4 class="checkout-sidebar-title"> Invoice </h4>
                        <div class="checkout-sidebar-contents">
                            <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                <li class="list">
                                    <span class="regular"> Rent/Night </span>
                                    <span class="strong">
                                        <i class="las la-rupee-sign"></i>{{ $roomBookingData->price}}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular"> Nights </span>
                                    <span class="strong"> {{ $roomBookingData->total_nights}}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular"> Rooms </span>
                                    <span class="strong"> {{ $roomBookingData->total_rooms}}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular"> Gst </span>
                                    <span class="strong"> (+0%) <i class="las la-rupee-sign"></i>0 </span>
                                </li>
                            </ul>
                            <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                <li class="list">
                                    <span class="regular"> Total </span>
                                    <span class="strong color-one fs-20">
                                        <i class="las la-rupee-sign"></i>{{ $roomBookingData->price}}
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection