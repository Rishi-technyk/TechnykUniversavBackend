@extends('layouts.web')
@section('content')

<style>
    .text-end {
        text-align: end;
    }

    input[type=text], input[type=number], input[type=date], input[type=email], textarea {
        width: 100% !important;
        padding: 20px 10px !important;
        box-sizing: border-box !important;
        border: solid 1px !important;
        border-radius: .25rem !important;
    }

    select { 
        border: solid 1px !important;
    }

    table {
        width: 100% !important;
    }

</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Room Booking Form</h5>
                </nav>
            </div>
        </div>

        <div class="row">
             
            <div class="col-lg-12">
                <div class="card mb-1 h-100">

                    <div class="card-header">
                        Room Booking
                    </div>

                    <form action="{{ route('banquet.store') }}" method="Post">

                        @csrf

                        <div class="card-body">
                            
                            <div class="row">

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Member ID <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="memberID" value="{{ $member->MemberID }}" placeholder="Member ID" readonly>
                                        
                                    </div>
                                </div>

                                <!-- <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Card ID <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="SC_ID" value="{{ $member->SC_ID }}" placeholder="Card ID" readonly>
                                    </div>
                                </div> -->

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Name <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" name="memberName" value="{{ $member->DisplayName }}" placeholder="Name" readonly>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Mobile No. <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control mobile_no" name="memberMobile" placeholder="Member Mobile No." required>
                                        <small class="text-danger mobile_no_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-6 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Email <b class="text-danger">*</b></label>
                                        <input type="text" class="form-control" value="{{ $member->Email }}" name="memberEmail" placeholder="Email" required>
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Address <b class="text-danger">*</b></label>
                                        @if($member->Address)
                                        <input type="text" class="form-control address" name="address" value="{{ $member->Address }}" placeholder="Address" required readonly>
                                        @else
                                        <input type="text" class="form-control address" name="address" placeholder="Address" required>
                                        @endif
                                        <small class="text-danger address_error"></small>
                                    </div>
                                </div>

                                <div class="col-lg-12 mt-4">
                                    <div class="form-group">
                                        <label>Booking Period</label>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="text-muted"> From Date <b class="text-danger">*</b></label>
                                        <input type="date" name="from_date" id="fromDate" class="form-control">
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-group">
                                        <label class="text-muted"> To Date <b class="text-danger">*</b></label>
                                        <input type="date" name="to_date" id="toDate" class="form-control">
                                    </div>
                                </div>                               

                            </div>

                            
                            <div class="card mt-4">

                                <div class="card-header">
                                    Room Details
                                </div>
                                
                                <div class="card-body" id="newinput">
                                    
                                    <div class="row">

                                        <div class="col-lg-12">
                                            
                                            <table class="table">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th scope="col">Room Category</th>
                                                        <th scope="col">Available</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <th scope="row">
                                                            <div class="form-group">
                                                                <select class="form-control" name="category_id[]">
                                                                    <option value="">Select Room</option>
                                                                    @foreach($category as $cate)
                                                                    <option value="{{ $cate->id }}">{{ $cate->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </th>
                                                        <td>
                                                            <b class="room_available_0">0</b>
                                                            <input type="hidden" class="room_available_input_0">
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>

                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="text-muted"> Occupant Type <b class="text-danger">*</b></label>
                                                <select class="form-control occupant_0" onchange="occupantType('0')" name="occupant_type[]" required>
                                                    <option value="">Select Occupant Type</option>
                                                    @foreach($occupan as $key => $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="text-muted">Guest Name</label>
                                                <input type="text" class="form-control guest_name_0" name="guest_name[]" placeholder="Guest Name">
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="text-muted">Mobile No.</label>
                                                <input type="number" class="form-control mobileNo_0" name="mobileNo[]" placeholder="Mobile No.">
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <label class="text-muted"> Email</label>
                                                <input type="text" class="form-control other_field email_0" value="{{ $member->Email }}" name="email[]" placeholder="Email" readonly>
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Adult</label>
                                                <input type="text" class="form-control adult_0" name="adult[]" placeholder="Adult">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Child</label>
                                                <input type="text" class="form-control child_0" name="child" placeholder="Child">
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Rooms to be Booked</label>
                                                <input type="text" class="form-control room_to_be_booked_0" name="room_to_be_booked" placeholder="Rooms to be Booked">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Tariff/Nite</label>
                                                <input type="text" class="form-control tariff_nite_0" name="tariff_nite" placeholder="Tariff/Nite">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Total Tariff</label>
                                                <input type="text" class="form-control total_tariff_0" name="total_tariff" placeholder="Total Tariff">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">GST Amount</label>
                                                <input type="text" class="form-control GST_amt_0" name="GST_amt" placeholder="GST Amount">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            <div class="form-group">
                                                <label class="text-muted">Gross Tariff</label>
                                                <input type="text" class="form-control gross_tariff_0" name="gross_tariff" placeholder="Gross Tariff">
                                            </div>
                                        </div>

                                        <div class="col-lg-3 mt-4">
                                            
                                        </div>

                                    </div>

                                </div>

                                <div class="card-footer text-end mt-4">
                                    <button type="button" class="btn-sm btn btn-success" id="rowAdder">Add Room</button>
                                </div>

                            </div>

                            <div class="row">

                                <div class="col-lg-12 mt-4">
                                    <div class="form-group">
                                        <label class="text-muted"> Remarks </label>
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
                                
                                <button class="btn btn-success" type="button" onclick="validateForm()">Submit</button>
                            </div>

                        </div>

                    </form>

                </div>
                
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

<script>    
    $(document).ready(function() {
        document.getElementById("fromDate").min = new Date().toISOString().split("T")[0];
        document.getElementById("toDate").min = new Date().toISOString().split("T")[0];
    });
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

                    if(data.data.additional_info=='Yes'){

                        $('.other_field').prop('readonly',false);

                    } else {

                        $('.other_field').prop('readonly',true);

                    }
                } else {

                    $('.other_field').prop('readonly',true);

                }

           }

        });
    }
</script>

    <script type="text/javascript">
        $("#rowAdder").click(function () {
            
            $.ajax({

                type:'Get',

                url:"{{ route('append.extra.field.room') }}",

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

    <script>
        function selectVanue(argument) {
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
            var venue = $(venue_cls).val();

            var occupant = $('.occupant').val();

            $.ajax({

               type:'POST',

               url:"{{ route('get.charges') }}",

               data:{venue:venue, session:session, occupant:occupant, function_date:function_date},

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

                                alert('This venue already added.');

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

                                    alert('Please select another date. Because this date is already booked.');

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

                                            alert('This venue already added.');

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
                    }

                } else {

                    alert('Please Select Occupant');

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
    </script>

    <script>
        function validateForm()
        {
            // Validate
            var title = $(".occupant").val();
            if (title=="" || title==null) {
                $('.occupant_error').text("Please select occupant type");
                return false;
            } else {
               $('.occupant_error').text(""); 
            }

            var mobile = $(".mobile_no").val();
            if (mobile=="" || mobile==null) {
                $('.mobile_no_error').text("Please enter mobile no.");
                return false;
            } else {
               $('.mobile_no_error').text(""); 
            }

            var address = $(".address").val();
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

            
          return true;
        }
    </script>

    <script>
        jQuery('.noofPerson').keyup(function () {     
          this.value = this.value.replace(/[^0-9\.]/g,'');
        });
    </script>
@endpush()
@endsection