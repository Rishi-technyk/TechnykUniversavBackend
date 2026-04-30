@extends('frontend.layouts.app')

@section('title', 'Activity Booking')
<style>
    .carousel-inner img {
        border-radius: 20px;
    }

    .card {
        border-radius: 20px !important;
    }

    .btn-success {
        background-color: #2e4374 !important;
        border-color: #2e4374 !important;
    }

    .p-d-0 {
        padding-bottom: 0% !important;
    }

    .fz-13 {
        font-size: 13px !important;
    }

    .scroll-box {
        height: 385px;
        overflow: auto;
    }

    .facility-card {
        transition: transform 0.3s ease;
        cursor: pointer;
    }

    .facility-card:hover {
        transform: translateY(-5px);
        border: 2px solid #2e4374 !important;
    }

    /* Chrome, Edge, Safari */
    .facility_list::-webkit-scrollbar {
        width: 6px;
    }

    .facility_list::-webkit-scrollbar-track {
        background: transparent;
    }

    .facility_list::-webkit-scrollbar-thumb {
        background: linear-gradient(180deg, #3b4a6b, #1f2a44);
        border-radius: 20px;
        transition: 0.3s;
    }

    .facility_list::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(180deg, #4c5d85, #2a3a5e);
    }

    /* Hide by default */
    .facility_list::-webkit-scrollbar {
        width: 0px;
    }

    /* Show on hover */
    .facility_list:hover::-webkit-scrollbar {
        width: 6px;
    }

    .badge-box {        
        color: white;
        width: auto;
        text-align: center;
        border-radius: 5px;
        padding: 2%;
    }

    .red {
        background-color: #f94b4b;
    }

    .success {
        background-color: #458475;
    }

    .warning {
        background-color: #ffff00c2;
    }

    .secondary {
        background-color: #808080cc;
    }

    .active-card {
        border: 2px solid #2e4374 !important;
    }

    .fs-20 {
        font-size: 20px !important;
    }

    .selected_slot_btn {
        background-color: #117a8b !important;
        color: white !important;
    }

    .collapsible {
        background-color: #2e4374;
        border-radius: 8px;
        color: white;
        cursor: pointer;
        padding: 6px;
        width: 100%;
        border: none;
        text-align: left;
        outline: none;
        font-size: 15px;
    }

    .active, .collapsible:hover {
        background-color: #555;
    }

    .content {
        padding: 0 18px;
        display: none;
        overflow: hidden;
        background-color: #f1f1f1;
    }

    .content p {
        font-size: 13px;
    }

    .down-arrow {
        float: right;
        margin-top: 2%;
    }

    .amount_summary {
        font-family: "Cabin", sans-serif;
        color: #6b6b6b;
        font-weight: 400;
    }

    .summary-empty-card b {
        font-family: "Cabin", sans-serif;
        color: #6b6b6b;
    }

    .slot_details p {
        margin-bottom: 0% !important;
    }

    .f-r {
        text-align: right;
    }

    .total-section {
        margin-top: 13%;
        border-top: solid 1px lightgray;
    }

    /* Style the tab content */
    .tabcontent {
        display: none;
        padding: 6px 12px;
        border: 1px solid #ccc;
        border-top: none;
    }

    .fev-active {
        color: lightseagreen !important;
    }

</style>
@section('content')

<div class="container">
    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Activity Booking</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>

        <div class="row">
            <div class="col-lg-6">

                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($facility as $key => $facility_info)

                            <div class="carousel-item {{ $key == 0 ? 'active' : '' }}">
                                @if($facility_info->first_image && file_exists(public_path($facility_info->first_image)))
                                <img class="d-block w-100" src="{{ asset($facility_info->first_image) }}" alt="First slide">
                                @else
                                <img class="d-block w-100" src="{{ asset('default-image.jpg') }}" alt="Default Image">
                                @endif
                            </div>

                        @endforeach
                    </div>
                </div>

            </div>
            <div class="col-lg-6">

                <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($facility as $key => $facility_item)
                            <div class="carousel-item card {{ $key == 0 ? 'active' : '' }}">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $facility_item->name }}</h5>
                                    <p class="card-text">{{ $facility_item->short_description }}</p>  
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>

        <form action="">

            <div class="row">
                
                <div class="col-lg-9">
                    
                    <div class="card mt-4">
                
                        <div class="card-body">

                            <div class="row">
                                
                                <div class="col-lg-9">
                                    <h4 class="fs-20">Choose an Activity</h4>
                                </div>
                                <div class="col-lg-3 text-right">
                                    <a href="javascript:" onclick="getFacility()" class="btn btn-success checkout-btn btn-sm change-activity">Change Activity</a>
                                </div>
                            </div>  <hr>            

                            <div class="row mt-2 facility_list scroll-box">
                                
                            </div>

                        </div>

                    </div>

                    <div class="card mt-4">
                
                        <div class="card-body">

                            <h4 class="fs-20">Select Slots</h4>
                            <hr>
                            <div class="row">

                                <div class="col-lg-3 mt-2">

                                    <input type="date" name="" class="form-control slot_date" min="{{ date('Y-m-d') }}" max="{{ $max_days }}" value="{{ date('Y-m-d') }}">

                                </div>
                                
                                <div class="col-lg-3 mt-2">

                                    <select class="form-control session" name="session_id" onchange="getSlots(this.value)">
                                        <option value="">Select Session</option>
                                        <option value="All">All Session</option>
                                        @foreach($session as $key => $sess)
                                        <option value="{{ $sess->id }}">{{ $sess->name }}</option>
                                        @endforeach
                                    </select>
                                    
                                </div>

                                <div class="col-lg-6 mt-2 fz-13">

                                    <div class="mt-2">
                                        <span class="badge-box red"></span>&nbsp; Booked &nbsp;
                                        <span class="badge-box success"></span>&nbsp; Available &nbsp; 
                                        <span class="badge-box warning"></span>&nbsp; Filling Fast &nbsp; 
                                        <span class="badge-box secondary"></span>&nbsp; Blocked Slots
                                    </div>

                                </div>

                            </div>

                            <div class="slot_list">
                                
                                

                            </div>

                        </div>

                    </div>

                    <div class="card mt-4" id="guestSection">
                
                        <div class="card-body">

                            <h4 class="fs-20">Guest Info</h4><hr>

                            <div class="row">

                                <div class="col-lg-3 mt-2">

                                    <div class="game_type_select"></div>
                                    
                                </div>

                                <div class="col-lg-7"></div>

                                <div class="col-lg-2 mt-2">
                                    
                                    

                                </div>

                            </div>

                            <div class="game_type_list"></div>

                        </div>

                    </div>

                </div>

                <div class="col-lg-3">
                    
                    <div class="card mt-4 amount-card mb-4">
                        <div class="card-header d-none summary-card">
                            <span class="slot_count">0</span> slot selected 
                            <input type="hidden" class="slot_count_val">
                        </div>

                        <div class="card-body summary-card">

                            <div class="mt-2 collapsible">
                                Slot Details <i class="fa fa-chevron-down novge down-arrow" aria-hidden="true"></i>
                            </div>
                            <div class="content mb-2 slot_details">
                                <p class="mt-2"><b>Slot Date : </b></p>
                                <p><b>Slot Time : </b></p>
                            </div>

                            <div class="row mt-4 amount_summary">
                                <div class="col-lg-8"><b>Basic Amount</b></div>
                                <div class="col-lg-4 f-r"><b>₹0</b></div>

                                <div class="col-lg-8"><b>Occupant Type Charge</b> <small></small> </div>
                                <div class="col-lg-4 f-r"><b>₹0</b></div>

                                <div class="col-lg-8"><b>GST Amt (0%)</b></div>
                                <div class="col-lg-4 f-r"><b>₹0</b></div>
                                <hr>
                                <div class="col-lg-8 total-section"><b>Net Amount</b></div>
                                <div class="col-lg-4 f-r total-section"><b>₹0</b></div>
                            </div>

                        </div>

                        <div class="card-body mb-4 summary-empty-card">

                            <div class="text-center empty-img">
                                <img src="https://hudle.in/images/cart-empty.svg" height="100">
                                <p>You have not selected any slots!</p>
                            </div>

                        </div>

                        <div class="card-footer f-r checkout_btn summary-card text-right">
                            
                        </div>

                        <div class="card-footer text-center summary-empty-card">
                            <b class="text-center">Please select slots</b>
                        </div>
                    </div>

                </div>

            </div>

        </form>

        <!-- <div class="modal fade bd-example-modal-lg" id="guestModal" tabindex="-1" role="dialog" aria-labelledby="guestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="width: 100%;" role="document">
                <div class="modal-content">
                    <div class="modal-body">

                        <div class="tab">
                            <button class="tablinks btn-sm active" onclick="openCity(event, 'London')">Members</button>
                            <button class="tablinks btn-sm" onclick="openCity(event, 'Paris')">Guests</button>
                            <button class="tablinks btn-sm" onclick="openCity(event, 'Tokyo')">Favorite</button>
                        </div>

                        <div id="London" class="tabcontent" style="display: block;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="member_list">
                                    
                                </tbody>
                            </table>
                        </div>

                        <div id="Paris" class="tabcontent">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="guest_list">
                                    
                                </tbody>
                            </table>
                        </div>

                        <div id="Tokyo" class="tabcontent">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="favorite_list">
                                    
                                </tbody>
                            </table>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success btn-sm" data-dismiss="modal" id="myBtn">Add New Player</button> 
                        <button type="button" class="btn btn-success btn-sm" onclick="checkPlayerAndClose()">Proceed</button> 
                    </div>
                </div>
            </div>
        </div> -->

        <!-- <button type="button" class="btn btn-primary d-none" id="guestModalBtn" data-toggle="modal" data-target="#guestModal" data-backdrop="static" data-keyboard="false"></button> -->

        <!-- Button trigger modal -->
        <button type="button" class="btn btn-primary d-none" id="guestModalBtn" data-toggle="modal" data-target="#guestModal" data-backdrop="static" data-keyboard="false">
            Guest Modal
        </button>

        <!-- Modal -->
        <div class="modal fade" id="guestModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="guestModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-body">
                        
                        <div class="tab">
                            <button class="tablinks btn-sm active member_tab" onclick="openCity(event, 'London')">Members</button>
                            <button class="tablinks btn-sm" onclick="openCity(event, 'Paris')">Guests</button>
                            <button class="tablinks btn-sm" onclick="openCity(event, 'Tokyo')">Favorite</button>
                        </div>

                        <div id="London" class="tabcontent" style="display: block;">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="member_list">
                                    
                                </tbody>
                            </table>
                        </div>

                        <div id="Paris" class="tabcontent">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="guest_list">
                                    
                                </tbody>
                            </table>
                        </div>

                        <div id="Tokyo" class="tabcontent">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                      <th scope="col">#</th>
                                      <th scope="col">Name</th>
                                      <th scope="col">Email / Mobile</th>
                                      <th scope="col">Favorite</th>
                                      <th scope="col"></th>
                                    </tr>
                                </thead>
                                <tbody class="favorite_list">
                                    
                                </tbody>
                            </table>
                        </div>

                        <div id="addGuest" class="tabcontent">
                            <hr>
                            <div class="row">
                                <div class="col-lg-4">
                                    <input type="text" class="form-control guest_name" placeholder="Guest Name">
                                </div>
                                <div class="col-lg-4">
                                    <input type="text" class="form-control guest_mobile" placeholder="Guest Mobile">
                                </div>
                                <div class="col-lg-4">
                                    <input type="email" class="form-control guest_email" placeholder="Guest Email">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-lg-12 text-right mt-4">
                                    <button type="button" class="btn btn-success btn-sm" onclick="addGuest()">Add Guest</button>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="openCity(event, 'addGuest')">Add New Player</button>
                        <button type="button" class="btn btn-success btn-sm" onclick="checkPlayerAndClose()">Proceed</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<input type="hidden" class="name" value="{{ $member->DisplayName }}">
