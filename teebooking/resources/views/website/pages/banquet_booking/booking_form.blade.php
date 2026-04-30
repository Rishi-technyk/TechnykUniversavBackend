@extends('layouts.web')
@section('content')

<style>
    .text-end {
        text-align: end;
    }

    .f-right {
        float: right;
    }

    input[type=text], input[type=number], input[type=date], textarea {
        width: 100% !important;
        padding: 20px 10px !important;
        box-sizing: border-box !important;
        border: solid 1px !important;
        border-radius: .25rem !important;
    }

    select { 
        border: solid 1px !important;
    }

    .modal-body {
        height: 500px;
        overflow: scroll;
    }


</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Banquet Booking</h5>
                </nav>
            </div>
        </div>

        <div class="row">
             
            <div class="col-lg-12">
                <div class="card mb-1 h-100">

                    <div class="card-header">
                        Function Booking
                    </div>

                    <form action="{{ route('banquet.store') }}" method="Post" id="banquet_form">

                        @csrf

                        <div class="card-body">
                            
                            <div class="row">

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="text-muted"> Occupant Type <b class="text-danger">*</b></label>
                                        <select class="form-control occupant" onchange="occupantType(this.value)" id="occupant_type" name="occupant_type" required>
                                            <option value="">Select Occupant Type</option>
                                            @foreach($occupan as $key => $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger occupant_error"></small>
                                        <input type="hidden" class="add_info" value="">
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="text-muted"> Member ID <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="memberID" value="{{ $member->MemberID }}" placeholder="Member ID" readonly>
                                        
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4 d-none">
                                    <div class="form-group">
                                        <label class="text-muted"> Card ID <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="SC_ID" value="{{ $member->SC_ID }}" placeholder="Card ID" readonly>
                                    </div>
                                </div>

                                <input type="hidden" name="SC_ID" value="{{ $member->SC_ID }}">


                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Name <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control other_field memberName" name="memberName" value="{{ $member->DisplayName }}" placeholder="Name" readonly>
                                        <small class="text-danger name_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Mobile No. <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control mobile_no" name="memberMobile" placeholder="Member Mobile No.">
                                        <small class="text-danger mobile_no_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Email <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control other_field memberEmail" value="{{ $member->Email }}" name="memberEmail" placeholder="Email" readonly>
                                        <small class="text-danger email_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Address <b class="text-danger">*</b></label>
                                        @if($member->Address)
                                        <input type="text" class="form-control other_field address" name="address" value="{{ $member->Address }}" placeholder="Address" required readonly>
                                        @else
                                        <input type="text" class="form-control address" name="address" placeholder="Address" required>
                                        @endif
                                        <small class="text-danger address_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Function Date <b class="text-danger">*</b></label>
                                        @if($setting && $setting->min_days && $setting->max_days)
                                        <input type="date" class="form-control function_date" min="{{ $from_date }}" max="{{ $to_date }}" name="funDate" placeholder="Function Date" onkeydown="return false" required>
                                        @else
                                        <input type="date" class="form-control function_date" id="date" name="funDate" placeholder="Function Date" onkeydown="return false" required>
                                        @endif
                                        <small class="text-danger function_date_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Function Type <b class="text-danger">*</b></label>
                                        <select class="form-control functionType" name="functionType" required>
                                            <option value="">Select Function Type</option>
                                            @foreach($function as $key => $fun)
                                            <option value="{{ $fun->id }}">{{ $fun->name }}</option>
                                            @endforeach
                                        </select>
                                        <small class="text-danger functionType_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> No. Of Person <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control noofPerson" name="noofPerson" placeholder="No. Of Person" required>
                                        <small class="text-danger noofPerson_error"></small>
                                    </div>
                                </div>                             

                            </div>

                            
                            <div class="card mt-4">

                                <div class="card-header">
                                    Venue Details
                                </div>
                                
                                <div class="card-body" id="newinput">
                                    
                                    <div class="row">
                                        
                                        <div class="col-lg-6">
                                            <div class="form-group">
                                                <label class="text-muted"> Session </label>
                                                <select class="form-control session_0" name="session_id[]" onchange="selectSession('0')" required>
                                                  <option value="">Select Session</option>
                                                  @foreach($session as $key => $sess)
                                                    <option value="{{ $sess->id }}">{{ $sess->name }}</option>
                                                  @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <label class="text-muted"> Venue <small> (Capacity <span class="venue_capacity_0"></span>) </small> </label>
                                                <select class="form-control venue_0" name="vanue_id[]" onchange="selectVanue('0')" required>
                                                  <option value="">Select Venue</option>                                                  
                                                </select>
                                                <input type="hidden" name="group_id" class="venue_group">
                                                <input type="hidden" name="charges[]" class="form-control charges_0" placeholder="Charge">
                                                <input type="hidden" name="gst_amount[]" class="form-control gst_amt_0" placeholder="GST Amount">
                                                <input type="hidden" name="gst_per[]" class="form-control gst_per_0" placeholder="GST">
                                                <input type="hidden" name="venue_capacity_val[]" class="form-control venue_capacity_val_0">
                                                <input type="hidden" name="security_deposit[]" class="form-control security_deposit_0">
                                                <input type="hidden" name="total[]" class="form-control total_0" placeholder="Total">
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <hr>
                                <div class="row">
                                    
                                    <div class="col-lg-9">
                                        
                                    </div>
                                    <div class="col-lg-2">
                                        <div><b class="text-left">Venue Count :</b></div>
                                        <div><b class="text-left">Total Amount : </b></div>
                                    </div>
                                    <div class="col-lg-1">
                                        <div><span class="text-right total_value_count">0</span></div>
                                        <div><span class="text-right total_value_amt">0</span></div>
                                        <input type="hidden" name="total_value_amount" class="total_value_amt_val">
                                        <input type="hidden" name="pax_changes" value="No" class="pax_changes">
                                    </div>
                                </div>

                                <div class="card-footer text-end mt-4">
                                    <button type="button" class="btn-sm btn btn-success" id="rowAdder">Add Venue</button>
                                </div>

                                

                            </div>

                            <div class="row">
                                
                                <div class="col-lg-12 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Remark </label>
                                        <textarea name="remark" class="form-control" placeholder="Remark"></textarea>
                                    </div>
                                </div>

                            </div>

                            <div class="text-left row mt-4">

                                <div class="input-group mb-3">
                                  <input type="checkbox" aria-label="Checkbox for following text input" required onclick="changeFormBtn()">
                                  <span class="mt-1" style="margin-left: 1%;">I agree terms and conditions.</span>
                                </div>
                                
                            </div>

                            <div class="text-center mt-4">
                                
                                <button class="btn btn-success form_submit_btn" type="submit">Submit</button>
                                <button class="btn btn-success form_btn" type="button" onclick="validateForm()">Submit</button>
                                
                            </div>

                        </div>

                    </form>

                </div>
                
            </div>
        </div>

        @if($SOP && $SOP->content)
        <button type="button" class="btn btn-primary d-none" id="myBtn" data-toggle="modal" data-target="#exampleModal" data-backdrop="static" data-keyboard="false">
          
        </button>

        <div class="modal fade bd-example-modal-lg d-none" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
           <div class="modal-dialog modal-dialog-centered" style="max-width: 90vw;" role="document">
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

        $('.other_field_blank').hide();
        $('.form_btn').hide();
    });
