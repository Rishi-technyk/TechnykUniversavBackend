@extends('frontend.layouts.app')

@section('title', 'Card Recharge')

@section('content')

<div class="container">
    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Card Recharge</h4>
    </div>
    <!-- Breadcrumb Section End -->

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <div class="row">
            <div class="col-lg-4"></div>
            <div class="col-lg-4">
                <form action="{{ route('pay.card.recharge') }}" method="post" class="contact-form">
                    @csrf
                    <p><b>Card Balance</b> : @if($recharges) Rs.{{ $recharges?$recharges->CardBalance:'' }} as on {{ $recharges?date("d-m-Y", strtotime($recharges->ClosingDate)):'' }} @endif</p>
                    <p><b>Amount to be Paid :</b></p>
                    <input type="number" name="amount" class="form-control" placeholder="Enter Pay Amount" required>
                    <input type="hidden" name="type" value="Card Recharge">
                    <button type="submit" class="mt-2">Pay Now</button>
                </form>
            </div>
        </div>
    </div>
    
</div>

@endsection

@section('script')

@endsection