<input type="hidden" class="mobile" value="{{ $member->Mobile }}">
<input type="hidden" name="guest_no" value="0" class="guest_no">
<input type="hidden" value="{{ date('Y-m-d') }}" class="today_date">
<input type="hidden" class="slot_index_no" value="">
<input type="hidden" class="no_of_playes" value="">

@endsection

@section('script')
<script>
    $(document).ready(function() {
        setTimeout(function() {
            // guest_list();
            getFacility();
        }, 500);

        $('.summary-card').hide();
        $('.change-activity').hide();
    });
</script>
<script>
    let FacilityID = '';

    function getFacility() {
        $('.facility_list').addClass("scroll-box");
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('get.facility') }}",

            data:{facility_id:FacilityID},

            success:function(data){
                console.log(data);

                $('.facility_list').html(data);
            }

        });
    }
</script>
<script>
    function selectFacility(fc_id) {
        FacilityID = fc_id;

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('select.facility') }}",

            data:{facility_id:FacilityID},

            success:function(data){
                console.log(data);
                $('.facility_list').removeClass("scroll-box");
                $('.change-activity').show();
                $('.facility_list').html(data.view);
                $('.game_type_select').html(data.game_type);
                $('.slot_list').html('');
                $('.session').val('');
                $('.game_type').val('');
                $('.game_type_list').html('');
            }

        });
    }
