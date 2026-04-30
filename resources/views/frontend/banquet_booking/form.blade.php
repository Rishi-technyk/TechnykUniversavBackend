@extends('frontend.layouts.app')

@section('title', 'Banquet Booking')
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
        <h4 class="card-section-title">Banquet Booking</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">

        <span class="card-section-bar"></span>
        
        <div class="row">
             
            <div class="col-lg-12">

                <form action="{{ route('banquet.store') }}" method="Post">

                    @csrf

                    <div >
                        
                        <div class="row">

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Occupant Type <b class="text-danger">*</b></label>
                                    <select class="contact-select occupant" onchange="occupantType(this.value)" id="occupant_type" name="occupant_type" required>
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
                                    <input type="text" class="form-control" name="memberID" value="{{ $member->MemberID }}" placeholder="Member ID" readonly required>
                                    
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Card ID <b class="text-danger">*</b></label>
                                    <input type="text" class="form-control" name="SC_ID" value="{{ $member->SC_ID }}" placeholder="Card ID" readonly required>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Name <b class="text-danger">*</b></label>
                                    <input type="text" class="form-control other_field memberName" name="memberName" value="{{ $member->DisplayName }}" placeholder="Name" readonly required>
                                    <small class="text-danger name_error"></small>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Mobile No. <b class="text-danger">*</b></label>
                                    <input type="text" class="form-control mobile_no" name="memberMobile" placeholder="Member Mobile No." required>
                                    <small class="text-danger mobile_no_error"></small>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Email <b class="text-danger">*</b></label>
                                    <input type="text" class="form-control other_field memberEmail" value="{{ $member->Email }}" name="memberEmail" placeholder="Email" readonly required>
                                    <small class="text-danger email_error"></small>
                                </div>
                            </div>

                            <div class="col-lg-6">
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

                            <div class="col-lg-6">
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

                            <div class="col-lg-6">
                                <div class="form-group">
                                    <label class="text-muted"> Function Type <b class="text-danger">*</b></label>
                                    <select class="contact-select functionType" name="functionType" required>
                                        <option value="">Select Function Type</option>
                                        @foreach($function as $key => $fun)
                                        <option value="{{ $fun->id }}">{{ $fun->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-danger functionType_error"></small>
                                </div>
                            </div>

                            <div class="col-lg-6">
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
                                    
                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="text-muted"> Session </label>
                                            <select class="contact-select session_0" name="session_id[]" onchange="selectSession('0')" required>
                                                <option value="">Select Session</option>
                                                @foreach($session as $key => $sess)
                                                <option value="{{ $sess->id }}">{{ $sess->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group value_select_0">
                                            <label class="text-muted"> Venue </label>
                                            <select class="contact-select venue_0" name="vanue_id[]" onchange="selectVanue('0', this.value)" required>
                                                <option value="">Select Venue</option> 
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="text-muted"> Charge </label>
                                            <input type="text" name="charges[]" class="form-control charges_0" placeholder="Charge" readonly required>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="text-muted"> GST(<span class="gst_per_text_0"></span>%) </label>
                                            <input type="text" name="gst_amount[]" class="form-control gst_amt_0" placeholder="GST Amount" readonly required>
                                            <input type="hidden" name="gst_per[]" class="form-control gst_per_0" placeholder="GST" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="text-muted"> Security Deposit </label>
                                            <input type="text" name="security_deposit[]" class="form-control security_deposit_0" placeholder="Security Deposit" readonly>
                                        </div>
                                    </div>

                                    <div class="col-lg-2">
                                        <div class="form-group">
                                            <label class="text-muted"> Total </label>
                                            <input type="text" name="total[]" class="form-control total_0" placeholder="Total" readonly required>
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="card-footer text-end mt-4">
                                <button type="button" class="btn-sm" id="rowAdder">Add Venue</button>
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
                                <input type="checkbox" aria-label="Checkbox for following text input" required>
                                <span class="mt-1" style="margin-left: 1%;">I agree terms and conditions.</span>
                            </div>
                            
                        </div>

                        <div class="text-center mt-4">
                            
                            <button type="submit">Submit</button>
                        </div>

                    </div>

                </form>

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

@endsection

@section('script')
<script>
    jQuery(document).ready(function(){   
        $('.other_field_blank').hide();
    });
</script>
<script>
    $(document).ready(function () {
        $('#exampleModal').modal('show'); // Bootstrap 4
    });
</script>
<script>    
    $(document).ready(function() {
        document.getElementById("date").min = new Date().toISOString().split("T")[0];
    });
</script>

<script>

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
<script>
    function selectVanue(argument, val) {
        var venue_cls = '.venue_'+argument;
        var venue = $(venue_cls).val();

        var session_cls = '.session_'+argument;
        var session = $(session_cls).val();
        
        getCharges(venue, session, argument);
    }

    
</script>

<script>
    function selectSession(argument) {
        var session_cls = '.session_'+argument;
        var session = $(session_cls).val();

        var venue_cls = '.venue_'+argument;

        var venue_select_cls = '.value_select_'+argument;

        var func = $(".function_date").val();
        if (func=="" || func==null) {
            toastr.error("Please select function date");
            $(session_cls).val('');
            return false;
        } else {
            $('.function_date_error').text(""); 
        }

        $(".function_date").attr('readonly','readonly');

        $(venue_cls).html('');
        
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({
            type: 'POST',
            url: "{{ route('get.venue.by.session') }}",
            data: {
                session: session,
                function_date: func,
                argument: argument
            },
            success: function (data) {

                $(venue_select_cls).html(data);
            }
        });
        
        // chrgeAjax(argument);
        
    }
</script>

<script>
    function chrgeAjax(argument) {

        var session_cls = '.session_'+argument;
        var session = $(session_cls).val();

        var venue_cls = '.venue_'+argument;
        var venue = $(venue_cls).val();
        
        var occupant = $('.occupant').val();
        
        $.ajax({

            type:'POST',

            url:"{{ route('get.charges') }}",

            data:{venue:venue, session:session, occupant:occupant, function_date:function_date},

            success:function(data){

                console.log(data.charges);                            

                if(data.booking=='Booking'){

                    toastr.error('Please select another date. Because this date is already booked.');

                    var session_cls = '.session_'+rand;
                    var session = $(session_cls).val('');

                    var venue_cls = '.venue_'+rand;
                    var venue = $(venue_cls).val('');

                    $(charges_cls).val('');

                    $(gst_per_text_cls).text('');

                    $(gst_amt_cls).val('');

                    $(gst_per_cls).val('');

                } else {

                    $("#date").prop("readonly", true);
                    $("#date").attr("readonly","readonly");
                    $("#date").attr("disabled",true);
                    $("#date").attr('disabled','disabled');

                    if(data.charges){

                        if(data.checkVenue=='Insert'){

                            $(charges_cls).val(data.charges.rate);

                            $(gst_per_text_cls).text(data.venue.GSTper);

                            $(gst_per_cls).val(data.venue.GSTper);

                            if(data.venue){

                                var percentage = data.venue.GSTper;
                                
                                var totalWidth = data.charges.rate;

                                var new_width = (percentage / 100) * totalWidth;

                                $(gst_amt_cls).val(new_width);

                                $(total_cls).val(parseInt(data.charges.rate)+parseInt(new_width));
                                
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

                            toastr.error('This venue already added.');

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

                    }

                }

            }

        });

        getCharges(venue, session, argument);

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

            var security_deposit_cls= '.security_deposit_'+rand;

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            if(occupant){

                if(function_date){

                    $.ajax({

                        type:'POST',

                        url:"{{ route('get.charges') }}",

                        data:{venue:venue, session:session, occupant:occupant, function_date:function_date},

                        success:function(data){

                            console.log(data.charges);                            
                        
                            if(data.booking=='Booking'){

                                toastr.info('Please select another date. Because this date is already booked.');

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

                                if(data.charges){

                                    if(data.checkVenue=='Insert'){

                                        $(charges_cls).val(data.charges.rate);

                                        $(gst_per_text_cls).text(data.venue.GSTper);

                                        $(gst_per_cls).val(data.venue.GSTper);

                                        $(security_deposit_cls).val(data.venue.security_deposit);

                                        if(data.venue){

                                            var percentage = data.venue.GSTper;
                                            
                                            var totalWidth = data.charges.rate;

                                            var security_deposit = data.venue.security_deposit;

                                            var new_width = (percentage / 100) * totalWidth;

                                            $(gst_amt_cls).val(new_width);

                                            $(total_cls).val(parseInt(data.charges.rate)+parseInt(new_width)+parseInt(security_deposit));
                                            
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

                                        toastr.info('This venue already added.');

                                    }
                                    
                                } else {

                                    toastr.info('Charges not available.');

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

                    toastr.info('Please Select Function Date');

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

                toastr.info('Please Select Occupant');

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
</script>

<script>
    function validateForm()
    {
        // Validate
        var title = $(".occupant").val();
        if (title=="" || title==null) {
            toastr.error("Please select occupant type");
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
            toastr.error("Please enter name");
            return false;
        } else {
            $('.name_error').text(""); 
        }
        
        if (memberEmail=="" || memberEmail==null) {
            toastr.error("Please enter email");
            return false;
        } else {
            $('.email_error').text(""); 
        }
        
        if (mobile=="" || mobile==null) {
            toastr.error("Please enter mobile no.");
            return false;
        } else {
            $('.mobile_no_error').text(""); 
        }
        
        if (address=="" || address==null) {
            toastr.error("Please enter address");
            return false;
        } else {
            $('.address_error').text(""); 
        }

        var func = $(".function_date").val();
        if (func=="" || func==null) {
            toastr.error("Please select function date");
            return false;
        } else {
            $('.function_date_error').text(""); 
        }

        var functy = $(".functionType").val();
        if (functy=="" || functy==null) {
            toastr.error("Please select function type");
            return false;
        } else {
            $('.functionType_error').text(""); 
        }

        var noofp = $(".noofPerson").val();
        if (noofp=="" || noofp==null) {
            toastr.error("Please enter number of person");
            return false;
        } else {
            $('.noofPerson_error').text(""); 
        }
        
        return true;
    }
</script>

<script>
    jQuery('.noofPerson').keyup(function () {     
        this.value = this.value.replace(/[^0-9\.]/g,'');
    });
</script>
<script type="text/javascript">
    $("#rowAdder").click(function () {
        
        $.ajax({

            type:'Get',

            url:"{{ route('append.extra.field') }}",

            success:function(data){

                console.log(data.data);
                $('#newinput').append(data.html);

            }

        });

    });

    $("body").on("click", "#DeleteRow", function () {

        if($(this).val()){

            var argument = $(this).val();

            var venue_cls = '.venue_'+argument;
            var venuee = $(venue_cls).val();

            var session_cls = '.session_'+argument;
            var sessionn = $(session_cls).val();

            var occupant = $('.occupant').val();
            
            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

                type:'POST',

                url:"{{ route('remove.extra.field') }}",

                data:{venue:venuee, session:sessionn, occupant:occupant},

                success:function(data){
                    console.log(data);
                }

            });
        } 
    
        $(this).parents("#row").remove();

    })
</script>
@endsection