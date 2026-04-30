@extends('layouts.member')

{{-- @section('sidebar')
@include('partials.member.sidebar')
@endsection; --}}

@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-promo">
        <div class="hotel-details-widget hotel-details-widget-padding widget bg-white radius-10">
            <div class="details-sidebar">
                <div class="details-sidebar-dropdown custom-form">
                    <form method="get" action="{{ route('rooms') }}" class="row g-3">
                        <div class="col-sm-5">
                            <div class="single-input">
                                <span class="banner-location-single-contents-subtitle"> Check In </span>
                                <input name="checkin" class="form--control checkin" type="text"
                                    value="{{ date('d-m-Y', strtotime(session('checkin'))) }}" placeholder="Check in"
                                    required />
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <div class="single-input">
                                <span class="banner-location-single-contents-subtitle"> Check Out </span>
                                <input name="checkout" class="form--control checkout" type="text"
                                    placeholder="Check out" required
                                    value="{{ date('d-m-Y', strtotime(session('checkout'))) }}" />
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="btn-wrapper">
                                <button type="submit" class="cmn-btn btn-bg-1"
                                    style="padding: 9px 26px; margin-top: 26px">
                                    <i class="las la-search"></i> &nbsp; Search
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="grid-list-contents-flex">
            <p class="grid-list-contents-para">
                Showing {{ (count($rooms))? 1 : 0 }}-{{ count($rooms) }} of {{
                count($rooms) }}
                results
            </p>
        </div>
    </div>


    <div class="row gy-4">
        <div class="col-md-8">
            {{-- search result --}}
            @forelse($rooms as $room)
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="hotel-view bg-white radius-20">
                        <div class="hotel-view-flex">
                            <a href="#" class="hotel-view-thumb hotel-view-list-thumb bg-image"
                                style="background-image: url({{ asset('public/member/assets/img/single-page/hotel-list1.jpg') }});">
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
                                        @forelse($room->roomAmenity as $amenity)
                                        <div class="hotel-view-contents-icon d-flex gap-1">
                                            <span> <i class="{{ $amenity->icon }}"></i> </span>
                                            <p class="hotel-view-contents-icon-title flex-fill">
                                                {{ $amenity->name }}
                                            </p>
                                        </div>
                                        @empty
                                        <span></span>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="hotel-view-contents-bottom">
                                    <div class="btn-wrapper">
                                        <a href="#" class="cmn-btn btn-outline-1 color-one float-end"
                                            data-bs-toggle="modal" data-bs-target="#guestDetailsModal"
                                            data-max_guest="{{$room->max_guest}}" data-id="{{$room->id}}"
                                            data-title="{{$room->title}}">
                                            Book This Room
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <p>No rooms found.</p>
            @endforelse
            {{-- .search result --}}
        </div>

        {{-- summary --}}
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12">
                    <div class="checkout-widget checkout-widget-padding widget bg-white radius-10">
                        <div class="checkout-sidebar pb-4">
                            <h4 class="checkout-sidebar-title"> Summary </h4>
                            {{-- @php
                            echo "
                            <pre>";
                            print_r(session('room_cart'));
                            @endphp --}}
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

                                </ul>
                                @if(session()->has('room_cart'))
                                <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                    <li class="list">
                                        <span class="regular"> Days </span>
                                        <span class="strong"> {{ session('room_cart')['days'] }}
                                        </span>
                                    </li>
                                </ul>
                                <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                        @forelse(session('room_cart')['rooms'] as $room_cart)
                                        <li class="list">
                                            <span class="regular">
                                                {{ $room_cart['room_title'] }} <br />
                                                ({{ $room_cart['occupant_type'] }})
                                            </span>
                                            <span class="strong"> <i class="las la-rupee-sign"></i> {{ $room_cart['price'] }} </span>
                                        </li>
                                        @empty
                                        <li class="list">
                                            <span class="regular">You have not select any room yet!</span>
                                        </li>
                                        @endforelse
                                </ul>
                                @else
                                <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                    <li class="list">
                                        <span class="regular">You have not select any room yet!</span>
                                    </li>
                                </ul>
                                @endif
                                @if(session()->has('room_cart'))
                                <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                    <li class="list"> <span class="regular"> Total </span> <span
                                            class="strong color-one fs-20">
                                            <i class="las la-rupee-sign"></i>
                                            @php echo array_sum(array_map(fn ($item) => $item['price'], session('room_cart')['rooms'])); @endphp
                                        </span> </li>
                                </ul>
                                <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                    <div class="btn-wrapper mt-2 pb-4">
                                        <a href="{{ route('checkout') }}" class="cmn-btn btn-bg-1 float-end"> Continue </a>
                                    </div>
                                </ul>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        {{-- .summary --}}
    </div>

    {{-- popup --}}
    <div class="popup-m">
        <div class="modal fade" id="guestDetailsModal" tabindex="-1" aria-labelledby="guestDetailsModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="guestDetailsModalStaticBackdropLabel">Single Room</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="dashboard-reservation">
                            <div class="single-reservation bg-white base-padding show open">
                                <div class="single-reservation-inner">
                                    <div class="custom--form dashboard-form">
                                        <form action="{{ route('addToList') }}" method="post" id="form_add_guest">
                                            @csrf
                                            <input type="hidden" name="room_id" id="room_id">
                                            <div class="row g-3 mb-4">
                                                <div class="col-md-4">
                                                    <div class="dashboard-input">
                                                        <label class="label-title"> Booked for
                                                        </label>
                                                        <select class="lg-select form--control" name="occupant_type_id"
                                                            id="occupant_type_id" {{--onchange="validateRoomBooking()"
                                                            --}}>
                                                            <option value="">Select Booked for</option>
                                                            <option value="1">Member</option>
                                                            <option value="2">Guest Civil</option>
                                                            <option value="3">Guest Defence</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="div_guest_details" style="border-top: 2px solid #f3f3f3; "
                                                class="pb-2 pt-4">
                                            </div>
                                            <div style="border-top: 2px solid #f3f3f3; "
                                                class="pb-2 pt-2">
                                                <button type="button" class="btn btn-sm primary-pill" id="btn_add_guest">
                                                    <i class="las la-plus"></i> Add Guest
                                                </button>
                                            </div>
                                            <div class="single-reservation-item" id="div_guest_add_btn">
                                                <div class="single-reservation-flex">
                                                    <div class="single-reservation-content">

                                                        <span class="single-reservation-content-price" id="room_booked_for_rate">

                                                        </span>
                                                    </div>
                                                    <div class="single-reservation-logoPrice">
                                                        <div class="btn-wrapper">
                                                            <button type="submit" class="cmn-btn btn-bg-1">
                                                                Add to List
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- .popup --}}

