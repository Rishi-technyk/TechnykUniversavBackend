@extends('layouts.member')
@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-promo">
        <div class="hotel-details-widget hotel-details-widget-padding widget bg-white radius-10">
            <div class="details-sidebar">
                <div class="details-sidebar-dropdown custom-form">
                    <form method="get" action="{{ route('rooms') }}" class="row g-3">
                        <div class="col-sm-5">
                            <div class="single-input">
                                <input name="checkin" class="form--control checkin" type="text"
                                    value="{{ date('d-m-Y', strtotime(session('checkin'))) }}" placeholder="Check in"
                                    required />
                            </div>
                        </div>

                        <div class="col-sm-5">
                            <div class="single-input">
                                <input name="checkout" class="form--control checkout" type="text"
                                    placeholder="Check out" required
                                    value="{{ date('d-m-Y', strtotime(session('checkout'))) }}" />
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="btn-wrapper">
                                <button type="submit" class="cmn-btn btn-bg-1" style="padding: 7px 26px">
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
    <div id="tab-list" class="tab-content-item active mt-4">
        <div class="row gy-4">
            @forelse($rooms as $room)
            <div class="col-12 mb-4">
                <div class="hotel-view bg-white radius-20">
                    <div class="hotel-view-flex">
                        <a href="hotel_details.html" class="hotel-view-thumb hotel-view-list-thumb bg-image"
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
                                    <div class="btn-wrapper">
                                        <a href="{{ url('/room-details') }}/{{encrypt($room->id)}}"
                                            class="cmn-btn btn-bg-1 btn-small">
                                            Reserve Now
                                        </a>
                                    </div>
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
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <span> <i class="las la-parking"></i> </span>
                                        <p class="hotel-view-contents-icon-title flex-fill">
                                            Parking
                                        </p>
                                    </div>
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <span> <i class="las la-wifi"></i> </span>
                                        <p class="hotel-view-contents-icon-title flex-fill">
                                            Wifi
                                        </p>
                                    </div>
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <span> <i class="las la-coffee"></i> </span>
                                        <p class="hotel-view-contents-icon-title flex-fill">
                                            Breakfast
                                        </p>
                                    </div>
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <span>
                                            <i class="las la-swimming-pool"></i>
                                        </span>
                                        <p class="hotel-view-contents-icon-title flex-fill">
                                            Pool
                                        </p>
                                    </div>
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <span> <i class="las la-dumbbell"></i> </span>
                                        <p class="hotel-view-contents-icon-title flex-fill">
                                            Gym
                                        </p>
                                    </div>
                                    <div class="hotel-view-contents-icon d-flex gap-1">
                                        <a class="hotel-view-contents-icon-more" href="javascript:void(0)">
                                            +8 More
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="hotel-view-contents-bottom">
                                <div class="hotel-view-contents-bottom-flex">
                                    <div class="hotel-view-contents-bottom-contents d-flex flex-wrap gap-4 gap-sm-1">

                                        @foreach($room->roomPrices as $price)
                                        <h4 class="hotel-view-contents-bottom-title">
                                            <i class="las la-rupee-sign"></i>{{$price->price}}
                                            <sub>/{{$price->occupants->name}}</sub>
                                        </h4>
                                        @endforeach

                                        {{-- <p class="hotel-view-contents-bottom-para">
                                            (4 Nights, 2 Rooms, 4 Persons)
                                        </p> --}}
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
        </div>
    </div>
</div>
@endsection