</script>
<script>
    function getSlots(session='') {

        var facility_id = FacilityID;
        
        if(facility_id){

            var slot_date = $('.slot_date').val();

            var cc_id       = $('.card_id').val(); 

            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

                type:'POST',

                url:"{{ route('get.slots') }}",

                data:{session:session, facility_id:facility_id, slot_date:slot_date, card_id:cc_id},

                success:function(data){
                    console.log(data);
                    $('.slot_list').html(data);
                }

            });

        } else {
            alert('Please Select Facility.');
        }

    }
</script>
<script>
    function getTodaySlot() {

        var today = $('.today_date').val();

        $('.slot_date').val(today);

        var session = $('.session').val();

        getSlots(session);
    }
</script>

<script>
    function getNextSlot() {

        var slot_date   = $('.last_date').val();
        var session     = $('.session').val();
        var cc_id       = $('.card_id').val(); 
        var facility_id = FacilityID;

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('get.slots') }}",

            data:{session:session, facility_id:facility_id, slot_date:slot_date, card_id:cc_id},

            success:function(data){
                console.log(data);                
                $('.slot_list').html(data);
                $(".previus_btn").attr("disabled", false);
            }

        });
    }
</script>

<script>
    function getPrevSlot() {

        var slot_date   = $('.prev_date').val();
        var session     = $('.session').val();
        var cc_id       = $('.card_id').val();
        var facility_id = FacilityID;

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('get.slots') }}",

            data:{session:session, facility_id:facility_id, slot_date:slot_date, card_id:cc_id},

            success:function(data){
                console.log(data);
                $(".previus_btn").attr("disabled", false);
                $('.slot_list').html(data);
            }

        });
    }
</script>

<script>
    function bookSlot(slot_id, slot_key, facility_id) 
    {
        var slot_date       = '.slot_date_'+slot_id+'_'+slot_key;

        var slot_date       = $(slot_date).val();

        var btn_slot_cls    = '.btn_slot_'+slot_id+'_'+slot_key;

        var element         = $(btn_slot_cls);
        
        var game_type_id    = $('.game_type').val();

        var session_id      = $('.session').val();

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('add.game.in.session') }}",

            data:{
                slot_id:slot_id, 
                facility_id:facility_id, 
                slot_date:slot_date,
                game_type_id:game_type_id,
                session_id:session_id,
                // occupant_ids:occupant_ids,
                // player_names:player_names,
                // player_emails:player_emails,
                // player_mobiles:player_mobiles,
            },

            success:function(data){
                console.log(data);
                if(data.status){
                    if (element.hasClass('btn-secondary')) {
                        // $(btn_slot_cls).removeClass("selected_slot_btn");
                        $(btn_slot_cls).removeClass("btn-secondary");
                        $(btn_slot_cls).addClass("btn-success");                        
                    } else {
                        $(btn_slot_cls).addClass("btn-secondary");
                        $(btn_slot_cls).removeClass("btn-success");
                    }
                    getSummaryCard(data.card_id);
                } else {
                    toastr.error('Try Again');
                }
            }

        });

    }
</script>
<script>
    
    function getSummaryCard(booking_id) {

        var game_type_id = $('.game_type').val();

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('get.summary.card') }}",

            data:{
                booking_id:booking_id,
                game_type_id:game_type_id,
            },

            success:function(data){
                console.log(data);
                $('.summary-empty-card').hide();
                $('.summary-card').show();
                $('.slot_count').html(data.slot_count);
                $('.slot_count_val').val(data.slot_count);
                $('.slot_details').html(data.slot_details);
                $('.amount_summary').html(data.amount_summary);
                $('.checkout_btn').html(data.checkout_btn);
                $('.game_type_list').html(data.guest_list);
            }

        });
    }
</script>
<script>
    var coll = document.getElementsByClassName("collapsible");
    var i;

    for (i = 0; i < coll.length; i++) {
      coll[i].addEventListener("click", function() {
        this.classList.toggle("active");
        var content = this.nextElementSibling;
        if (content.style.display === "block") {
          content.style.display = "none";
        } else {
          content.style.display = "block";
        }
      });
    }
