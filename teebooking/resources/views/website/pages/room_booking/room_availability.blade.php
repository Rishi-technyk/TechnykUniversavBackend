@extends('layouts.web')
@section('content')

<style>
    .text-end {
        text-align: end;
    }

    input[type=text], input[type=number], input[type=date], input[type=email], textarea {
        width: 100% !important;
        padding: 0px 4px !important;
        box-sizing: border-box !important;
        border: solid 1px !important;
        border-radius: .25rem !important;
    }

    .input-h {
        height: 25px !important;
    }

    select { 
        border: solid 1px !important;
    }

    table {
        width: 100% !important;
    }

    label {
        font-size: 15px !important;
        margin-bottom: 0px !important;
    }

    .info h3 {
        font-size: 20px;
        margin-top: 0;
        display: inline-block;
        margin: 10px 0 5px 0;
        font-weight: bold;
        color: #488f3e;
    }

    .label-info {
        background-color: #f6f6f4;
        color: rgba(0, 0, 0, 0.5);
    }

    .f-z-11 {
        font-size: 11px;
    }

    .search-section {
        padding-left: 5%;
    }


</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Room Booking</h5>
                </nav>
            </div>
        </div>

        <div class="row">
             
            <div class="col-lg-12">
                <div class="card mb-1 h-100 mb-4">

                    <div class="card-header">
                        Room Booking
                    </div>

                    <form action="" method="Get">

                        <div class="card-body">

                            @if(Session::has('message'))
                            <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('message') }}</p>
                            @endif
                            
                            <div class="row search-section">

                                <div class="col-lg-3 col-sm-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Member Name <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="member_name" value="{{ $member->DisplayName }}" placeholder="Member Name" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-sm-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Member ID <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="member_ID" value="{{ $member->MemberID }}" placeholder="Member ID" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-sm-6 mt-4">
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

                                <div class="col-lg-2 col-sm-6 mt-4">
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

                                <div class="col-lg-3 col-sm-6 mt-4">

                                    <button class="btn btn-success btn-sm mt-4" type="submit">Search Rooms</button>

                                    <a href="{{ route('room-booking.summary') }}"><button class="btn btn-warning btn-sm mt-4" type="button">My Cart</button></a>

                                </div>  

                            </div>

                        </div>

                    </form>

                </div>
                
            </div>
        </div>

        @if($status)

        <div class="row mt-4">
         
            <div class="col-lg-12">
                <div class="card mb-1 h-100 mb-4">

                    <div class="card-header">
                        Search Results
                    </div>

                    <div class="card-body">
                        
                        <div class="row">
                            
                            <div class="col-lg-3">

                                <span class="text-success">Check In : </span> {{ date("F d, Y", strtotime($request->check_in)) }}
                                
                            </div>

                            <div class="col-lg-3">

                                <span class="text-success">Check Out : </span> {{ date("F d, Y", strtotime($request->check_out)) }}
                                
                            </div>

                            <div class="col-lg-3">

                                <!-- <span class="text-success">Nites : </span> 0 -->
                                
                            </div>

                        </div>

                        <div class="row mt-4">

                            @if($rooms)

                                @foreach($rooms as $key => $room)

                                    @if(getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in)>'0')
                                    
                                        <div class="col-lg-12 mb-4">
                                            
                                            <div class="card">

                                                <div class="row">
                                                    
                                                    <div class="col-lg-3">

                                                        <img src="{{ asset($room->room_category->room_image) }}" style="max-height: 175px;">
                                                        
                                                    </div>

                                                    <div class="col-lg-7">

                                                        <div class="info">
                                                            
                                                            <h3>{{ $room->room_category->name }}</h3>

                                                            <div class="row mb-2">

                                                                <?php
                                                                    $avl_room = getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in);
                                                                ?>
                                                                
                                                                <div class="col-lg-3">
                                                                    <button class="btn btn-sm btn-danger mt-2" type="button">{{ $avl_room }} Room Left!</button>
                                                                </div>

                                                                <div class="col-lg-4 mt-2 label label-info">
                                                                    <b> <span class="roominfo" style="padding-right: 6px;">Room Charge</span> <i class="fa fa-inr"></i> <span class="room_charge_{{ $room->id }}">0</span> </b>
                                                                </div>

                                                                <div class="col-lg-5 mt-2 label label-info">
                                                                    <!-- <b > <span class="roominfo" style="padding-right: 6px;">Guest</span> <i class="fa fa-inr"></i> 2000 </b> -->
                                                                </div>

                                                            </div>

                                                            <b class="f-z-11 mt-4">{{ $room->room_category->description }}</b>

                                                            <div class="row mt-2">

                                                                <div class="col-lg-3 text-dark"> <small>Rooms to be booked</small> </div>

                                                                <div class="col-lg-2">
                                                                    <input type="number" value="1" class="booked_room_{{ $room->id }}" style="height: 25px !important;">
                                                                </div>

                                                                <div class="col-lg-3">
                                                                    <select class="form-control occupant_type_{{ $room->id }}" onchange="changeOccupantType({{ $room->id }})" style="height: 25px !important; padding: 0% 5% !important;">
                                                                        <option value="">Select Occupant Type</option>
                                                                        @foreach($occupant as $occu)
                                                                        <option value="{{ $occu->id }}">{{ $occu->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="col-lg-2">
                                                                    <input type="text" class="adult_{{ $room->id }} mobile_validation" style="height: 25px !important;" placeholder="Adult">
                                                                </div>

                                                                <div class="col-lg-2">
                                                                    <input type="text" class="child_{{ $room->id }} mobile_validation" style="height: 25px !important;" placeholder="Child">
                                                                </div>

                                                            </div>

                                                        </div>
                                                        
                                                    </div>

                                                    <div class="col-lg-2">

                                                        
                                                        @if(checkRoomInCard($room->category_id, $room->category_type_id, $room->room_category_id, $member->MemberID))
                                                        <button class="btn btn-sm btn-danger mb-2 booking_btn_{{ $room->id }}" style="margin-top: 90% !important;" onclick="removeRoom({{ $room->id }}, {{$room->category_id}}, {{$room->category_type_id}}, {{$room->room_category_id}})" type="button">Remove</button>
                                                        <button class="btn btn-sm btn-warning mb-2 booked_btn_{{ $room->id }}" onclick="addRoomInCard({{ $room->id }})" style="margin-top: 90% !important; display: none;" type="button">Book Room</button>
                                                        @else
                                                        <button class="btn btn-sm btn-warning mb-2 booked_btn_{{ $room->id }}" onclick="addRoomInCard({{ $room->id }})" style="margin-top: 90% !important;" type="button">Book Room</button>
                                                        <button class="btn btn-sm btn-danger mb-2 booking_btn_{{ $room->id }}" onclick="removeRoom({{ $room->id }}, {{$room->category_id}}, {{$room->category_type_id}}, {{$room->room_category_id}})" style="margin-top: 90% !important; display: none;" type="button">Remove</button>
                                                        @endif
                                                       
                                                        
                                                    </div>

                                                    <div class="col-lg-3"></div>
                                                    <div class="col-lg-9 mb-2 guest_section_{{ $room->id }}" style="display: none;">
                                                        <div class="row">
                                                            <div class="col-lg-3">
                                                                <input type="text" name="guest_name" placeholder="Guest Name" class="form-control input-h guest_name_{{ $room->id }}"> 
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <input type="email" name="guest_email" placeholder="Guest Email" class="form-control input-h guest_email_{{ $room->id }}"> 
                                                            </div>
                                                            <div class="col-lg-3">
                                                                <input type="text" name="guest_mobile" placeholder="Guest Mobile" class="form-control input-h mobile_validation guest_mobile_{{ $room->id }}" maxlength="10"> 
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>
                                                
                                            </div>

                                        </div>

                                        <form action="" method="Post" class="form_data_{{ $room->id }}">

                                            <input type="hidden" name="category_id" value="{{ $room->category_id }}" class="category_id_{{ $room->id }}">
                                            <input type="hidden" name="category_type_id" value="{{ $room->category_type_id }}" class="category_type_id_{{ $room->id }}">
                                            <input type="hidden" name="room_category_id" value="{{ $room->room_category_id }}" class="room_category_id_{{ $room->id }}">
                                            <input type="hidden" name="gst" value="{{ $room->room_category->GST }}" class="gst_{{ $room->id }}">
                                            <input type="hidden" name="no_of_booked_room" value="{{ $room->no_of_booked_room }}" class="no_of_booked_room_{{ $room->id }}">
                                            <input type="hidden" name="room_charge" class="room_charge_val_{{ $room->id }}">
                                            <input type="hidden" name="guest_info" class="guest_info_{{ $room->id }}">
                                            <input type="hidden" class="avalaiable_rooms_{{ $room->id }}" value="{{ $avl_room }}">
                                            
                                        </form>


                                    @endif

                                @endforeach

                            @else

                                <div class="col-lg-12 mb-4">
                                            
                                    <div class="text-center">
                                        @if($daysDifference>=$max_nitie)
                                        <p class="text-danger">Nites booked exceed the permissible limit.</p>
                                        @else
                                        <p class="text-danger">Sorry! No Rooms available</p>
                                        @endif
                                        
                                    </div>

                                </div>

                            @endif

                        </div>
                        
                    </div>

                </div>
                
            </div>
        </div>

        @endif

        @if($SOP && $SOP->content && count($rooms))
        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary d-none" id="myBtn" data-toggle="modal" data-target="#exampleModal" data-backdrop="static" data-keyboard="false">
          
        </button>

        <!-- Modal -->
        <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered modal-lg" style="width: 100%;" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="close btn-sm btn-outline-danger" data-dismiss="modal" aria-label="Close">
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

    </div>
</section>



<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

<script>
    jQuery(document).ready(function(){
        
        setTimeout(function() {
            $('#myBtn').click();
        }, 500);
    });
</script>

<script>    
    $(document).ready(function() {
        document.getElementById("fromDate").min = new Date().toISOString().split("T")[0];
        document.getElementById("toDate").min = new Date().toISOString().split("T")[0];
    });
</script>

<script>
    function validateForm()
    {
        // Validate
        var toDate = $("#toDate").val();
        if (toDate=="" || toDate==null) {
            $('.toDate_error').text("Please Select Check In Date");
            return false;
        } else {
           $('.toDate_error').text(""); 
        }

        var fromDate = $("#fromDate").val();
        if (fromDate=="" || fromDate==null) {
            $('.fromDate_error').text("Please Select Check Out Date");
            return false;
        } else {
           $('.fromDate_error').text(""); 
        }
        
      return true;
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
                    $(room_chr_cls).text(data.room_charge.charges_nite);
                    $(room_chr_val).val(data.room_charge.charges_nite);
                } else {
                    $(room_chr_cls).text('0');
                    $(room_chr_val).val('0');
                }

           }

        });
    }
