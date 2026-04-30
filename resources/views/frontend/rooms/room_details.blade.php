@extends('frontend.layouts.app')

@section('title', 'Room Details')

@section('content')

<!-- Room Details Section Begin -->
<section class="room-details-section">
    <div class="container">
        <!-- Breadcrumb Section Begin -->
        <div class="card-section-title-box">
            <span class="card-section-bar"></span>
            <h4 class="card-section-title">Room Details</h4>
        </div>
        <!-- Breadcrumb Section End -->
        <div class="row">
            <div class="col-lg-8">
                <div class="room-details-item">
                    <img src="{{ asset('frontend/img/room/room-details.jpg') }}" alt="">
                    <?php
                        $avl_room = getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $request->check_in_date_last, $request->check_out, $request->check_in);
                    ?>
                    <div class="rooms-left-badge">
                        {{ $avl_room }} Rooms Left
                    </div>
                    <div class="rd-text">
                        <div class="rd-title">
                            <h3>{{ $room->room_category->name ?? 'No Name' }}</h3>
                            <div class="rdt-right">
                                <a href="javascript:void(0)" onclick="scrollToTarget()">Booking Now</a>
                            </div>
                        </div>
                        <h2 class="room_charge_{{ $room->id }}">{{ format_price(getMinCharge($room->category_id)) }}<span>/Pernight</span></h2>
                        <table>
                            <tbody>
                                <tr>
                                    <td class="r-o">Size:</td>
                                    <td>{{ $room->room_category->size ?? 'Not Available' }}</td>
                                </tr>
                                <tr>
                                    <td class="r-o">Capacity:</td>
                                    <td>{{ $room->room_category->capacity ?? '0' }} Max persion</td>
                                </tr>
                                <tr>
                                    <td class="r-o">Bed:</td>
                                    <td>{{ $room->room_category->bed_type ?? 'Not Available' }}</td>
                                </tr>
                                <tr>
                                    <td class="r-o">Services:</td>
                                    <td>{{ $room->room_category->services ?? 'Not Available' }}</td>
                                </tr>
                            </tbody>
                        </table>
                        <p class="f-para">{{ $room->room_category->description ?? 'No Description Available' }}</p>
                    </div>
                </div>
                <hr>
                <div class="review-add mb-4" id="targetSection">
                    <h4>Booking Information</h4>                    
                    <form action="{{ route('store.card') }}" method="post" class="ra-form bookRoomForm" data-room-id="{{ $room->id }}" class="ra-form">
                    
                        <div class="row">
                            <div class="col-lg-6">
                                <input type="text" value="{{ $request->check_in ?? '' }}" name="check_in" placeholder="Check In Date" readonly>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" value="{{ $request->check_out ?? '' }}" name="check_out" placeholder="Check Out Date*" readonly>
                            </div>
                            <div class="col-lg-6">
                                <select class="contact-select occupant_type_{{ $room->id }}" onchange="changeOccupantType({{ $room->id }})" name="occupant_type">
                                    <option value="">Select Occupant Type</option>
                                    @foreach($occupant as $occu)
                                    <option value="{{ $occu->id }}">{{ $occu->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <input type="text" name="room_charge" class="room_charge_val_{{ $room->id }}" value="{{ getMinCharge($room->category_id) }}" placeholder="Room Charge" readonly required>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" name="adult" class="adult" placeholder="No. of Adults" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" name="child" class="child" placeholder="No. of Children" required>
                            </div>
                            <div class="col-lg-6">
                                <input type="number" name="booked_room_no" value="1" maxlength="{{ $avl_room }}"  class="booked_room_{{ $room->id }}" placeholder="No. of Booked Room" required>
                            </div>
                            <!-- Guest Info -->
                            <div class="col-lg-6 guest_section_{{ $room->id }}" style="display: none;">
                                <input type="text" name="guest_name" placeholder="Guest Name" class="guest_name_{{ $room->id }}"> 
                            </div>
                            <div class="col-lg-6 guest_section_{{ $room->id }}" style="display: none;">
                                <input type="email" name="guest_email" placeholder="Guest Email" class="guest_email_{{ $room->id }}"> 
                            </div>
                            <div class="col-lg-6 guest_section_{{ $room->id }}" style="display: none;">
                                <input type="number" name="guest_mobile" placeholder="Guest Mobile" class="mobile_validation guest_mobile_{{ $room->id }}" maxlength="10"> 
                            </div>
                            <input type="hidden" name="category_id" value="{{ $room->category_id }}" class="category_id_{{ $room->id }}">
                            <input type="hidden" name="category_type_id" value="{{ $room->category_type_id }}" class="category_type_id_{{ $room->id }}">
                            <input type="hidden" name="room_category_id" value="{{ $room->room_category_id }}" class="room_category_id_{{ $room->id }}">
                            <input type="hidden" name="gst" value="{{ $room->room_category->GST }}" class="gst_{{ $room->id }}">
                            <input type="hidden" name="no_of_booked_room" value="{{ $room->no_of_booked_room }}" class="no_of_booked_room_{{ $room->id }}">
                            <input type="hidden" name="room_charge" class="room_charge_val_{{ $room->id }}" value="{{ getMinCharge($room->category_id) }}">
                            <input type="hidden" name="guest_info" class="guest_info_{{ $room->id }}">
                            <input type="hidden" class="avalaiable_rooms_{{ $room->id }}" value="{{ $avl_room }}">
                            <input type="hidden" value="{{ $room->room_category->capacity }}" class="capacity">
                            <div class="col-lg-12">
                                @if(checkRoomInCard($room->category_id, $room->category_type_id, $room->room_category_id, $member->MemberID))
                                <button type="button" onclick="showMsg()">Booked</button>
                                @else
                                <button type="submit" class="book_btn">Book Room</button> 
                                <button type="button" class="booked_btn" onclick="showMsg()" style="display: none;">Booked</button>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="room-booking">
                    
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Room Details Section End -->


@endsection

@section('script')
<script>
    $(document).ready(function() {
        getSummaryCard();
        // Datepicker initialization
        $(".date-input").datepicker({
            dateFormat: "mm/dd/yy",
            minDate: 0
        });

        
    });
</script>
<script>
    function scrollToTarget() {
        $('html, body').animate({
                scrollTop: $('#targetSection').offset().top
            }, 2000);
    }
</script>

<script>
    function formatPrice(amount) {
        return '₹ ' + Number(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2
        });
    }
</script>
<script>
    function changeOccupantType(argument) {
        var occup_cls       = '.occupant_type_'+argument;
        var cat_cls         = '.category_id_'+argument;
        var cat_typ_cls     = '.category_type_id_'+argument;
        var rm_cat_cls      = '.room_category_id_'+argument;
        var gest_sec_cls    = '.guest_section_'+argument;
        var guest_info      = '.guest_info_'+argument;
        var room_chr_cls    = '.room_charge_'+argument; 
        var room_chr_val    = '.room_charge_val_'+argument;

        var occup_val           = $(occup_cls).val();
        var category_id         = $(cat_cls).val();
        var category_type_id    = $(cat_typ_cls).val();
        var room_category_id    = $(rm_cat_cls).val();
       
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

           type:'POST',

           url:"{{ route('check.occupant.room') }}",

           data:{occup_val:occup_val, cat_val:category_id, cat_typ_val:category_type_id, rm_cat_val:room_category_id},

           success:function(data){

                console.log(data.data);

                if(data.data){

                    $(guest_info).val(data.data.additional_info);
                   
                    if(data.data.additional_info=='Yes'){
                        $(gest_sec_cls).css('display','block');
                    } else {
                        $(gest_sec_cls).css('display','none');
                    }

                } else {

                    $(gest_sec_cls).css('display','none');

                }

                if(data.room_charge){
                    $(room_chr_cls).html(formatPrice(data.room_charge.charges_nite)+'<span>/Pernight</span>');
                    $(room_chr_val).val(data.room_charge.charges_nite);
                } else {
                    $(room_chr_cls).html(formatPrice('0')+'<span>/Pernight</span>');
                    $(room_chr_val).val('0');
                    toastr.error('Changes are not defined. Please select different occupant type');
                }

           }

        });
    }