</script>

<script>    
    $(document).ready(function() {
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
    });
</script>

<script>
    function changeFormBtn() {
        $('.form_submit_btn').hide();
        $('.form_btn').show();
    }
</script>

<script>

    // $('.other_field').hide();

    function occupantType(argument) {

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

           type:'POST',

           url:"{{ route('check.occupant') }}",

           data:{occ_id:argument},

           success:function(data){

                console.log(data.data);

                if(data.data){

                    var memberName = '<?php echo $member->DisplayName; ?>';
                    var memberEmail = '<?php echo $member->Email; ?>';

                    if(data.data.additional_info=='Yes'){

                        $('.add_info').val('Yes');
                        // $('.other_field_blank').show();
                        // $('.other_field').hide();
                        $('.memberName').val('');
                        $('.memberEmail').val('');
                        $('.other_field').prop('readonly',false);

                    } else {

                        $('.memberName').val(memberName);
                        $('.memberEmail').val(memberEmail);
                        $('.other_field').prop('readonly',true);
                        // $('.other_field_blank').hide();
                        // $('.other_field').show();
                        // $('.add_info').val('No');

                    }
                } else {

                    $('.memberName').val(memberName);
                    $('.memberEmail').val(memberEmail);
                    $('.other_field').prop('readonly',true);
                    // $('.other_field_blank').hide();
                    // $('.other_field').show();
                    // $('.add_info').val('No');

                }

           }

        });
    }
</script>