</div>
@endsection
{{-- .main body --}}

@section('script')

<script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js">
</script>

<script>
    $(function() {
        $('#guestDetailsModal').on('hidden.bs.modal', function (e) {
            // do something...
            $("#occupant_type_id").val("").change();
            $("#form_add_guest")[0].reset();
            // $.fn.build_guest_fields();
        })

        $('#guestDetailsModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget) // Button that triggered the modal
            // var recipient = button.data('whatever') // Extract info from data-* attributes
            // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
            // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
            var modal = $(this)

            $("#guestDetailsModalStaticBackdropLabel").html(button.data('title'))
            $("#room_id").val(button.data('id'))
            window.maxGuest = button.data('max_guest')

            // modal.find('.modal-title').text('New message to ' + recipient)
            // modal.find('.modal-body input').val(recipient)
        })

        $.fn.build_guest_fields = function() {
            let room_id = $("#room_id").val();
            let max_guest = window.maxGuest;
            let occupant_type_id = $("#occupant_type_id").val();
            if(occupant_type_id == '') {
                $("#div_guest_details").hide().html("");
                $("#div_guest_add_btn").hide();
                $("#btn_add_guest").hide();
            }
            else if($("#div_guest_details").children().length >= max_guest) {
                //alert('Maximum '+ max_guest + ' Guest Allowed')
                toastr.error('Maximum '+ max_guest + ' Guest Allowed');
            }
            else {
                $("#div_guest_details").LoadingOverlay("show");
                $("#div_guest_details").show();
                $("#div_guest_add_btn").show();
                $("#btn_add_guest").show();
                const response = $.ajax({
                    url: "{{ url('/validate-room-booking') }}",
                    method: "get",
                    data: {
                        room_id: room_id,
                        occupant_id: occupant_type_id,
                    },
                    //processData: false,
                    //contentType: false,
                    //cache: false,
                    //async:false,
                    success: function (result, textStatus, xhr) {
                        //console.log(result.price_details.price)

                        // ${user.name}
                        $("#room_booked_for_rate").html(`
                            <i class="las la-rupee-sign"></i><span>${result.price_details.price}</span><sub class="fw-light">/Night</sub>
                        `);

                        let occupant_type_option_html = "";
                        $.each(result.options, function(index, item) {
                            //console.log(item);
                            occupant_type_option_html += `<option value='${item.id}'>${item.option}</option>`;
                        });

                        let add_delete_button_html = "";

                        let html_text = `
                                    <div class="row g-3 mb-2 pb-4 align-items-center ">
                                        <div class="col-md-3">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Guest Type
                                                </label>
                                                <select class="lg-select form-select" name="occupant_type[]" required>
                                                    <option value="">---Guest Type---</option>
                                                    ${occupant_type_option_html}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Name </label>
                                                <input type="text" name="occupant_name[]" class="form--control "
                                                    placeholder="Guest Name" pattern="^[A-Za-z ]+$" oninvalid="setCustomValidity('Please enter a valid name.')" oninput="this.setCustomValidity('')" required onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode<123) || (event.charCode == 32)" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Mobile </label>
                                                <input type="text" name="occupant_mobile[]" class="form--control input_mobile"
                                                    placeholder="Guest Mobile" pattern="[6789][0-9]{9}" oninvalid="setCustomValidity('Please enter a valid mobile number.')" oninput="this.setCustomValidity('')" required minlength="10" maxlength="10">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="dashboard-input mt-4">
                                                <button type="button" class="btn btn-sm float-end text-danger btn_remove_guest">
                                                    <i class="las la-trash"></i> Remove
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    `;

                        $("#div_guest_details").append(html_text);
                        $("#div_guest_details").LoadingOverlay("hide", true);
                    }
                });
            }

            // $.LoadingOverlay("hide", true);
        }

        $( "#occupant_type_id" ).on( "change", function() {
            $("#div_guest_details").html('');
            $.fn.build_guest_fields();
        });

        $(document).on("click", "#btn_add_guest", function(){
            $.fn.build_guest_fields();
        });

        $(document).on("submit", "#form_add_guest", function(){
            if($("#div_guest_details").children().length < 1) {
                toastr.error('Minimum one guest required.');
                return false;
            }
        });

        $(document).on("click", ".btn_remove_guest", function(){
            // if($("#div_guest_details").children().length > 1) {
                $(this).closest('.row').remove();
            // }
            // else {
            //     toastr.error('Minimum one guest required.');
            // }

        });

        $.fn.build_guest_fields();





        $(document).on("keyup", ".input_mobile",function(e)
        {
            if (/\D/g.test(this.value))
            {
                // Filter non-digits from input value.
                this.value = this.value.replace(/\D/g, '');
            }
        });

    });
</script>


@endsection