</script>
<script>
    function showMsg() {
        toastr.info('This room is already booked in your cart.');
    }
</script>
<script>
    $(document).on('submit', '.bookRoomForm', function(e){

        var argument                = $(this).data('room-id');

        var occup_cls               = '.occupant_type_'+argument;
        var occup_val               = $(occup_cls).val();
    
        if(occup_val==''){
            toastr.error('Select Occupant Type.');
            return false;
        }
        
        var booked_room             = '.booked_room_'+argument;
        var booked_room_no          = $(booked_room).val();

        var no_of_booked_room_cls   = '.no_of_booked_room_'+argument;
        var no_of_booked_room       = $(no_of_booked_room_cls).val();

        var avalaiable_rooms        = $(avalaiable_rooms_cls).val();
        var avalaiable_rooms_cls    = '.avalaiable_rooms_'+argument;

        var child                   = $('.child').val();
        var adult                   = $('.adult').val();
        var capacity                = $('.capacity').val();

        if(Number(adult)+Number(child)>Number(capacity)){
            var vald_msg = 'Total number of guest should not be more than room capacity which is '+capacity;
            toastr.error(vald_msg);
            return false;
        }

        if(booked_room_no==''){
            toastr.error('Enter Room Booked.');
            return false;
        } 

        if(Number(booked_room_no)>Number(no_of_booked_room)){

            var vald_msg = 'You can book only '+no_of_booked_room+' room as per the validation defined in the Application';
            toastr.error(vald_msg);
            return false;
        }
        
        if(Number(booked_room_no)>Number(avalaiable_rooms)){
            toastr.error('Room Inventory not available');
            return false;
        }
        
        e.preventDefault(); // ❌ normal submit roko

        let form = $(this);
        let formData = form.serialize(); // 🔥 all form values

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: formData,
            success: function(res){
                if(res.status === true){
                    toastr.success(res.message);
                    $('.booked_btn').show();
                    $('.book_btn').hide();
                    getSummaryCard();
                    // optional: reset form
                    form[0].reset();
                }else{
                    toastr.error(res.message);
                }
            },
            error: function(xhr){
                let msg = 'Something went wrong';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    });
</script>

<script>
    function getSummaryCard() {
     
        $.ajax({
            url: "{{ route('room-booking.card') }}",
            type: "GET",
            success: function(res){
                $('.room-booking').html(res);
            },
            error: function(xhr){
                console.log('Could not fetch cart item count');
            }
        });
    }
</script>

<script>
    function removeRoomFromCard(card_item_id) {
     
        $.ajax({
            url: "{{ route('remove.room.from.card') }}",
            type: "POST",
            data: {card_item_id:card_item_id, _token:'{{ csrf_token() }}'},
            success: function(res){
                if(res.status === true){
                    toastr.success(res.message);
                    getSummaryCard();
                }else{
                    toastr.error(res.message);
                }
            },
            error: function(xhr){
                let msg = 'Something went wrong';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });
    }   
</script>

@endsection