<script type="text/javascript">
    $("#rowAdder").click(function () {

        var maxVenue = '<?php echo $maxVenue; ?>';

        var venue_count = 0;

        $('select[name="vanue_id[]"]').each(function(index) {
            let val = $(this).val();
            venue_count++;
            console.log(`Charge ${index}:`, val);
        });

        if(venue_count<maxVenue){

            $.ajax({

                type:'Get',

                url:"{{ route('append.extra.more.field') }}",

                success:function(data){

                    console.log(data.data);
                    $('#newinput').append(data.html);

                }

            });

        } else {

            var msg = 'You can add max of '+maxVenue+' venues in a booking.';
            alert(msg);
        }
        
        

    });

    $("body").on("click", "#DeleteRow", function () {

        if($(this).val()){

            var argument = $(this).val();

            var venue_cls = '.venue_'+argument;
            var venuee = $(venue_cls).val();

            var session_cls = '.session_'+argument;
            var sessionn = $(session_cls).val();

            var total_cls = '.total_'+argument;
            var total = $(total_cls).val();

            var occupant = $('.occupant').val();
            
            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

               type:'POST',

               url:"{{ route('remove.extra.more.field') }}",

               data:{venue:venuee, session:sessionn, occupant:occupant},

               success:function(data){
                    console.log(data);
               }

            });
        } 

        if(total){

            var total_value_amt = parseFloat($('.total_value_amt_val').val()) || 0;

            var vtotl = total_value_amt - parseFloat(total);

            $('.total_value_amt_val').val(vtotl);

            $('.total_value_amt').text(vtotl);
        }
    
        $(this).parents("#row").remove();

        let venue_count = 0;

        $('select[name="vanue_id[]"]').each(function(index) {
            let val = $(this).val();
            venue_count++;
            console.log(`Charge ${index}:`, val);
        });

        $('.total_value_count').text(venue_count);

    })
</script>

<script>
    function selectVanue(argument) {
        var venue_cls = '.venue_'+argument;
        var venue = $(venue_cls).val();

        var session_cls = '.session_'+argument;
        var session = $(session_cls).val();

        if(argument=='0'){
            $('.extra_venue').remove();
        }

        var venue_cls = '.venue_'+argument;
        var venuee = $(venue_cls).val();

        var session_cls = '.session_'+argument;
        var sessionn = $(session_cls).val();

        var occupant = $('.occupant').val();
        
        // $.ajaxSetup({

        //     headers: {

        //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

        //     }

        // });

        // $.ajax({

        //    type:'POST',

        //    url:"{{ route('remove.extra.more.field') }}",

        //    data:{venue:venuee, session:sessionn, occupant:occupant},

        //    success:function(data){
        //         console.log(data);
        //    }

        // });

        getCharges(venue, session, argument);
    }
</script>

<script>
    function selectSession(argument) {
        var session_cls = '.session_'+argument;
        var session = $(session_cls).val();

        var venue_cls = '.venue_'+argument;

        var func = $(".function_date").val();
        if (func=="" || func==null) {
            $('.function_date_error').text("Please select function date");
            $(session_cls).val('');
            return false;
        } else {
           $('.function_date_error').text(""); 
        }

        $(".function_date").attr('readonly','readonly');

        var group_id = $('.venue_group').val();

        $(venue_cls).html('');
        
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

           type:'POST',

           url:"{{ route('get.venue.by.session') }}",

           data:{session:session, function_date:func, group_id:group_id},

           success:function(data){

                console.log(data);                            
                
                $(venue_cls).html(data);
           }

        });
        
        // chrgeAjax(argument);
        
    }
</script> 

