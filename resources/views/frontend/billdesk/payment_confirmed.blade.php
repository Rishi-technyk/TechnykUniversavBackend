@extends('frontend.layouts.app')

@section('title', 'Payment Details')

@section('content')

<div class="container">

    <div class="card-section-title-box">

        <span class="card-section-bar"></span>

        <h4 class="card-section-title">Payment Details</h4>

    </div>

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        
        <div class="row mb-2">

            <div class="col-lg-4"></div>

            <div class="col-lg-5">

                

                    <div class="">

                        @if($transaction->payment_status == 'Paid')

                            <h4 class="text-success text-center mb-4 mt-4">Payment Successful</h4>

                        @else

                            <h4 class="text-danger text-center mb-4 mt-4">Payment Failed</h4>     

                        @endif

                        <div class="row">

                            <div class="col-lg-6">

                                <p><b>Member ID :</b></p>

                            </div>

                            <div class="col-lg-6">

                                <p>{{ $transaction->member_id }}</p>

                            </div>

                            <div class="col-lg-6">

                                <p><b>Order ID :</b></p>

                            </div>

                            <div class="col-lg-6">

                                <p>{{ $transaction->order_id }}</p>

                            </div>



                            <div class="col-lg-6">

                                <p><b>Transaction ID :</b></p>

                            </div>

                            <div class="col-lg-6">

                                <p>{{ $transaction->bank_refrance_no ?? "--" }}</p>

                            </div>

                            

                            <div class="col-lg-6">

                                <p><b>Payment Date :</b></p>

                            </div>

                            <div class="col-lg-6">

                                <p>{{ date("d-m-Y H:i:s", strtotime($transaction->created_at)) }}</p>       

                            </div>



                            <div class="col-lg-6">

                                <p><b>Amount Paid :</b></p>        

                            </div>

                            <div class="col-lg-6">

                                <p>Rs. {{ number_format($transaction->amount, 2) }}</p>

                            </div>



                            <div class="col-lg-6">

                                <p><b>Status :</b></p>        

                            </div>

                            <div class="col-lg-6">

                                <p>{{ $transaction->payment_status }}</p>

                            </div>



                            <div class="col-lg-6">

                                <p><b>Type :</b></p>        

                            </div>

                            <div class="col-lg-6">

                                <p>{{ $transaction->type }}</p>

                            </div>



                        </div>

                    </div>

                </div>

            </div>

        </div>

</div>



@endsection

@section('script')

@endsection