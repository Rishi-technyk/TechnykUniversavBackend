@extends('layouts.member')
@section('content')
{{-- @php
echo "
<pre>";
print_r($room_cart);
die;
@endphp --}}

<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-reservation">
        @if(!empty($room_cart))
        <div class="row">
            <div class="col-md-8">
                <div class="single-reservation bg-white base-padding">

                    <div class="single-reservation-item">
                        <div class="single-reservation-details">
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Check in </span>
                                <h5 class="single-reservation-details-title"> {{$room_cart['checkin']}} </h5>
                            </div>
                            <div class="single-reservation-details-item">
                                <span class="single-reservation-details-subtitle"> Check Out </span>
                                <h5 class="single-reservation-details-title"> {{$room_cart['checkout']}} </h5>
                            </div>

                        </div>
                    </div>
                    {{-- rooms --}}
                    @foreach($room_cart['rooms'] as $key1 => $room)
                    <div class="single-reservation-item">
                        <div class="single-reservation-flex">
                            <div class="single-reservation-name">
                                <h5 class="single-reservation-name-title"> {{$room['room_title'] }} </h5>
                                <p class="single-reservation-name-para">
                                    Occupant - {{$room['occupant_type'] }}
                                </p>
                            </div>
                            {{-- <div class="single-reservation-btn">
                                 <a href="javascript:void(0)" class="dash-btn popup-click">
                                     <i class="las la-exclamation-circle"></i> Cancel?
                                </a>
                            </div> --}}
                        </div>
                        <table class="table table-borderless mt-2">
                            <thead>
                                <tr>
                                    <th scope="col">Guest Type</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Mobile</th>
                                    <th scope="col" class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($room['occupants'] as $key2 => $occupant)
                                <tr>
                                    <td>{{$occupant['type_name'] }}</td>
                                    <td>{{$occupant['name'] }}</td>
                                    <td>{{$occupant['mobile'] }}</td>
                                    <td class="text-center">
                                        <a class="text-danger confirm" data-title="Confirm!" data-content="Are you sure want to remove the guest?" href="{{ route('deleteOccupantsFromList', ['id'=>encrypt($key1.'.'.$key2)]) }}">
                                            <i class="las la-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @if(count($room['occupants']) < $room['max_guest'])
                        <div style="border-top: 2px solid #f3f3f3; " class="pb-2 pt-2">
                            <a href="#" class="btn btn-sm primary-pill" data-bs-toggle="modal" data-bs-target="#guestDetailsModal"
                                data-id="" data-room_id="{{ $room['room_id'] }}" data-occupant_id="{{ $room['occupant_type_id'] }}" data-title="{{ $room['room_title'] }}" data-room_cart_key="{{ encrypt($key1) }}" data-max_guest="{{$room['max_guest']}}">
                                <i class="las la-plus"></i> Add Guest
                            </a>
                        </div>
                        @else
                        <a href="#" class="btn btn-sm disabled-pill" id="guestDetailsModalMax" data-max_guest="{{$room['max_guest']}}">
                            <i class="las la-plus"></i> Add Guest
                        </a>
                        @endif
                        <hr />
                        <div class="single-reservation-flex">
                            <div class="single-reservation-content">
                                <h5 class="single-reservation-content-title"> Price </h5>
                            </div>
                            <div class="single-reservation-logoPrice">
                                <span class="single-reservation-logoPrice-code"> <i class="las la-rupee-sign"></i>
                                    {{ $room['price'] }}</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    {{-- rooms --}}
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
                                        <a href="{{ route('rooms') }}" class="cmn-btn btn-outline-1 color-one btn-small"> Add More Room </a>
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
                                    <span class="regular"> Nights </span>
                                    <span class="strong">
                                        {{$room_cart['days']}}
                                    </span>
                                </li>
                                {{-- <li class="list">
                                    <span class="regular">Single room<br /> (Member)</span>
                                    <span class="strong"> <i class="las la-rupee-sign"></i>{{ $room_cart['price'] }}
                                    </span>
                                </li>
                                <li class="list">
                                    <span class="regular">Double room<br /> (Guest)</span>
                                    <span class="strong"> <i class="las la-rupee-sign"></i>1500
                                    </span>
                                </li> --}}
                                @foreach(session('room_cart')['rooms'] as $room_cart)
                                <li class="list">
                                    <span class="regular">
                                        {{ $room_cart['room_title'] }} <br />
                                        ({{ $room_cart['occupant_type'] }})
                                    </span>
                                    <span class="strong"> <i class="las la-rupee-sign"></i> {{ $room_cart['price'] }}
                                    </span>
                                </li>
                                @endforeach
                                <li class="list">
                                    <span class="regular"> Gst </span>
                                    <span class="strong"> (+0%) <i class="las la-rupee-sign"></i>0 </span>
                                </li>
                            </ul>
                            <ul class="checkout-flex-list list-style-none checkout-border-top pt-3 mt-3">
                                <li class="list">
                                    <span class="regular"> Total </span>
                                    <span class="strong color-one fs-20">
                                        <i class="las la-rupee-sign"></i>@php echo array_sum(array_map(fn ($item) =>
                                        $item['price'], session('room_cart')['rooms'])); @endphp
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
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
                                            <form action="{{ route('addOccupantsToList') }}" method="post" id="form_add_guest">
                                                @csrf
                                                <input type="hidden" name="room_cart_key" id="room_cart_key" />
                                                <div class="row g-3 mb-2 pb-4 align-items-center " id="div_guest_details">

                                                </div>
                                                <div class="single-reservation-item" id="div_guest_add_btn">
                                                    <div class="single-reservation-flex">
                                                        <div class="single-reservation-content">
                                                        </div>
                                                        <div class="single-reservation-logoPrice">
                                                            <div class="btn-wrapper">
                                                                <button type="submit" class="cmn-btn btn-bg-1">
                                                                    Add Guest
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
        @else
        <div class="row">
            <div class="col-md-12">
                <section class="confirmation-area section-bg-2 pat-50 pab-50">
                    <div class="container">
                        <div class="confirmation-contents center-text">
                            <div class="confirmation-contents-icon">
                                <i class="las la-meh"></i>
                            </div>
                            <h4 class="confirmation-contents-title"> Sorry! Your room cart is empty. </h4>
                            <p class="confirmation-contents-para"> Dear {{Auth()->user()->DisplayName}}, for reserve a room, please go to our Book Room section and select appropriate rooms. Thank You!
                            </p>
                            <div class="btn-wrapper flex-btn mt-4 mt-lg-5">
                                <a href="{{ route('dashboard') }}" class="cmn-btn btn-outline-1 color-one"> Back to Home </a>
                                <a href="{{ route('rooms') }}" class="cmn-btn btn-bg-1"> <span class="icon"><i
                                            class="las la-home"></i></span> Book Room </a>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection


@section('script')

    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js">
    </script>
    <script>
        $(function() {

            $('#guestDetailsModal').on('show.bs.modal', function (event) {

                var button = $(event.relatedTarget) // Button that triggered the modal
                // var recipient = button.data('whatever') // Extract info from data-* attributes
                // If necessary, you could initiate an AJAX request here (and then do the updating in a callback).
                // Update the modal's content. We'll use jQuery here, but you could use a data binding library or other methods instead.
                var modal = $(this)

                $("#guestDetailsModalStaticBackdropLabel").html(button.data('title'));
                $("#room_cart_key").val(button.data('room_cart_key'));

                let val_room_id = button.data('room_id');
                let val_occupant_id = button.data('occupant_id');

                $("#div_guest_details").LoadingOverlay("show");
                const response = $.ajax({
                    url: "{{ url('/validate-room-booking') }}",
                    method: "get",
                    data: {
                        room_id: val_room_id,
                        occupant_id: val_occupant_id,
                    },
                    //processData: false,
                    //contentType: false,
                    //cache: false,
                    //async:false,
                    success: function (result, textStatus, xhr) {
                        //console.log(result.price_details.price)

                        let occupant_type_option_html = "";
                        $.each(result.options, function(index, item) {
                            //console.log(item);
                            occupant_type_option_html += `<option value='${item.id}'>${item.option}</option>`;
                        });

                        let add_delete_button_html = "";

                        let html_text = `
                                        <div class="col-md-4">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Guest Type
                                                </label>
                                                <select class="lg-select form-select" name="occupant_type" required>
                                                    <option value="">---Guest Type---</option>
                                                    ${occupant_type_option_html}
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Name </label>
                                                <input type="text" name="occupant_name" class="form--control "
                                                    placeholder="Guest Name" pattern="^[A-Za-z ]+$" oninvalid="setCustomValidity('Please enter a valid name.')" oninput="this.setCustomValidity('')" required onkeypress="return (event.charCode > 64 && event.charCode < 91) || (event.charCode > 96 && event.charCode<123) || (event.charCode == 32)" />
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="dashboard-input">
                                                <label class="label-title"> Mobile </label>
                                                <input type="text" name="occupant_mobile" class="form--control input_mobile"
                                                    placeholder="Guest Mobile" pattern="[6789][0-9]{9}" oninvalid="setCustomValidity('Please enter a valid mobile number.')" oninput="this.setCustomValidity('')" required minlength="10" maxlength="10">
                                            </div>
                                        </div>
                                    `;

                        $("#div_guest_details").append(html_text);
                        $("#div_guest_details").LoadingOverlay("hide", true);
                    }
                });



            });
            
            $('#guestDetailsModalMax').on('click', function(event){
                let max_guest = $(this).data('max_guest');
                toastr.error('Maximum '+ max_guest + ' Guests Allowed');
            });

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