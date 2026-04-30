@extends('layouts.member')
@section('content')
<section class="hotel-details-area section-bg-2">
    <div class="container">
        <div class="row g-4">
            <div class="col-xl-12 col-lg-12">
                <div class="details-left-wrapper">
                    <div class="hotel-view bg-white radius-10">
                        <div class="hotel-view-flex">
                            <a href="hotel_details.html" class="hotel-view-thumb hotel-view-list-thumb bg-image" style="
                                          background-image: url(member/assets/img/single-page/hotel-list1.jpg);
                                        ">
                            </a>
                            <div class="hotel-view-contents">
                                <div class="hotel-view-contents-header">
                                    <div
                                        class="hotel-view-contents-header-flex d-flex flex-wrap gap-3 align-items-center justify-content-between">
                                        <span class="hotel-view-contents-review">
                                            <i class="las la-star"></i> 4.5
                                            <span class="hotel-view-contents-review-count">
                                                (380)
                                            </span>
                                        </span>
                                    </div>
                                    <h3 class="hotel-view-contents-title">
                                        <a href="hotel_details.html"> King Suite Room </a>
                                    </h3>
                                    <div class="hotel-view-contents-location mt-2">
                                        <span class="hotel-view-contents-location-icon">
                                            <i class="las la-map-marker-alt"></i>
                                        </span>
                                        <span class="hotel-view-contents-location-para">
                                            4140 Parker Rd. Allentown, New Mexico 31134
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
                                            <span> <i class="las la-quidditch"></i> </span>
                                            <p class="hotel-view-contents-icon-title flex-fill">
                                                Room Service
                                            </p>
                                        </div>
                                        <div class="hotel-view-contents-icon d-flex gap-1">
                                            <span> <i class="las la-swimming-pool"></i> </span>
                                            <p class="hotel-view-contents-icon-title flex-fill">
                                                Pool
                                            </p>
                                        </div>
                                        <div class="hotel-view-contents-icon d-flex gap-1">
                                            <span> <i class="las la-receipt"></i> </span>
                                            <p class="hotel-view-contents-icon-title flex-fill">
                                                Reception
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
                                        <div class="hotel-view-contents-bottom-contents d-flex flex-wrap gap-4">
                                            <h4 class="hotel-view-contents-bottom-title">
                                                $230 <sub>/Night</sub>
                                            </h4>
                                            <p class="hotel-view-contents-bottom-para">
                                                (4 Nights, 2 Rooms, 4 Persons)
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-12 col-lg-12">
                <div class="row">
                    <div class="col-md-8">
                        <div class="hotel-details-widget hotel-details-widget-padding widget bg-white radius-10">
                            <div class="dashboard-reservation">
                                <div class="single-reservation bg-white base-padding">
                                    <div class="single-reservation-details pb-4">
                                        <div class="single-reservation-details-item">
                                            <span class="single-reservation-details-subtitle"> Check in </span>
                                            <h5 class="single-reservation-details-title"> 10:30 AM, 23 Jun 22 </h5>
                                        </div>
                                        <div class="single-reservation-details-item">
                                            <span class="single-reservation-details-subtitle"> Check Out </span>
                                            <h5 class="single-reservation-details-title"> 10:30 AM, 28 Jun 22 </h5>
                                        </div>
                                    </div>
                                    <hr />
                                    <div class="custom--form dashboard-form">
                                        <form action="#">
                                            <div class="dashboard-flex-input">
                                                <div class="dashboard-input mt-4">
                                                    <label class="popup-contents-select-label"> Rooms to be booked for
                                                    </label>
                                                    <div class="js-select">
                                                        <select>
                                                            <option value="">Select One</option>
                                                            <option value="1">Member</option>
                                                            <option value="2">Guest Civil</option>
                                                            <option value="3">Guest Defence</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="dashboard-input mt-4">
                                                    <label class="popup-contents-select-label"> How many rooms </label>
                                                    <div class="details-sidebar-quantity-field">
                                                        <span class="minus"><i class="las la-minus"></i></span>
                                                        <input class="quantity-input" type="number" value="1" /><span
                                                            class="plus"><i class="las la-plus"></i></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="dashboard-flex-input">
                                                <div class="dashboard-input mt-4">
                                                    <label class="popup-contents-select-label"> To be booked for
                                                    </label>
                                                    <div class="js-select">
                                                        <select>
                                                            <option value="">Select One</option>
                                                            <option value="1">Member</option>
                                                            <option value="2">Dependent</option>
                                                            <option value="3">Guest Defence</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="dashboard-input mt-4">
                                                    <label class="label-title"> Occupant Name </label>
                                                    <input type="text" class="form--control" placeholder="Last Name">
                                                </div>
                                            </div>
                                            <div class="dashboard-flex-input">
                                                <div class="dashboard-input mt-4">
                                                    <label class="popup-contents-select-label"> To be booked for
                                                    </label>
                                                    <div class="js-select">
                                                        <select>
                                                            <option value="">Select One</option>
                                                            <option value="1">Member</option>
                                                            <option value="2">Dependent</option>
                                                            <option value="3">Guest Defence</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="dashboard-input mt-4">
                                                    <label class="label-title"> Occupant Name </label>
                                                    <input type="text" class="form--control" placeholder="Last Name">
                                                </div>
                                            </div>
                                            <div class="btn-wrapper mt-4">
                                                <a href="javscript:void(0)" class="cmn-btn btn-bg-1"> Go to Checkout
                                                </a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        Price
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection