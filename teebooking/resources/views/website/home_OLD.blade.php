@extends('layouts.web')
@section('content')
<style>
.timeline-container .timeline-list .inactive {
    background-color: #d5d5d5;
}

.is-available {
    background-color: #ccecc2;
}

.is-booked {
    background-color: #fca6a6;
}
</style>
<!--============================== Content Start ==============================-->

<div style="background-color: #eee;" class="p-4">
<div class="card mb-4 ">
<div class="card-body">
<div class="content-container pb-0" >
    <div class="container timeline-container">
        <div class="row">
            <div class="col-1">
                <div class="row">
                    <div class="col-12 prev-btn">
                        <span class="fa fa-angle-left"></span>
                    </div>
                </div>
            </div>
            <div class="col-10">
                <div>


                    {{-- Get the current date --}}
                    @php
                    $currentDate = now();
                    @endphp

                    {{-- Clone the current date --}}
                    @php
                    $timelineDate = $currentDate->copy();
                    $timelineDate->modify("-1 day");
                    $day= \App\CPU\Helpers::get_setting('day_open_booking');
                    @endphp
                    <!--   -->

                    <div class="row timeline-list">
                        @for($i = 0; $i < 14; $i++) @php $timelineDate->modify("+1 day");
                            $formattedDate = $timelineDate->format('d M');
                            $formattedDay = $timelineDate->format('D');
                            $date = $timelineDate->format('Y-m-d');
                            @endphp

                            <div
                                class="col-3 col-sm-2 col-lg-1 timeline-item {{ $selectedDate==$date?'active':''}} {{ ($i>$day || $i==0)?'inactive':''}}">
                                @if(($i>$day || $i==0))
                                <a href="javascript:void();" class="timeline-date " onClick="">
                                    <span class="d-block"><strong>{{ strtolower($formattedDay) }}</strong></span>
                                    <span class="d-block">{{ $formattedDate }}</span>
                                </a>
                                @else
                                <a href="javascript:void();" class="timeline-date " onClick="submitForm('{{$date}}')">
                                    <span class="d-block"><strong>{{ strtolower($formattedDay) }}</strong></span>
                                    <span class="d-block">{{ $formattedDate }}</span>
                                </a>
                                @endif
                            </div>
                            @endfor

                    </div>
                </div>
            </div>
            <div class="col-1">
                <div class="row">
                    <div class="col-12 next-btn">
                        <span class="fa fa-angle-right"></span>
                    </div>
                </div>
            </div>
        </div>

        {!!$notesMessage!!}<br>
        @if($windowMessage)
        <span class="text-black"> Note: {!!$windowMessage!!}</span>
        @endif
    </div>
</div>
<!--============================== Content Start ==============================-->

<!-- !!- ===================================== Content Start ======================== -!! -->


<div class="container">

    <div class="row os-animation" data-os-animation="fadeIn" data-os-animation-delay="0.5s">
        <div class="col-lg-12">

            <div class="main-tab-box">
                <nav>
                    <div class="nav nav-tabs mb-3" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-tab-1-tab" data-bs-toggle="tab"
                            data-bs-target="#nav-tab-1" aria-controls="nav-tab-1" aria-selected="true">Tee
                            Sheet</button>
                        <button class="nav-link" id="nav-tab-3-tab" data-bs-toggle="tab" data-bs-target="#nav-tab-3"
                            aria-controls="nav-tab-3" aria-selected="false">My Bookings</button>
                        <button class="nav-link" id="nav-tab-2-tab" data-bs-toggle="tab" data-bs-target="#nav-tab-2"
                            aria-controls="nav-tab-2" aria-selected="false">Group Booking </button>

                        <button class="nav-link" id="nav-tab-4-tab" data-bs-toggle="tab" data-bs-target="#nav-tab-4"
                            aria-controls="nav-tab-4" aria-selected="false">Manage Buddies and Groups</button>
                    </div>
                </nav>
                <div class="tab-content py-3 border bg-light" id="nav-tabContent">
                    <div class="tab-pane fade active show" id="nav-tab-1" aria-labelledby="nav-tab-1-tab">
                        <div class="product-content-box">

                            <!-- !!- ===================================== Inner Content Start ======================== -!! -->


                            <!-- !!- ===================================== Inner Content Start ======================== -!! -->

                            <!-- !!- ===================================== Inner Content Start ======================== -!! -->
                            <form action="{{route('home')}}" method="get" id="searchForm" class="loading-btn-form">

                                <input type="hidden" name="date" value="{{$selectedDate}}" id="searchDate">
                                <div class="user-info-box">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="user-info-list">
                                                <div class="user-info-icon">
                                                    <i class="fa-regular fa-flag" style="color: #030303;"></i>
                                                </div>
                                                <label for="exampleInputEmail1">Tee Holes</label>
                                                <select name="teeHole" class="form-select"
                                                    aria-label="Default select example">
                                                    <option value="">All</option>
                                                    @foreach ($teeHoles as $value)
                                                    <option value="{{$value->id}}"
                                                        {{($selectedHole==$value->id)?'selected':''}}>
                                                        {{$value->hole_number}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="user-info-list">
                                                <div class="user-info-icon">
                                                    <i class="fa-regular fa-clock" style="color: #030303;"></i>
                                                </div>
                                                <label for="exampleInputEmail1">Session</label>
                                                <select name="session_name" class="form-select"
                                                    aria-label="Default select example">
                                                    <option value="" selected>All Day </option>
                                                    @foreach ($teeSessions as $value)
                                                    <option value="{{$value->id}}"
                                                        {{($selectedSession==$value->id)?'selected':''}}>
                                                        {{$value->session_name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="user-info-list">
                                                <div class="user-info-icon">
                                                    <i class="fa-regular fa-eye" style="color: #000000;"></i>
                                                </div>
                                                <label for="exampleInputEmail1">Show</label>
                                                <select name="show_time" class="form-select"
                                                    aria-label="Default select example">
                                                    <option value="" selected>All Times</option>
                                                    <option value="1">Only Show Available </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>


                            <!-- !!- ===================================== Inner Content End ======================== -!! -->

                            <!-- !!- ===================================== Inner Content Start ======================== -!! -->
                            <!-- <div id="accordion-1" class="calendar-accordion">
                                    <div class="head">
                                        <i class="fa-regular fa-calendar arrow-2" style="color: #1a1a1a;"></i>
                                        <h2> Selected Date </h2>
                                        <i class="fas fa-angle-down arrow"></i>
                                    </div>
                                    <div class="content">
                                        <div id="calendar" class="calendar">
                                            <div class="calendar-title">
                                                <div class="calendar-title-text"></div>
                                                <div class="calendar-button-group">
                                                    <button id="prevMonth">&lt;</button>
                                                    <button id="today">Today</button>
                                                    <button id="nextMonth">&gt;</button>
                                                </div>
                                            </div>
                                            <div class="calendar-day-name"></div>
                                            <div class="calendar-dates"></div>
                                        </div>
                                    </div>
                                </div> -->
                            <!-- !!- ===================================== Inner Content End ======================== -!! -->

                            <!-- !!- ===================================== Inner Content Start ======================== -!! -->

                            <div class="user-data-content-box">
                                <ul class="user-data-list">
                                    @foreach ($teeSheets as $teeSheet)

                                    @if(@$teeSheet->is_locked_by_admin==0)
                                    <li
                                        class="user-data-item {{($teeSheet->data->available_players==4)?'is-available':'is-booked'}}">
                                        <div class="udi-left">
                                            <div class="udi-l-btn-box">
                                                <a href="javascript:void();" class="udi-btn">
                                                    {{$teeSheet->tee_time}} </a>
                                            </div>
                                        </div>
                                        <div class="udi-middle">
                                            <div class="udi-m-left">
                                                <div class="udi-ml-text">
                                                    <h4> Available </h4>
                                                    <p> 
                                                     
                                                        {{$teeSheet->data->available_players}}
                                                      

                                                        
                                                    
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="udi-m-right">
                                                <div id="accordion-1">
                                                    <div class="head ps-0">
                                                        <h2> QUICK BOOK </h2>
                                                        <i class="fas fa-angle-down arrow"></i>
                                                    </div>
                                                    <div class="content">
                                                        <div class="user-inco-content">
                                                            <div class="user-inco-left">
                                                                <ul class="user-inco-list">
                                                                    <li class="user-inco-item">
                                                                        @if($teeSheet->data->player1_name)
                                                                        {{$teeSheet->data->player1_name}}/{{$teeSheet->data->player1_member_id}}
                                                                        @else
                                                                        Yourself
                                                                        @endif
                                                                    </li>
                                                                    <li class="user-inco-item">
                                                                        @if($teeSheet->data->player2_name)
                                                                        {{$teeSheet->data->player2_name}}/{{$teeSheet->data->player2_member_id}}
                                                                        @else
                                                                        Player 2
                                                                        @endif
                                                                    </li>
                                                                    <li class="user-inco-item">
                                                                        @if($teeSheet->data->player3_name)
                                                                        {{$teeSheet->data->player3_name}}/{{$teeSheet->data->player3_member_id}}
                                                                        @else
                                                                        Player 3
                                                                        @endif
                                                                    </li>
                                                                    <li class="user-inco-item">
                                                                        @if($teeSheet->data->player4_name)
                                                                        {{$teeSheet->data->player4_name}}/{{$teeSheet->data->player4_member_id}}
                                                                        @else
                                                                        Player 4
                                                                        @endif
                                                                    </li>



                                                                </ul>
                                                            </div>
                                                            <!-- <div class="user-inco-right">
                                                                <ul class="user-inco-list">
                                                                    <li class="user-inco-item"> <a href="#"> <span>
                                                                                Group </span> </a> </li>
                                                                    <li class="user-inco-item"> <a href="#">
                                                                            Caldwell team <span> ( 2 players )
                                                                            </span> </a> </li>
                                                                    <li class="user-inco-item"> <a href="#"> family
                                                                            <span> ( 3 players) </span> </a>
                                                                    </li>
                                                                    <li class="user-inco-item"> <a href="#"> hopkins
                                                                            groups <span> (1 players )
                                                                            </span> </a> </li>
                                                                </ul>
                                                            </div> -->
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="udi-right">
                                            <div class="udi-r-text">
                                                Tee : {{$teeSheet->hole_number}}
                                                @if($is_booking_exist && $teeSheet->data->available_players == 4 || Auth::user()->Status=="Out Station") 
                                                    <p>-</p> 
                                                @elseif (Auth::user()->id == $teeSheet->data->player1_id)
                                                    <button class="btn btn-primary book-now-btn"
                                                        data-member-status="{{ Auth::user()->Status}}"
                                                        data-booking-date="{{ $teeSheet->teeBooking->booking_date }}"
                                                        data-tee-time="{{ \Carbon\Carbon::parse($teeSheet->tee_time)->format('g:iA') }}"
                                                        data-session-name="{{ $teeSheet->session->session_name }}"
                                                        data-tee-off-hole="{{ $teeSheet->teeHole->hole_number }}"
                                                        data-tee-booking-id="{{ $teeSheet->tee_booking_id }}"
                                                        data-tee-sheet-id="{{ $teeSheet->id }}"
                                                        data-player1-member-id="{{ $teeSheet->data->player1_id }}"
                                                        data-player2-member-id="{{ $teeSheet->data->player2_id }}"
                                                        data-player3-member-id="{{ $teeSheet->data->player3_id }}"
                                                        data-player4-member-id="{{ $teeSheet->data->player4_id }}"
                                                        data-player1-name="{{ $teeSheet->data->player1_name }}/{{ $teeSheet->data->player1_member_id }}"
                                                        data-player2-name="{{ $teeSheet->data->player2_name }}/{{ $teeSheet->data->player2_member_id }}"
                                                        data-player3-name="{{ $teeSheet->data->player3_name }}/{{ $teeSheet->data->player3_member_id }}"
                                                        data-player4-name="{{ $teeSheet->data->player4_name }}/{{ $teeSheet->data->player4_member_id }}"
                                                        data-tee-booking-detail-id="{{ $teeSheet->data->tee_booking_detail_id }}">
                                                        @if(App\Models\TeeBookingDetails::date_check($teeSheet->teeBooking->booking_date))
                                                            @if($teeSheet->data->available_players==4)
                                                                Book
                                                            @else
                                                                Modify
                                                            @endif
                                                        @endif
    
                                                    </button>
                                                @elseif (Auth::user()->id != $teeSheet->data->player1_id && $teeSheet->data->available_players==4)
                                                    <button class="btn btn-primary book-now-btn"
                                                        data-member-status="{{ Auth::user()->Status}}"
                                                        data-booking-date="{{ $teeSheet->teeBooking->booking_date }}"
                                                        data-tee-time="{{ \Carbon\Carbon::parse($teeSheet->tee_time)->format('g:iA') }}"
                                                        data-session-name="{{ $teeSheet->session->session_name }}"
                                                        data-tee-off-hole="{{ $teeSheet->teeHole->hole_number }}"
                                                        data-tee-booking-id="{{ $teeSheet->tee_booking_id }}"
                                                        data-tee-sheet-id="{{ $teeSheet->id }}"
                                                        data-player1-member-id="{{ $teeSheet->data->player1_id }}"
                                                        data-player2-member-id="{{ $teeSheet->data->player2_id }}"
                                                        data-player3-member-id="{{ $teeSheet->data->player3_id }}"
                                                        data-player4-member-id="{{ $teeSheet->data->player4_id }}"
                                                        data-player1-name="{{ $teeSheet->data->player1_name }}/{{ $teeSheet->data->player1_member_id }}"
                                                        data-player2-name="{{ $teeSheet->data->player2_name }}/{{ $teeSheet->data->player2_member_id }}"
                                                        data-player3-name="{{ $teeSheet->data->player3_name }}/{{ $teeSheet->data->player3_member_id }}"
                                                        data-player4-name="{{ $teeSheet->data->player4_name }}/{{ $teeSheet->data->player4_member_id }}"
                                                        data-tee-booking-detail-id="{{ $teeSheet->data->tee_booking_detail_id }}">
                                                        @if(App\Models\TeeBookingDetails::date_check($teeSheet->teeBooking->booking_date))
                                                            @if($teeSheet->data->available_players==4)
                                                                Book
                                                            @else
                                                                Modify
                                                            @endif
                                                        @endif
    
                                                    </button>
                                                @else 
                                                    <p></p>
                                                @endif
                                               
                                            </div>
                                        </div>
                                    </li>
                                    @endif

                                    @endforeach

                                </ul>
                            </div>
                            <!-- !!- ===================================== Inner Content End ======================== -!! -->

                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-tab-2" aria-labelledby="nav-tab-2-tab">
                        <div class="product-content-box">
                            <!-- !!- ===================================== Content Start ======================== -!! -->

                            <div class="user-list">
                                Coming Soon
                                <!-- <a href="#" class="user-booking">
                                    <div class="user-booking-box">
                                        <div class="user-upper">
                                            <div class="user-u-icon"> <i class="fa-regular fa-calendar arrow-2"
                                                    style="color: #1a1a1a;">
                                                </i> </div>
                                            <h4> Tee Time </h4>
                                            <p> Sunday, July 29,2018 @ 8:22 AM </p>
                                        </div>
                                        <div class="user-middle">
                                            <div class="user-m-data">
                                                <p>Confirmation</p>
                                                <h4>RI00007887</h4>
                                            </div>
                                            <div class="user-m-data">
                                                <p>Course</p>
                                                <h4>The Royel Ibrox Golf Course</h4>
                                            </div>
                                            <div class="user-m-data">
                                                <p>Hole</p>
                                                <h4>18</h4>
                                            </div>
                                            <div class="user-m-data">
                                                <p>Tee</p>
                                                <h4>1</h4>
                                            </div>
                                        </div>
                                        <div class="user-lower">
                                            <p> Players </p>
                                            <div class="user-inco"> Roger Smith - <span> Cart,No,No rental </span>
                                            </div>
                                            <div class="user-inco"> John Wood - <span> Cart,No,No rental </span>
                                            </div>
                                        </div>
                                        <a href="#" class="ubb-icon"> <i class="fa-solid fa-arrow-right"
                                                style="color: #000000;"> </i>
                                        </a>
                                    </div>
                                </a> -->



                            </div>



                            <!-- !!- ===================================== Content End ======================== -!! -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-tab-3" aria-labelledby="nav-tab-3-tab">
                        <div class="product-content-box">
                            <!-- !!- ===================================== Content Start ======================== -!! -->


                            @foreach (App\Models\TeeBookingDetails::get_player_booking(auth()->user()->id) as $booking)


                            <div class="confirm-lower">
                                <div class="confirm-lower-left">
                                    <ul class="cll-list">
                                        <li class="cll-item">
                                            <div class="cll-heading"> Confirmation Id</div>
                                            <h3>{{ $booking['bookingId'] }}</h3>
                                        </li>
                                        <li class="cll-item">
                                            <!-- <div class="cll-heading"> The Royal ibrox Golf Course </div> -->
                                            <h3>{{ \Carbon\Carbon::parse($booking['booking_date'])->format('D M d, Y') }}
                                                {{ \Carbon\Carbon::parse($booking['tee_sheet_time'])->format('g:i A') }}
                                            </h3>

                                        </li>

                                        <li class="cll-item">
                                            <div class="cll-heading"> Players </div>
                                            <div class="cllh-data">
                                                <h5>
                                                    @if($booking['player1_name'])
                                                    1. {{ $booking['player1_name'] }}/{{$booking['player1_member_id']}}
                                                    @else
                                                    1. Yourself
                                                    @endif
                                                </h5>
                                                <h4>
                                                    @if($booking['player2_name'])
                                                    2. {{ $booking['player2_name'] }}/{{$booking['player2_member_id']}}
                                                    @else
                                                    2. Player 2
                                                    @endif
                                                </h4>
                                                <h4>
                                                    @if($booking['player3_name'])
                                                    3. {{ $booking['player3_name'] }}/{{$booking['player3_member_id']}}
                                                    @else
                                                    3. Player 3
                                                    @endif
                                                </h4>
                                                <h4>
                                                    @if($booking['player4_name'])
                                                    4. {{ $booking['player4_name'] }}/{{$booking['player4_member_id']}}
                                                    @else
                                                    4.Player 4
                                                    @endif
                                                </h4>
                                                <!-- <p>Cart, No Caddy, No rental</p> -->
                                            </div>
                                        </li>
                                    </ul>
                                </div>

                                
                                <div class="confirm-lower-right">
                                    <ul class="clr-list">
                                        @if(App\Models\TeeBookingDetails::date_check($booking['booking_date']))
                                        @if($booking['is_cancelled']==0)
                                        @if($booking['created_by']==auth()->user()->id)
                                        <li class="clr-item">
                                            <a href="javascript:void();" class="cll-link book-now-btn"
                                                data-member-status="{{ Auth::user()->Status}}"
                                                data-booking-date="{{ $booking->teeBooking->booking_date }}"
                                                data-tee-time="{{ \Carbon\Carbon::parse($booking->tee_time)->format('g:iA') }}"
                                                data-session-name="" data-tee-off-hole=""
                                                data-tee-booking-id="{{ $booking->tee_booking_id }}"
                                                data-tee-sheet-id="{{ $booking->tee_sheet_id }}"
                                                data-player1-member-id="{{ $booking->player1_id }}"
                                                data-player2-member-id="{{ $booking->player2_id }}"
                                                data-player3-member-id="{{ $booking->player3_id }}"
                                                data-player4-member-id="{{ $booking->player4_id }}"
                                                data-player1-name="{{ $booking->player1_name }}/{{$booking->player1_member_id}}"
                                                data-player2-name="{{ $booking->player2_name }}/{{$booking->player2_member_id}}"
                                                data-player3-name="{{ $booking->player3_name }}/{{$booking->player3_member_id}}"
                                                data-player4-name="{{ $booking->player4_name }}/{{$booking->player4_member_id}}"
                                                data-tee-booking-detail-id="{{ $booking->tee_booking_detail_id }}"
                                               >
                                                <i class="fa-solid fa-arrow-right-from-bracket"
                                                    style="color: #0d0d0d;"></i> Edit booking
                                            </a>
                                        </li>


                                        <li class="clr-item">
                                            <a href="{{route('cancel-booking',[$booking->tee_booking_detail_id])}}"
                                                class="cll-link cancel-booking"
                                                onclick="return confirm('Are you sure you want to cancel this booking?');"
                                                >
                                                <i class="fa-solid fa-xmark" style="color: #000000;"> </i> Cancel
                                                booking
                                            </a>
                                        </li>
                                        @endif

                                        @else
                                        <li class="clr-item">
                                           Canceled
                                        </li>
                                        @endif
                                        @endif


                                        <!-- <li class="clr-item">
                                            <a href="#" class="cll-link">
                                                <i class="fa-solid fa-print" style="color: #000000;"> </i> Print
                                                confirmation
                                            </a>
                                        </li> -->
                                    </ul>
                                </div>
                            </div>
                            <hr>
                            @endforeach



                            <div class="confirm-lower d-none">
                                <div class="confirm-lower-left">
                                    <ul class="cll-list">
                                        <li class="cll-item">
                                            <div class="cll-heading"> The Royal ibrox Golf Course </div>
                                            <h3> Mon Jul 30, 2018 </h3>
                                            <p> 7:00 PM </p>

                                        </li>
                                        <li class="cll-item">
                                            <div class="cll-heading"> Confirmation </div>
                                            <h3> RI00007885 </h3>
                                        </li>
                                        <li class="cll-item">
                                            <div class="cll-heading"> Players </div>
                                            <div class="cllh-data">
                                                <h4> Mr.Roger Smith </h4>
                                                <p> Cart,No Caddy, No rental</p>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                                <div class="confirm-lower-right">
                                    <ul class="clr-list">
                                        <li class="clr-item">



                                        <li class="clr-item"> <a href="#" class="cll-link"> <i class="fa-solid fa-xmark"
                                                    style="color: #000000;"> </i> cancel
                                                booking </a> </li>
                                        <li class="clr-item"> <a href="#" class="cll-link"><i class="fa-solid fa-print"
                                                    style="color: #000000;"> </i> print
                                                confirmation </a> </li>
                                    </ul>
                                </div>
                            </div>




                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-tab-4" aria-labelledby="nav-tab-4-tab">
                        <div class="product-content-box">
                            <!-- !!- ===================================== Content Start ======================== -!! -->
                            @include('website.my_buddy')

                            <!-- !!- ===================================== Content End ======================== -!! -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>


<!-- The modal structure -->
<div class="modal loading-btn-form" id="bookNowModal" tabindex="-1" role="dialog" aria-labelledby="bookNowModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookNowModalLabel">Book Now</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <div class="modal-body">
                <p class="text-danger" id="countdown"></p>
                <!-- Populate this section with the pre-populated details and form for player details -->
                <div class="row booking-details mb-3 small">
                    <div class="col-6">
                        <p class="mb-1"><b>Booking Date:</b> <span id="bookingDate"></span></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><b>Tee Time:</b> <span id="teeTime"></span></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><b>Session:</b> <span id="sessionName"></span></p>
                    </div>
                    <div class="col-6">
                        <p class="mb-1"><b>Tee Off Hole:</b> <span id="teeOffHole"></span></p>
                    </div>
                </div>

                <!-- Form for player details -->
                <form id="playerDetailsForm" method="post" action="{{route('store_tee_booking')}}">
                    @csrf
                    <div class="row">
                        <input type="hidden" id="teeBookingId" name="tee_booking_id">
                        <input type="hidden" id="teeSheetId" name="tee_sheet_id">
                        <input type="hidden" id="teeBookingDetailId" name="tee_booking_detail_id">
                        <div class="col-md-6 mb-3">
                            <label for="player1Name">Player 1 Name/Member ID</label>

                            <input type="hidden" id="player1NameSelected" value="" name="player1_id">
                            <input type="text" id="player1Name" value="" class="form-control player-list"
                                list="datalistOptions1" readonly>
                            <datalist id="datalistOptions1">

                            </datalist>
                        </div>
                        <!--<div class="col-md-6 mb-3">-->
                        <!--    <label for="player1ID">Player 1 ID</label>-->
                        <!--    <input type="text" class="form-control" id="player1ID" placeholder="Player 1 ID">-->
                        <!--</div>-->
                        <div class="col-md-6 mb-3">
                            <label for="player2Name">Player 2 Name/Member ID</label>
                            <input type="hidden" id="player2NameSelected" name="player2_id">
                            <input type="text" id="player2Name" class="form-control player-list"
                                list="datalistOptions2" autocomplete="off">
                            <datalist id="datalistOptions2">

                            </datalist>
                        </div>
                        <!--<div class="col-md-6 mb-3">-->
                        <!--    <label for="player2ID">Player 2 ID</label>-->
                        <!--    <input type="text" class="form-control" id="player2ID" placeholder="Player 2 ID">-->
                        <!--</div>-->
                        <div class="col-md-6 mb-3">
                            <label for="player3Name">Player 3 Name/Member ID</label>
                            <input type="hidden" id="player3NameSelected" name="player3_id">
                            <input type="text" id="player3Name" class="form-control player-list"
                                list="datalistOptions3" autocomplete="off">
                            <datalist id="datalistOptions3">

                            </datalist>
                        </div>
                        <!--<div class="col-md-6 mb-3">-->
                        <!--    <label for="player3ID">Player 3 ID</label>-->
                        <!--    <input type="text" class="form-control" id="player3ID" placeholder="Player 3 ID">-->
                        <!--</div>-->
                        <div class="col-md-6 mb-3">
                            <label for="player4Name">Player 4 Name/Member ID</label>
                            <input type="hidden" id="player4NameSelected" name="player4_id">
                            <input type="text" id="player4Name" class="form-control player-list"
                                list="datalistOptions4" autocomplete="off">
                            <datalist id="datalistOptions4">

                            </datalist>
                        </div>
                        <!--<div class="form-group col-md-6 mb-3">-->
                        <!--    <label for="player4ID">Player 4 ID</label>-->
                        <!--    <input type="text" class="form-control" id="player4ID" placeholder="Player 4 ID">-->
                        <!--</div>-->
                    </div>
                    <!-- Repeat the above form rows for Player 2, Player 3, and Player 4 -->

                    <button type="submit" id="submitButton" class="btn btn-primary">Submit</button>
                </form>
            </div>
            <!-- <div class="modal-footer"> -->
            <!-- <button type="button" class="btn btn-secondary cancelButton" data-dismiss="modal">Close</button> -->
            <!-- </div> -->
        </div>
    </div>
</div>

</div>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')
<script>
// Set the countdown duration to 5 minutes

let countdown;
$('#bookNowModal').on('shown.bs.modal', function() {

    var teeSheetId = $("#teeSheetId").val();
    var tee_booking_id = $("#teeBookingId").val();
    var tee_booking_detail_id = $("#teeBookingDetailId").val();
    //alert(tee_booking_detail_id);
    
    //Set countdown to 1 min
    const countdownDuration = (60 * 1000) + 1000;
    const countdownDate = new Date().getTime() + countdownDuration;
    $.ajax({
             url: "{{route('lock-tee-booking')}}",
             method: "POST",
             data: {
                 _token:$('meta[name="csrf-token"]').attr('content'),
                 tee_sheet_id:teeSheetId,
                 tee_booking_id:tee_booking_id
             },
             success: function(response) {
                // Update the countdown every second
                if(tee_booking_detail_id==""){
                countdown = setInterval(function() {
                    const now = new Date().getTime();
                    const distance = countdownDate - now;

                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    $('#countdown').html(`${minutes}m ${seconds}s`);




                    if (distance < 0) {
                        clearInterval(countdown);
                        $('#countdown').html('Countdown expired');
                        window.location.reload();
                        $('#bookNowModal').modal('hide');
                        }
                    });
                }
                 
                //  toastr.success(response
                //      .message);
                // window.location.reload();
                // $('#bookNowModal').modal('hide');
             },
             error: function(xhr) {
                $('#bookNowModal').modal('hide');
                
                 if (xhr.status === 422) {
                     var errors = xhr.responseJSON.errors;
                    
                     $.each(errors, function(key, value) {
                         toastr.error(value);
                     });
                 } else if (xhr.status === 400) {

                     toastr.error(xhr.responseJSON.error);

                 } else {
                     toastr.error('An error occurred. Please try again.');
                 }
                 // Reload the page after 1 second
                setTimeout(function() {
                    window.location.reload();
                }, 1000); // 1000 milliseconds = 1 second
             }
         });

    
});

$('#bookNowModal').on('hidden.bs.modal', function() {
    clearInterval(countdown);
    $('#countdown').html('');
});

$(document).ready(function() {

    $('.book-now-btn').click(function() {
        const status = $(this).data('member-status');
     
        if(status =="Direct Out Station" || status =="In Station"){
        const bookingDate = $(this).data('booking-date');
     
        const teeTime = $(this).data('tee-time');
        const sessionName = $(this).data('session-name');
        const teeOffHole = $(this).data('tee-off-hole');
        const teeSheetId = $(this).data('tee-sheet-id');
        const teeBookingId = $(this).data('tee-booking-id');
        var player1MemberId = $(this).data('player1-member-id');
        const player2MemberId = $(this).data('player2-member-id');
        const player3MemberId = $(this).data('player3-member-id');
        const player4MemberId = $(this).data('player4-member-id');
        var player1Name = $(this).data('player1-name');
        const player2Name = $(this).data('player2-name');
        const player3Name = $(this).data('player3-name');
        const player4Name = $(this).data('player4-name');
        const teeBookingDetailId = $(this).data('tee-booking-detail-id');

        // Populate the modal with dynamic data
        $('#bookingDate').text(bookingDate);
        $('#teeTime').text(teeTime);
        $('#sessionName').text(sessionName);
        $('#teeOffHole').text(teeOffHole);
        $('#teeSheetId').val(teeSheetId);
        $('#teeBookingId').val(teeBookingId);
        if (player1Name == "" || player1Name == "/") {
            player1Name = "{{auth()->user()->DisplayName}}/{{auth()->user()->MemberID}}";
        }
        if (player1MemberId == "" ) {
            player1MemberId = "{{auth()->user()->id}}";
        }
        $('#player1NameSelected').val(player1MemberId);
        $('#player2NameSelected').val(player2MemberId);
        $('#player3NameSelected').val(player3MemberId);
        $('#player4NameSelected').val(player4MemberId);
        
        if(player1MemberId!="" || player1MemberId=="/")
        $('#player1Name').val(player1Name);
        if(player2MemberId!="" || player1MemberId=="/")
        $('#player2Name').val(player2Name);
        if(player3MemberId!="" || player1MemberId=="/")
        $('#player3Name').val(player3Name);
        if(player4MemberId!="" || player1MemberId=="/")
        $('#player4Name').val(player4Name);
        
        $('#teeBookingDetailId').val(teeBookingDetailId);
        // Show the modal
        $('#bookNowModal').modal('show');
    } else{
        toastr.error("Only active member can book the slot.");
    }
    });




    // Handle form submission
    $('#playerDetailsForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        // Submit form via AJAX
        $.ajax({
            url: $(this).attr('action'),
            method: $(this).attr('method'),
            data: formData,
            success: function(response) {

                toastr.success(response
                    .message);
                window.location.reload();
                $('#bookNowModal').modal('hide');
            },
            error: function(xhr) {

               $html="Submit";
        var $submitBtn = $('#submitButton');
        $submitBtn.prop('disabled', false);
        $submitBtn.html('');
        $submitBtn.html($html);

                if (xhr.status === 422) {
                    var errors = xhr.responseJSON.errors;

                    $.each(errors, function(key, value) {
                        toastr.error(value);
                    });
                } else if (xhr.status === 400) {

                    toastr.error(xhr.responseJSON.error);

                } else {
                    toastr.error('An error occurred. Please try again.');
                }
            }
        });

    });

});


$(document).ready(function() {
    // $('#myModal').modal('show');
    $('select').change(function() {
        $('#searchForm').submit();
    });
});

function submitForm($date) {
    $("#searchDate").val($date);
    $('#searchForm').submit();
}


$(document).ready(function() {
    $html = 'Processing...';
    $('.loading-btn-form').submit(function (e) {
        var $form = $(this);
        var $submitBtn = $form.find('button[type="submit"]');
        $submitBtn.prop('disabled', true);
        $submitBtn.html('');
        $submitBtn.html($html);
    });
    
    $('.player-list').on('input', function() {

        var teeSheetId = $("#teeSheetId").val();
        // alert(teeSheetId);

        var currentPlayerInput = $(this);
        var userInput = currentPlayerInput.val();

        if (userInput.length >= 1) {
            $.ajax({
                url: "{{ route('autocomplete-members') }}",
                method: 'GET',
                data: {
                    userInput: userInput,
                    teeSheetId: teeSheetId
                },
                success: function(response) {
                    var formattedOptions = formatResponse(response);
                    populateOptions(currentPlayerInput, formattedOptions);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            clearOptions(currentPlayerInput);
        }
    });

    $('.buddy-list').on('input', function() {

        //var teeSheetId = $("#teeSheetId").val();
        // alert(teeSheetId);

        var currentPlayerInput = $(this);
        var userInput = currentPlayerInput.val();

        if (userInput.length >= 1) {
            $.ajax({
                url: "{{ route('autocomplete-buddy') }}",
                method: 'GET',
                data: {
                    userInput: userInput,
                   
                },
                success: function(response) {
                    var formattedOptions = formatResponse(response);
                    populateOptions(currentPlayerInput, formattedOptions);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            clearOptions(currentPlayerInput);
        }
    });

    function formatResponse(response) {
        var formattedOptions = [];
        response.forEach(function(obj) {
            formattedOptions.push({
                label: obj.label,
                value: obj.value
            });
        });
        return formattedOptions;
    }

    $('.player-list').change(function() {
        var selectedLabel = $(this).val();
        var selectedId = $(this).siblings('datalist').find('option[value="' + selectedLabel + '"]')
            .data('value');
        var playerId = $(this).attr('id');
        // alert(playerId);
        $('#selectedLabel').text(selectedLabel);
        $('#' + playerId + 'Selected').val(selectedId);
    });

    $('.buddy-list').change(function() {
        var selectedLabel = $(this).val();
        var selectedId = $(this).siblings('datalist').find('option[value="' + selectedLabel + '"]')
            .data('value');
        var playerId = $(this).attr('id');
        // alert(playerId);
        $('#selectedLabel').text(selectedLabel);
        $('#' + playerId + 'Selected').val(selectedId);
    });

    function populateOptions(currentPlayerInput, options) {
        var datalist = currentPlayerInput.siblings('datalist');
        datalist.empty();

        options.forEach(function(option) {
            datalist.append(`<option value="${option.label}" data-value="${option.value}">`);
        });
    }

    function clearOptions(currentPlayerInput) {
        var datalist = currentPlayerInput.siblings('datalist');
        datalist.empty();
    }
});
$(document).ready(function() {
  
  $('.nav-link').click(function() {
    localStorage.setItem('activeTab',$(this).attr('id'));
  });

 
  var activeTab = localStorage.getItem('activeTab');
  if (activeTab) {
    $('#'+activeTab).trigger('click');
  }
});
</script>
@endpush()
@endsection