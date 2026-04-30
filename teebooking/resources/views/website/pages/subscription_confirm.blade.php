@extends('layouts.web')
@section('content')

<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Confirm subscription </h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/avatar.png') }}" alt="avatar"
                            class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{$member->DisplayName}}</h5>
                        <p class="text-muted mb-2"> {{$member->Email}}</p>


                        <div class="btn-wrapper mt-1">
                            <a class="cmn-btn btn-bg-1" href="{{route('member_edit')}}"> Edit </a>
                        </div>

                    </div>
                </div>

            </div>
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Member ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->MemberID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">C_ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->SC_ID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Category</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0"> {{$member->CategoryType}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Mobile</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0"> {{$member->Mobile}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Status</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->Status}}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-4 ">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center pt-1">
                        <div class="custom--form dashboard-form mt-5">
                            <form method="post"  name="redirect" action="{{route('subscription-handle')}}">
                                <input name="tid" type="hidden" value="<?php echo $txnid; ?>" />
                                <input name="merchant_id" type="hidden" value="3073874" />
                                <input name="account_id" type="hidden" value="24981" /> 
                                <input name="order_id" type="hidden" value="<?php echo $txnid; ?>" />
                                <input name="amount" type="hidden" value="<?php echo $RechargeAmount; ?>" />
                                <input name="currency" type="hidden" value="INR" />
                                <input name="redirect_url" type="hidden" value="{{route('subscription-response')}}" />
                                <input name="cancel_url" type="hidden" value="{{route('subscription-response')}}" />
                                <input name="language" type="hidden" value="EN" />
                                <input name="billing_name" type="hidden" value="<?php echo $member->DisplayName; ?>" />
                                <input name="billing_address" type="hidden" value="AEPTA" />
                                <input name="billing_city" type="hidden" value="Delhi Cantonment" />
                                <input name="billing_state" type="hidden" value="New Delhi" />
                                <input name="billing_zip" type="hidden" value="11010" />
                                <input name="billing_country" type="hidden" value="India" />
                                <input name="billing_tel" type="hidden" value="<?php echo $member->Mobile; ?>" />
                                <input name="billing_email" type="hidden" value="<?php echo $member->Email; ?>" />
                                <input name="delivery_name" type="hidden" value="<?php echo $member->DisplayName; ?>" />
                                <input name="delivery_address" type="hidden" value="Club26" />
                                <input name="delivery_city" type="hidden" value="Sector-26, Noida" />
                                <input name="delivery_state" type="hidden" value="UP" />
                                <input name="delivery_zip" type="hidden" value="201301" />
                                <input name="delivery_country" type="hidden" value="India" />
                                <input name="delivery_tel" type="hidden" value="<?php echo $member->Mobile; ?>" />
                                <input name="merchant_param1" type="hidden" value="<?php echo $member->SC_ID ?>" />
                                <input name="merchant_param2" type="hidden" value="<?php echo $member->DisplayName; ?>" />
                                <input name="merchant_param3" type="hidden" value="<?php echo $member->MemberID; ?>" />
                                <input name="merchant_param4" type="hidden" value="" />
                                <input name="merchant_param5" type="hidden" value="" />
                                <input name="promo_code" type="hidden" value="" />
                                <input name="customer_identifier" type="hidden" value="" />
                                {{ csrf_field() }}
                                <p class="text-muted mb-0">Transaction Id:<br><?php echo $txnid; ?></p>
                              
                                <br> <br>   
                                <div class="dashboard-input">
                                    <input type="text" name="txtIdd" class="form--control" value="<?php echo $RechargeAmount; ?>"
                                        placeholder="Enter amount" disabled />

                                </div>
                                <br>
                                <div class="btn-wrapper mt-1">
                                    <a href="{{route('member_card_recharge')}}" class="cmn-btn btn-bg-1">
                                        Cancel
                                    </a>
                                    <button type="submit" class="cmn-btn btn-bg-1">
                                        Proceed
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection