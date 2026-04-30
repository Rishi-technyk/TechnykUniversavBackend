@extends('layouts.member')
@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-reservation">
        <div class="hotel-view bg-white radius-20">
            <div class="hotel-view-flex">
                <a href="hotel_details.html" class="hotel-view-thumb hotel-view-list-thumb bg-image" style="
                                                  background-image: url({{ asset('public/member/assets/img/single-page/hotel-list1.jpg') }});
                                                ">
                </a>
                <div class="hotel-view-contents">
                    <div class="hotel-view-contents-header">
                        <div
                            class="hotel-view-contents-header-flex d-flex flex-wrap gap-3 align-items-center justify-content-between">
                            <span class="hotel-view-contents-review">

                                <span class="hotel-view-contents-review-count">
                                    {{$room->available_rooms}} Rooms Left !
                                </span>
                            </span>
                        </div>
                        <h3 class="hotel-view-contents-title">
                            {{$room->title}}
                        </h3>
                        <div class="hotel-view-contents-location mt-2">
                            <span class="hotel-view-contents-location-para">
                                {{$room->short_description}}
                            </span>
                        </div>
                    </div>
                    <div class="hotel-view-contents-middle">
                        <div class="hotel-view-contents-flex">
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <span> <i class="las la-parking"></i> </span>
                                <p class="hotel-view-contents-icon-title flex-fill">
                                    Parking
                                </p>
                            </div>
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <span> <i class="las la-wifi"></i> </span>
                                <p class="hotel-view-contents-icon-title flex-fill">
                                    Wifi
                                </p>
                            </div>
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <span> <i class="las la-coffee"></i> </span>
                                <p class="hotel-view-contents-icon-title flex-fill">
                                    Breakfast
                                </p>
                            </div>
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <span>
                                    <i class="las la-swimming-pool"></i>
                                </span>
                                <p class="hotel-view-contents-icon-title flex-fill">
                                    Pool
                                </p>
                            </div>
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <span> <i class="las la-dumbbell"></i> </span>
                                <p class="hotel-view-contents-icon-title flex-fill">
                                    Gym
                                </p>
                            </div>
                            <div class="hotel-view-contents-icon d-flex gap-1">
                                <a class="hotel-view-contents-icon-more" href="javascript:void(0)">
                                    +8 More
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="hotel-view-contents-bottom">
                        <div class="hotel-view-contents-bottom-flex">
                            <div class="hotel-view-contents-bottom-contents d-flex flex-wrap gap-4 gap-sm-1">

                                @foreach($room->roomPrices as $price)
                                <h4 class="hotel-view-contents-bottom-title">
                                    <i class="las la-rupee-sign"></i>{{$price->price}}
                                    <sub>/{{$price->occupants->name}}</sub>
                                </h4>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row pt-4">
            <div class="col-md-8">
                <div class="hotel-details-widget hotel-details-widget-padding widget bg-white radius-10">
                    <div class="dashboard-reservation">
                        <div class="single-reservation bg-white base-padding">
                            <div class="custom--form dashboard-form">
                                @if ($message = Session::get('error'))
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>{{ $message }}</strong>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                                </div>
                                @endif
                                <form action="{{route('checkoutStore') }}" method="post">
                                    @csrf
                                    <input type="hidden" value="{{ $room->id }}" name="room_id" id="room_id" />
                                    <div class="dashboard-flex-input">
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> Rooms to be booked for
                                            </label>
                                            <input type="number" name="total_rooms" id="roomcount" class="form--control"
                                                value="1" min="1" max="5" onchange="validateRoomBooking()" required />
                                            {{-- @if($errors->has('total_rooms'))
                                            <div class="text-danger">
                                                {{ $errors->first('total_rooms') }}
                                            </div>
                                            @endif --}}
                                        </div>
                                        <div class="dashboard-input mt-4">
                                            <label class="popup-contents-select-label"> Rooms to be booked for
                                            </label>
                                            <select class="lg-select" name="occupant_type_id" id="occupantId"
                                                onchange="validateRoomBooking()">
                                                <option value="">Select One</option>
                                                <option value="1">Member</option>
                                                <option value="2">Guest Civil</option>
                                                <option value="3">Guest Defence</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="dashboard-flex-input">
                                        <div class="dashboard-input mt-4">
                                            <label class="popup-contents-select-label"> To be booked for
                                            </label>
                                            <select name="occupant_type_1" class="lg-select occupent-type" disabled>
                                                <option value="">Select One</option>
                                            </select>

                                        </div>
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> Occupant Name </label>
                                            <input type="text" name="occupant_name_1" class="form--control"
                                                placeholder="Name" required>
                                        </div>
                                    </div>
                                    <div class="dashboard-flex-input">
                                        <div class="dashboard-input mt-4">
                                            <label class="popup-contents-select-label"> To be booked for
                                            </label>
                                            <div class="">
                                                <select name="occupant_type_2" class="lg-select occupent-type" disabled>
                                                    <option value="">Select One</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="dashboard-input mt-4">
                                            <label class="label-title"> Occupant Name </label>
                                            <input type="text" name="occupant_name_2" class="form--control"
                                                placeholder="Name" required>
                                        </div>
                                    </div>
                                    <div class="btn-wrapper mt-4">
                                        <button type="submit" class="cmn-btn" id="goToCheckout" disabled>
                                            Go to
                                            Checkout
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="checkout-widget checkout-widget-padding widget bg-white radius-10">
                    <div class="checkout-sidebar">
                        <h4 class="checkout-sidebar-title"> Summary </h4>
                        <div class="checkout-sidebar-contents">
                            <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                <li class="list">
                                    <span class="regular"> Checking In </span>
                                    <span class="strong"> {{ date('d-m-Y', strtotime(session('checkin'))) }}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular"> Checking Out </span>
                                    <span class="strong"> {{ date('d-m-Y', strtotime(session('checkout'))) }}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular"> Nights </span>
                                    <span class="strong">
                                        @php
                                        $diff = abs(strtotime(session('checkout')) -
                                        strtotime(session('checkin')));

                                        $years = floor($diff / (365*60*60*24));
                                        $months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
                                        echo $days = floor(($diff - $years * 365*60*60*24 -
                                        $months*30*60*60*24)/
                                        (60*60*24));
                                        @endphp

                                    </span>
                                </li>
                                {{-- <li class="list">
                                    <span class="regular"> Price </span>
                                    <span class="strong" id="price"> $230.00 </span>
                                </li>

                                <li class="list"> <span class="regular"> Vat </span> <span class="strong">
                                        (+13%) $20.08 </span> </li> --}}
                            </ul>
                            {{-- <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                <li class="list"> <span class="regular"> Total </span> <span
                                        class="strong color-one fs-20">
                                        $250.08
                                    </span> </li>
                            </ul> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    async function validateRoomBooking() {
        console.log('called');
        let room_id = $("#room_id").val();
        let occupantId = $("#occupantId").val();
        let roomCount = $("#roomcount").val();
        console.log(roomCount);
        const response = await $.ajax({
            url: "{{ url('/validate-room-booking') }}",
            method: "get",
            data: { room_id: room_id, occupantId : occupantId, roomCount : roomCount }
        });
        if (response.status) {
            $(".occupent-type").prop('disabled', false);
            //console.log(response.data);
            let html = '';
            $.each(response.options, function(index, item) {
                console.log(item);
                html += `<option value='${item.option}'>${item.option}</option>`;
                $(".occupent-type").html(html);
            });
            // console.log()
            $("#price").text(`${response.price_details.price}`)
            /* $("#max-night").val(item.price.occupants.max_nights);
            $("#max-rooms").val(item.price.occupants.max_rooms) */
            $("#goToCheckout").attr("disabled", false);
            $("#goToCheckout").addClass("btn-bg-1");
        } else {
            toastr.error(response.message);
            $("#goToCheckout").attr("disabled", true);
            $("#goToCheckout").removeClass("btn-bg-1");
        }
    }
</script>
@endsection