</script>
<script>
    function getGameType(game_type) {

        var facility_id = FacilityID;

        if(facility_id){  

            var cc_id = $('.card_id').val();          

            if(cc_id){

                $.ajaxSetup({

                    headers: {

                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                    }

                });

                $.ajax({

                    type:'POST',

                    url:"{{ route('get.game_type') }}",

                    data:{game_type:game_type, facility_id:facility_id},

                    success:function(data){
                        console.log(data);
                        $(".previus_btn").attr("disabled", true);
                        $(".today-btn").attr("disabled", true);
                        $(".next_btn").attr("disabled", true);
                        $(".slo_btn").attr("disabled", true);

                        $('.game_type_list').html(data.view);
                        getSummaryCard(data.card_id);
                    }

                });

            } else {

                toastr.warning('Please Select Any Slot.');
                return false;

            }

        } else {
            toastr.error('Please Select Facility.');
            return false;
        } 
    }
</script>
<script>
    let no_of_playes = '';
    let pogition = '';
    function openModal(argument, pogition) {
       
        if($('.game_type').val()){
            $('#guestModalBtn').click();
            guest_list();
        } else {
            toastr.warning('Please Select Game Type.');
        }

        $('.slot_index_no').val(pogition);
        $('.no_of_playes').val(argument);

        no_of_playes = argument;
        pogition = pogition;

    }
</script>
<script>
    function guest_list() {
        $.ajax({

            type:'Get',

            url:"{{ route('get.guest.list') }}",

            success:function(data){
                console.log(data);
                $('.guest_list').html(data.guest_list);
                $('.member_list').html(data.member_list);
                $('.favorite_list').html(data.favorite_list);
            }

        });
    }
</script>
<script>
    function openCity(evt, cityName) {
      var i, tabcontent, tablinks;
      tabcontent = document.getElementsByClassName("tabcontent");
      for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
      }
      tablinks = document.getElementsByClassName("tablinks");
      for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
      }
      document.getElementById(cityName).style.display = "block";
      evt.currentTarget.className += " active";
    }
</script>
<script>
    function selectGuest(argument, tab) {

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('get.guest.info') }}",

            data:{guest_id:argument, tab:tab},

            success:function(data){

                console.log(data);

                var no_of_playes =  $('.no_of_playes').val();
               
                var pogition = $('.slot_index_no').val();

                for(var i = 1; i <= no_of_playes; i++)//see that I removed the $ preceeding the `for` keyword, it should not have been there
                {
                    var player_name_clg     = '.player_name_'+i+'_'+pogition;
                    var player_email_clg    = '.player_email_'+i+'_'+pogition;
                    var player_mobile_clg   = '.player_mobile_'+i+'_'+pogition;
                    var occupant_clg        = '.occupant_id_'+i+'_'+pogition;
                    
                    if($(player_name_clg).val()==''){

                        if(data.from=='member'){
                         
                            $(player_name_clg).val(data.player.DisplayName);
                            $(player_email_clg).val(data.player.Email);
                            $(player_mobile_clg).val(data.player.Mobile);
                            $(occupant_clg).val('0');
                            
                        } else {
                         
                            $(player_name_clg).val(data.player.name);
                            $(player_email_clg).val(data.player.email);
                            $(player_mobile_clg).val(data.player.mobile);
                            $(occupant_clg).val(data.player.occupant_id);

                        }                        
                        
                        $(player_name_clg).prop("readonly", true);
                        $(player_email_clg).prop("readonly", true);
                        $(player_mobile_clg).prop("readonly", true);
                        break;
                    }
                    
                    
                }
                
            }

        });
    }
</script>

<script>
    function favoriteMe(argument) {
        
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('favorite.active') }}",

            data:{id:argument},

            success:function(data){
                console.log(data);
                if(data.status){
                    guest_list();
                } else {
                    toastr.error(data.msg);
                }
            }

        });

    }
</script>

<script>
    function removePlayer(argument) {

        if(confirm('Can you want to delete this player?')){
            
            $.ajaxSetup({

                headers: {

                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

                }

            });

            $.ajax({

                type:'POST',

                url:"{{ route('remove.player') }}",

                data:{id:argument},

                success:function(data){
                    console.log(data);
                    guest_list();
                }

            });

        }
    }
</script>