<script>
    function getCharges(venue, session, rand) {
        
        if(venue && session){

            var occupant            = $('.occupant').val();

            var function_date       = $('.function_date').val();

            var charges_cls         = '.charges_'+rand;

            var gst_per_text_cls    = '.gst_per_text_'+rand;

            var gst_amt_cls         = '.gst_amt_'+rand;

            var gst_per_cls         = '.gst_per_'+rand;

            var total_cls           = '.total_'+rand;

            var venue_capacity_cls  = '.venue_capacity_'+rand;

            var venue_capacity_val_cls  = '.venue_capacity_val_'+rand;

            var security_deposit_cls= '.security_deposit_'+rand;

            var pax                 = $('.noofPerson').val();

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            if(pax){

                if(occupant){

                    if(function_date){

                        $.ajax({

                           type:'POST',

                           url:"{{ route('get.charges') }}",

                           data:{venue:venue, session:session, occupant:occupant, function_date:function_date, pax:pax},

                           success:function(data){

                                console.log(data.charges);  
                                
                                if(data.booking=='Booking'){

                                    alert('Please select another date. Because this date is already booked.');

                                    var session_cls = '.session_'+rand;
                                    var session = $(session_cls).val('');

                                    var venue_cls = '.venue_'+rand;
                                    var venue = $(venue_cls).val('');

                                    $(charges_cls).val('');

                                    $(gst_per_text_cls).text('');

                                    $(gst_amt_cls).val('');

                                    $(gst_per_cls).val('');

                                    $(security_deposit_cls).val('');

                                } else {

                                    $("#date").prop("readonly", true);
                                    $("#date").attr("readonly","readonly");

                                    if(data.charges && data.venue_charge){

                                        if(data.checkVenue=='Insert'){

                                            $('.noofPerson').attr("readonly", true);

                                            if(rand=='0'){
                                                $('.venue_group').val(data.venue.group_id); 
                                            }

                                            $(charges_cls).val(data.venue_charge);

                                            $(gst_per_text_cls).text(data.venue.GSTper);

                                            $(gst_per_cls).val(data.venue.GSTper);

                                            $(venue_capacity_cls).text(data.venue.capacity);

                                            $(venue_capacity_val_cls).val(data.venue.capacity);

                                            $(security_deposit_cls).val(data.venue.security_deposit);

                                            if(data.venue){

                                                var percentage = parseFloat(data.venue.GSTper) || 0;
                                                
                                                var totalWidth = data.venue_charge;

                                                var security_deposit = parseFloat(data.venue.security_deposit) || 0;

                                                var new_width = (percentage / 100) * totalWidth;

                                                $(gst_amt_cls).val(new_width);

                                                $(total_cls).val(parseInt(data.venue_charge)+parseInt(new_width)+parseInt(security_deposit));

                                                let venue_count = 0;

                                                $('select[name="vanue_id[]"]').each(function(index) {
                                                    let val = $(this).val();
                                                    venue_count++;
                                                    console.log(`Charge ${index}:`, val);
                                                });

                                                // let total_value_amt = parseFloat($('.total_value_amt_val').val()) || 0;
                                                let total_value_amt = 0;

                                                $('input[name="total[]"]').each(function(index) {
                                                    total_value_amt += parseFloat($(this).val());
                                                });

                                                let v_t_a = 0;

                                                if(venue_count=='0'){

                                                    v_t_a += 0;

                                                } else {

                                                    v_t_a += total_value_amt;

                                                }

                                                // v_t_a += parseInt(data.venue_charge)+parseInt(new_width)+parseInt(security_deposit);

                                                $('.total_value_amt_val').val(v_t_a);

                                                $('.total_value_amt').text(v_t_a);

                                                $('.total_value_count').text(venue_count);
                                                
                                            }

                                        } else {

                                            var session_cls = '.session_'+rand;
                                            var session = $(session_cls).val('');

                                            var venue_cls = '.venue_'+rand;
                                            var venue = $(venue_cls).val('');

                                            $(charges_cls).val('');

                                            $(gst_per_text_cls).text('');

                                            $(gst_amt_cls).val('');

                                            $(gst_per_cls).val('');

                                            $(security_deposit_cls).val('');

                                            alert('This venue already added.');

                                        }
                                        
                                    } else {
                                        var msg = 'The venue cannot be booked as selected venue has max capacity of '+data.venue_max_pax+' and you are trying to book it for '+pax+' pax.';
                                        
                                        alert(msg);

                                        var session_cls = '.session_'+rand;
                                        var session = $(session_cls).val('');

                                        var venue_cls = '.venue_'+rand;
                                        var venue = $(venue_cls).val('');

                                        $(charges_cls).val('');

                                        $(gst_per_text_cls).text('');

                                        $(gst_amt_cls).val('');

                                        $(gst_per_cls).val('');

                                        $(security_deposit_cls).val('');

                                    }

                                }

                           }

                        });

                    } else {

                        alert('Please Select Function Date');

                        var session_cls = '.session_'+rand;
                        var session = $(session_cls).val('');

                        var venue_cls = '.venue_'+rand;
                        var venue = $(venue_cls).val('');

                        $(charges_cls).val('');

                        $(gst_per_text_cls).text('');

                        $(gst_amt_cls).val('');

                        $(gst_per_cls).val('');

                        $(security_deposit_cls).val('');
                    }

                } else {

                    alert('Please Select Occupant');

                    $("html, body").animate({ scrollTop: 100 }, "slow");

                    var session_cls = '.session_'+rand;
                    var session = $(session_cls).val('');

                    var venue_cls = '.venue_'+rand;
                    var venue = $(venue_cls).val('');

                    $(charges_cls).val('');

                    $(gst_per_text_cls).text('');

                    $(gst_amt_cls).val('');

                    $(gst_per_cls).val('');

                    $(security_deposit_cls).val('');

                }
            } else {

                alert('Please Enter No. Of Person');

            }

        }

    }