</script>

<script>
    function addRoomInCard(argument) {
        
        var occup_cls       = '.occupant_type_'+argument;
        var cat_cls         = '.category_id_'+argument;
        var cat_typ_cls     = '.category_type_id_'+argument;
        var rm_cat_cls      = '.room_category_id_'+argument;
        var gest_sec_cls    = '.guest_section_'+argument;
        var room_chr_cls    = '.room_charge_'+argument; 
        var room_chr_val    = '.room_charge_val_'+argument;
        var guest_info      = '.guest_info_'+argument;
        var guest_name      = '.guest_name_'+argument;
        var guest_email     = '.guest_email_'+argument;
        var guest_mobile    = '.guest_mobile_'+argument;
        var adult_cls       = '.adult_'+argument;
        var child_cls       = '.child_'+argument;
        var booked_room     = '.booked_room_'+argument;
        var booked_btn_cls  = '.booked_btn_'+argument;
        var booking_btn_cls = '.booking_btn_'+argument;
        var gst_cls         = '.gst_'+argument;
        var no_of_booked_room_cls = '.no_of_booked_room_'+argument;
        var avalaiable_rooms_cls = '.avalaiable_rooms_'+argument;

        var occup_val           = $(occup_cls).val();
        var category_id         = $(cat_cls).val();
        var category_type_id    = $(cat_typ_cls).val();
        var room_category_id    = $(rm_cat_cls).val();
        var room_charges        = $(room_chr_val).val();
        var adult               = $(adult_cls).val();
        var child               = $(child_cls).val();
        var booked_room_no      = $(booked_room).val();
        var guest_info          = $(guest_info).val();
        var guest_name          = $(guest_name).val();
        var guest_email         = $(guest_email).val();
        var guest_mobile        = $(guest_mobile).val();
        var gst                 = $(gst_cls).val();
        var no_of_booked_room   = $(no_of_booked_room_cls).val();
        var avalaiable_rooms    = $(avalaiable_rooms_cls).val();
        var check_in            = $('.check_in').val();
        var check_out           = $('.check_out').val();

        if(occup_val==''){
            alert('Select Occupant Type.');
            return false;
        }

        if(room_charges=='' || room_charges=='0'){
            alert('Rates not Defined');
            return false;
        }

        if(adult==''){
            alert('Enter Adult.');
            return false;
        }

        if(child==''){
            alert('Enter Child.');
            return false;
        }

        if(guest_info=='Yes'){

            if(guest_name==''){
                alert('Enter Guest Name.');
                return false;
            }

            if(guest_email==''){
                alert('Enter Guest Email.');
                return false;
            }

            if(guest_mobile==''){
                alert('Enter Guest Mobile.');
                return false;
            }

        }

        if(booked_room_no==''){
            alert('Enter Room Booked.');
            return false;
        } 

        if(Number(booked_room_no)>Number(no_of_booked_room)){

            var vald_msg = 'You can book only '+no_of_booked_room+' room as per the validation defined in the Application';

            alert(vald_msg);
            return false;
        }
        
        if(Number(booked_room_no)>Number(avalaiable_rooms)){
            alert('Room Inventory not available');
            return false;
        }

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

           type:'POST',

           url:"{{ route('store.card') }}",

           data:{occup_val:occup_val, category_id:category_id, category_type_id:category_type_id, room_category_id:room_category_id, room_charges:room_charges, adult:adult, child:child, booked_room_no:booked_room_no, guest_name:guest_name, guest_email:guest_email, guest_mobile:guest_mobile, check_in:check_in, check_out:check_out, gst:gst},

           success:function(data){

                console.log(data);

                if(data){
                    $(booked_btn_cls).hide();
                    $(booking_btn_cls).show();
                }               

           }

        });       
        
    }
</script>

<script>
    jQuery('.mobile_validation').keyup(function () {     
        this.value = this.value.replace(/[^0-9\.]/g,'');
    });
</script>

<script>
    function removeRoom(argument='', category_id='', category_type_id='', room_category_id='') {

        var booked_btn_cls  = '.booked_btn_'+argument;
        var booking_btn_cls = '.booking_btn_'+argument;

        if(confirm("Are you sure?")){

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

                type:'POST',

                url:"{{ route('cancel.room.item.booking') }}",

                data:{category_id:category_id, category_type_id:category_type_id, room_category_id:room_category_id},

                success:function(data){
                    console.log(data);
                    $(booked_btn_cls).show();
                    $(booking_btn_cls).hide();
                }

            });

        }
    }
</script>

@endpush()
@endsection