<script>
    function checkPlayerAndClose(argument) {

        var no_of_playes =  $('.no_of_playes').val();
               
        var pogition = $('.slot_index_no').val();

        const occupant_ids      = [];        
        const player_names      = [];        
        const player_emails     = [];        
        const player_mobiles    = [];        

        for(var i = 1; i <= no_of_playes; i++)//see that I removed the $ preceeding the `for` keyword, it should not have been there
        {
            var player_name = ".player_name_"+i+"_"+pogition;
            if($(player_name).val() !=''){
                player_names.push($(player_name).val());
            } else {
                toastr.error('Please Enter Player Name');
                return false;
            }

            var player_email = ".player_email_"+i+"_"+pogition;
            if($(player_email).val() !=''){
                player_emails.push($(player_email).val());
            } else {
                toastr.error('Please Enter Player Email');
                return false;
            }

            var player_mobile = ".player_mobile_"+i+"_"+pogition;
            if($(player_mobile).val() !=''){
                player_mobiles.push($(player_mobile).val());
            } else {
                toastr.error('Please Enter Player Mobile');
                return false;
            }
            
        }

        storeGuestInTable();
    }
</script>
<script>
    
    function storeGuestInTable() {

        var no_of_playes =  $('.no_of_playes').val();
               
        var pogition = $('.slot_index_no').val();

        var facility_id = FacilityID;

        const slot_ids          = [];        
        const slot_dates        = [];        
        const occupant_ids      = [];        
        const player_names      = [];        
        const player_emails     = [];        
        const player_mobiles    = [];        

        for(var i = 1; i <= no_of_playes; i++)//see that I removed the $ preceeding the `for` keyword, it should not have been there
        {

            var slot_id = ".slot_id_"+i+"_"+pogition;
            if($(slot_id).val() !=''){
                slot_ids.push($(slot_id).val());
            }

            var slot_date = ".slot_date_"+i+"_"+pogition;
            if($(slot_date).val() !=''){
                slot_dates.push($(slot_date).val());
            }

            var occupant_id = ".occupant_id_"+i+"_"+pogition;
            if($(occupant_id).val() !=''){
                occupant_ids.push($(occupant_id).val());
            }

            var player_name = ".player_name_"+i+"_"+pogition;
            if($(player_name).val() !=''){
                player_names.push($(player_name).val());
            }

            var player_email = ".player_email_"+i+"_"+pogition;
            if($(player_email).val() !=''){
                player_emails.push($(player_email).val());
            }

            var player_mobile = ".player_mobile_"+i+"_"+pogition;
            if($(player_mobile).val() !=''){
                player_mobiles.push($(player_mobile).val());
            }
            
        }

        var game_type_id = $('.game_type').val();

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'POST',

            url:"{{ route('store.guest.in.table') }}",

            data:{
                slot_ids:slot_ids,
                slot_dates:slot_dates,
                game_type_id:facility_id,
                facility_id:facility_id,
                occupant_ids:occupant_ids,
                player_names:player_names,
                player_emails:player_emails,
                player_mobiles:player_mobiles,
            },

            success:function(data){
                console.log(data);
                if(data.status){
                    // $("#guestModal").hide();
                    
                    closeModal(data.card_id);
                } else {
                    toastr.error('Try Again');
                }
            }

        });
        
        
    }
</script>

<script>
    function closeModal(card_id) {
        $('#guestModal').modal('hide');

        setTimeout(function () {
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            $('.modal-backdrop').remove();
        }, 300);

        getSummaryCard(card_id);
        
    }
</script>

<script>
    function modifyPlayer(key) {        

        var cc_id = $('.card_id').val(); 
        
        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'Post',

            url:"{{ route('get.modify.guest.list') }}",

            data:{
                guest_info_id:key,
                cc_id:cc_id,
            },

            success:function(data){
                console.log(data);
                // $('#guestModalBtn').click();
                $('#guestModal').modal('show');
                $('.guest_list').html(data.guest_list);
                $('.member_list').html(data.member_list);
                $('.favorite_list').html(data.favorite_list);
            }

        });
    }
</script>

<script>
    function addGuest() {

        var guest_name  = $('.guest_name').val();
        var guest_email = $('.guest_email').val();
        var guest_mobile= $('.guest_mobile').val();

        if(guest_name==''){
            toastr.error('Please Enter Guest Name');
            return false;
        }

         if(guest_email==''){
            toastr.error('Please Enter Guest Email');
            return false;
        }

         if(guest_mobile==''){
            toastr.error('Please Enter Guest Mobile');
            return false;
        }

        $.ajaxSetup({

            headers: {

                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')

            }

        });

        $.ajax({

            type:'Post',

            url:"{{ route('store.guest') }}",

            data:{
                name:guest_name,
                email:guest_email,
                mobile:guest_mobile,
            },

            success:function(data){
                console.log(data);
                toastr.success('Guest Added Successfully');
                $('.guest_name').val('');
                $('.guest_email').val('');
                $('.guest_mobile').val('');                
                guest_list();                
            }

        });
    }
</script>
@endsection