</script>

<script>
    function validateForm()
    {
        // Validate
        var title = $(".occupant").val();
        if (title=="" || title==null) {
            $('.occupant_error').text("Please select occupant type");
              $("html, body").animate({ scrollTop: 100 }, "slow");
            return false;
        } else {
           $('.occupant_error').text(""); 
        }

        if($('.add_info').val()=='Yes'){

            var memberName      = $(".memberName_blank").val();
            var memberEmail     = $(".memberEmail_blank").val();
            var mobile          = $(".mobile_no_blank").val();
            var address         = $(".address_blank").val();

        } else {

            var memberName      = $(".memberName").val();
            var memberEmail     = $(".memberEmail").val();
            var mobile          = $(".mobile_no").val();
            var address         = $(".address").val();

        }

        if (memberName=="" || memberName==null) {
            $('.name_error').text("Please enter name");
            return false;
        } else {
           $('.name_error').text(""); 
        }
        
        if (memberEmail=="" || memberEmail==null) {
            $('.email_error').text("Please enter email");
            return false;
        } else {
           $('.email_error').text(""); 
        }
        
        if (mobile=="" || mobile==null) {
            $('.mobile_no_error').text("Please enter mobile no.");
            return false;
        } else {
           $('.mobile_no_error').text(""); 
        }
        
        if (address=="" || address==null) {
            $('.address_error').text("Please enter address");
            return false;
        } else {
           $('.address_error').text(""); 
        }

        var func = $(".function_date").val();
        if (func=="" || func==null) {
            $('.function_date_error').text("Please select function date");
            return false;
        } else {
           $('.function_date_error').text(""); 
        }

        var functy = $(".functionType").val();
        if (functy=="" || functy==null) {
            $('.functionType_error').text("Please select function type");
            return false;
        } else {
           $('.functionType_error').text(""); 
        }

        var noofp = $(".noofPerson").val();
        if (noofp=="" || noofp==null) {
            $('.noofPerson_error').text("Please enter number of person");
            return false;
        } else {
           $('.noofPerson_error').text(""); 
        }

        checkVenue();
      
    }
</script>

<script>
    function checkVenue() {

        if($('.venue_0').val()){

            var noofPerson = $('.noofPerson').val();

            let venue_count = 0;

            $('input[name="charges[]"]').each(function(index) {
                let val = $(this).val();
                if(val != ''){
                    venue_count++;
                    console.log(`Charge ${index}:`, val);
                }
                
            });
           
            let total_capacity = 0;

            $('input[name="venue_capacity_val[]"]').each(function(index) {
                var cap = $(this).val();
                if(cap != ''){
                    total_capacity = parseInt(total_capacity)+parseInt($(this).val());
                }
            });

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

               type:'POST',

               url:"{{ route('get.venue.pax') }}",

               data:{noofPerson:noofPerson, venue_count:venue_count},

               success:function(data){

                    console.log(data); 

                    if(data.status){

                        if(data.pax){

                            if(venue_count=='1'){

                                if(Number(total_capacity)>=Number(noofPerson)){

                                    $('form#banquet_form').submit();

                                } else {

                                    var msg = 'The venue cannot be booked as selected venue has max capacity of '+total_capacity+' and you are trying to book it for '+noofPerson+' pax.';
                                        
                                    alert(msg);

                                }

                            } else {

                                if(Number(noofPerson) <= Number(data.pax.max_pax)){

                                    if(data.pax.total_venue_rate){

                                        $('.total_value_amt').text(data.pax.total_venue_rate);
                                        $('.total_value_amt_val').val(data.pax.total_venue_rate);
                                        $('.pax_changes').val('Yes');

                                    }

                                    $('form#banquet_form').submit();

                                } else {

                                    alert(data.pax.message);

                                }

                            }

                        } else {

                            alert('Sorry!. Please Contact to Support Team.');
                        }

                    }   
                    
               }

            });

        }
        
    }
</script>

<script>
    jQuery('.noofPerson').keyup(function () {     
      this.value = this.value.replace(/[^0-9\.]/g,'');
    });
</script>
@endpush()
@endsection