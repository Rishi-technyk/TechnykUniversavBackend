@extends('frontend.layouts.app')

@section('title', 'Room Booking')
<style>
    .contact-select {
        height: 39px !important;
    }
</style>
@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Room Booking</h4>
    </div>
    <!-- Breadcrumb Section End -->

    @if(Session::has('message'))
    <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('message') }}</p>
    @endif

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <form action="" method="get">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="form-group">
                        <label class="text-muted"> Member Name <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" name="member_name" value="{{ $member->DisplayName }}" placeholder="Member Name" readonly>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6">
                    <div class="form-group">
                        <label class="text-muted"> Member ID <b class="text-danger">*</b></label>
                        <input type="text" class="form-control" name="member_ID" value="{{ $member->MemberID }}" placeholder="Member ID" readonly>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6">
                    <div class="form-group">
                        <label class="text-muted"> Check In <b class="text-danger">*</b></label>
                        @if($setting && $setting->min_days && $setting->max_days)
                        <input type="date" class="form-control check_in" min="{{ $from_date }}" max="{{ $to_date }}" name="check_in" value="{{ $request->check_in }}" placeholder="Check In" required>
                        @else
                        <input type="date" class="form-control check_in" id="toDate" name="check_in" value="{{ $request->check_in }}" placeholder="Check In" required>
                        @endif
                        <small class="text-danger toDate_error"></small>
                    </div>
                </div>

                <div class="col-lg-2 col-sm-6">
                    <div class="form-group">
                        <label class="text-muted"> Check Out <b class="text-danger">*</b></label>
                        @if($setting && $setting->min_days && $setting->max_days)
                        <input type="date" class="form-control check_out" min="{{ $from_date }}" max="{{ $to_date }}" name="check_out" value="{{ $request->check_out }}" placeholder="Check Out" required>
                        @else
                        <input type="date" class="form-control check_out" id="fromDate" name="check_out" value="{{ $request->check_out }}" placeholder="Check Out" required>
                        @endif
                        <small class="text-danger fromDate_error"></small>
                    </div>
                </div>    

                <div class="col-lg-3 col-sm-6">

                    <button class="btn-sm mt-15p" type="submit">Search Rooms</button>

                <a href="{{ route('room-booking.summary') }}"><button class="btn-sm mt-15p" type="button">My Cart</button></a>

                </div>
            </div>
        </form>
    </div>

    <div class="row">
        @if($rooms)
            <div class="container">

                <div class="">

                    <div class="row">
                        
                        <div class="col-lg-8 card-section-title-box">

                            <div class="row">

                                @foreach($rooms as $key => $room)

                                    @if(getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in)>'0')
                                    
                                    <div class="col-lg-6 col-md-6">
                                        <div class="room-item">
                                            @if($room->room_category->room_image && file_exists(public_path($room->room_category->room_image)))
                                                <img src="{{ asset($room->room_category->room_image) }}" alt="">
                                            @else
                                                <img src="{{ asset('frontend/img/room/room-3.jpg') }}" alt="">
                                            @endif
                                            <?php
                                                $avl_room = getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in);
                                            ?>
                                            <div class="rooms-left-badge">
                                                {{ $avl_room }} Rooms Left
                                            </div>
                                            <div class="ri-text">
                                                <h4>{{ $room->room_category->name ?? 'No Name' }}</h4>
                                                <h3>{{ format_price(getMinCharge($room->room_category_id)) }}<span>/Night</span></h3>
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
                                                <form action="{{ route('room.details', encrypt($room->id)) }}" class="form-{{ $room->id }}" method="get">
                                                    <input type="hidden" name="check_in" value="{{ $request->check_in }}">
                                                    <input type="hidden" name="check_out" value="{{ $request->check_out }}">
                                                    <input type="hidden" name="check_in_date_last" value="{{ $check_in_date_last }}">
                                                </form>
                                                <a href="javascript:;" data-toggle="modal" data-target="#exampleModal_{{ $room->id }}" class="primary-btn">Book Now</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal fade bd-example-modal-lg" id="exampleModal_{{ $room->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel">Booking For {{ $room->room_category->name ?? 'No Name' }}</h5>
                                                    <button type="button" class="close exampleModalClose_{{ $room->id }}" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form action="{{ route('store.card') }}" method="post" class="ra-form bookRoomForm_{{ $room->id }}" data-room-id="{{ $room->id }}" class="ra-form">
                                                        @csrf
                                                        <div class="row">
                                                            <div class="col-lg-6 form-group">
                                                                <label for="">Check In Date</label>
                                                                <input type="text" class="form-control check_in_{{ $room->id }}" value="{{ $request->check_in ?? '' }}" name="check_in" placeholder="Check In Date" readonly>
                                                            </div>
                                                            <div class="col-lg-6 form-group">
                                                                <label for="">Check Out Date</label>
                                                                <input type="text" class="form-control check_out_{{ $room->id }}" value="{{ $request->check_out ?? '' }}" name="check_out" placeholder="Check Out Date*" readonly>
                                                            </div>
                                                            <div class="col-lg-6 mt-2">
                                                                <label for="">Occupant Type</label>
                                                                <select class="contact-select occupant_type_{{ $room->id }}" onchange="changeOccupantType({{ $room->id }})" name="occupant_type">
                                                                    <option value="">Select Occupant Type</option>
                                                                    @foreach($occupant as $occu)
                                                                    <option value="{{ $occu->id }}">{{ $occu->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-lg-6 mt-2">
                                                                <label for="">Room Charge</label>
                                                                <input type="text" class="form-control room_charge_val_{{ $room->id }}" name="room_charge" value="{{ getMinCharge($room->category_id) }}" placeholder="Room Charge" readonly required>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <label for="">Adult</label>
                                                                <input type="number" name="adult" class="form-control adult_{{ $room->id }}" placeholder="No. of Adults" required>
                                                            </div>
                                                            <div class="col-lg-6">
                                                                <label for="">Child</label>
                                                                <input type="number" name="child" class="form-control child_{{ $room->id }}" placeholder="No. of Children" required>
                                                            </div>
                                                            <div class="col-lg-6 mt-2">
                                                                <label for="">Number Of Rooms</label>
                                                                <input type="number" class="form-control booked_room_no_{{ $room->id }}" name="booked_room_no" value="1" maxlength="{{ $avl_room }}"  class="booked_room_{{ $room->id }}" placeholder="No. of Booked Room" required>
                                                            </div>
                                                            <!-- Guest Info -->
                                                            <div class="col-lg-6 mt-2 guest_section_{{ $room->id }}" style="display: none;">
                                                                <label for="">Guest Name</label>
                                                                <input type="text" name="guest_name" placeholder="Guest Name" class="form-control guest_name_{{ $room->id }}"> 
                                                            </div>
                                                            <div class="col-lg-6 mt-2 guest_section_{{ $room->id }}" style="display: none;">
                                                                <label for="">Guest Email</label>
                                                                <input type="email" name="guest_email" placeholder="Guest Email" class="form-control guest_email_{{ $room->id }}"> 
                                                            </div>
                                                            <div class="col-lg-6 mt-2 guest_section_{{ $room->id }}" style="display: none;">
                                                                <label for="">Guest Mobile</label>
                                                                <input type="number" name="guest_mobile" placeholder="Guest Mobile" class="form-control mobile_validation guest_mobile_{{ $room->id }}" maxlength="10"> 
                                                            </div>
                                                            <input type="hidden" name="category_id" value="{{ $room->category_id }}" class="category_id_{{ $room->id }}">
                                                            <input type="hidden" name="category_type_id" value="{{ $room->category_type_id }}" class="category_type_id_{{ $room->id }}">
                                                            <input type="hidden" name="room_category_id" value="{{ $room->room_category_id }}" class="room_category_id_{{ $room->id }}">
                                                            <input type="hidden" name="gst" value="{{ $room->room_category->GST }}" class="gst_{{ $room->id }}">
                                                            <input type="hidden" name="no_of_booked_room" value="{{ $room->no_of_booked_room }}" class="no_of_booked_room_{{ $room->id }}">
                                                            <input type="hidden" name="room_charge" class="room_charge_val_{{ $room->id }}" value="{{ getMinCharge($room->room_category_id) }}">
                                                            <input type="hidden" name="guest_info" class="guest_info_{{ $room->id }}">
                                                            <input type="hidden" class="avalaiable_rooms_{{ $room->id }}" value="{{ $avl_room }}">
                                                            <input type="hidden" value="{{ $room->room_category->capacity }}" class="capacity_{{ $room->id }}">
                                                            <div class="col-lg-12 mt-4 text-center">
                                                                <button type="button" class="book_btn_{{ $room->id }}" onclick="submitBookingForm({{ $room->id }})">Book Room</button> 
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @endif
                                
                                @endforeach
                                
                            </div>
                            
                        </div>

                        <div class="col-lg-4 card-section-title-box">
                            <div class="room-booking"></div>
                        </div>

                    </div>

                </div>
                
            </div>

            @if($SOP && $SOP->content)
            <!-- Modal -->
            <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <?php echo $SOP->content; ?>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        @else
            @if($request->check_in || $request->check_out)
            <div class="col-lg-12">
                <div class="col-lg-12">
                                                
                    <div class="text-center">
                        @if($max_nitie && $daysDifference>=$max_nitie)
                        <p class="text-danger">Nites booked exceed the permissible limit.</p>
                        @else
                        <p class="text-danger">Sorry! No Rooms available</p>
                        @endif
                        
                    </div>

                </div>
            </div>
            @endif
        @endif
        
    </div>
</div>

@endsection

@section('script')
<script>
    function submitForm(id){
        $('.form-'+id).submit();
    }
</script>
<script>
    $(document).ready(function () {
        $('#exampleModal').modal('show'); // Bootstrap 4
    });
</script>
<script>
    $(document).ready(function() {
        getSummaryCard();        
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
                    // $(room_chr_cls).html(formatPrice(data.room_charge.charges_nite)+'<span>/Pernight</span>');
                    $(room_chr_val).val(data.room_charge.charges_nite);
                } else {
                    // $(room_chr_cls).html(formatPrice('0')+'<span>/Pernight</span>');
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
    function submitBookingForm(argument) {

        var occup_cls               = '.occupant_type_'+argument;
        var occup_val               = $(occup_cls).val();
    
        if(occup_val==''){
            toastr.error('Select Occupant Type.');
            return false;
        }
        
        var booked_room             = '.booked_room_'+argument;
        var booked_room_no          = $(booked_room).val();

        var room_charge             = '.room_charge_val_'+argument;
        var room_charges            = $(room_charge).val();

        var no_of_booked_room_cls   = '.no_of_booked_room_'+argument;
        var no_of_booked_room       = $(no_of_booked_room_cls).val();

        var avalaiable_rooms        = $(avalaiable_rooms_cls).val();
        var avalaiable_rooms_cls    = '.avalaiable_rooms_'+argument;

        var child                   = $('.child_'+argument).val();
        var adult                   = $('.adult_'+argument).val();
        var capacity                = $('.capacity_'+argument).val();
        var guest_info              = $('.guest_info_'+argument).val();

        if(Number(adult)+Number(child)>Number(capacity)){
            var vald_msg = 'Total number of guest should not be more than room capacity which is '+capacity;
            toastr.error(vald_msg);
            return false;
        }

        if(room_charges==''){
            toastr.error('Room charges are not selcted.');
            return false;
        } 

        if(booked_room_no==''){
            toastr.error('Enter Room Booked.');
            return false;
        } 

        if(adult==''){
            toastr.error('Enter number of adult.');
            return false;
        }

        if(child==''){
            toastr.error('Enter number of child.');
            return false;
        } 

        if(guest_info=='Yes'){

            var guest_name              = $('.guest_name_'+argument).val();
            var guest_email             = $('.guest_email_'+argument).val();
            var guest_mobile            = $('.guest_mobile_'+argument).val();
            
            if(guest_name==''){
                toastr.error('Enter guest name.');
                return false;
            } 

            if(guest_email==''){
                toastr.error('Enter guest email.');
                return false;
            } 

            if(guest_mobile==''){
                toastr.error('Enter guest mobile.');
                return false;
            } 
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

        let form = $('.bookRoomForm_'+argument);
        let formData = form.serialize(); // 🔥 all form values

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: formData,
            success: function(res){
                if(res.status === true){
                    toastr.success(res.message);
                    // $('.booked_btn_'+argument).show();
                    // $('.book_btn_'+argument).hide();
                    // $('#exampleModal_'+argument).modal('toggle');
                    // $('.exampleModalClose_'+argument).click();
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

        var child                   = $('.child_'+argument).val();
        var adult                   = $('.adult_'+argument).val();
        var capacity                = $('.capacity_'+argument).val();

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
                    $('.booked_btn_'+argument).show();
                    $('.book_btn_'+argument).hide();
                    $('#exampleModal_'+argument).hide();
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