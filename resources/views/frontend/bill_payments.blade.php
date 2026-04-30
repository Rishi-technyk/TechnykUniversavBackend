@extends('frontend.layouts.app')



@section('title', 'Bill Payments')



@section('content')



<div class="container">



    <!-- Breadcrumb Section Begin -->

    <div class="card-section-title-box">

        <span class="card-section-bar"></span>

        <h4 class="card-section-title">Bill Payments</h4>

    </div>

    <!-- Breadcrumb Section End -->



    <div class="card-section-title-box">

        <span class="card-section-bar"></span>

        <div class="row">



            <div class="col-lg-4"></div>

            <div class="col-lg-4">

                <!-- @if($receipts && $receipts->PayStatus === 'success')

                <div class="text-center text-success mb-3">

                    <b>Bill already paid. For extra payment, please recharge your Smart Card account.</b>

                </div>

                @endif -->

                <form action="{{ route('pay.bill.payment') }}" method="post" class="contact-form">

                    @csrf

                    <p><b>Bill Month : </b> {{ $receipts->BillMonthYear ?? '' }}</p>

                    <p><b>Amount to be Paid :</b></p>

                    <input type="number" class="form-control" placeholder="Enter Pay Amount" value="{{ $AmountPayable }}" required readonly>

                    

                    <p><b>Additional Amount :</b></p>

                    <input type="number" class="form-control" name="additional_amount" placeholder="Enter Pay Amount" value="">

                    

                    <input type="hidden" name="amount" class="form-control" value="{{ $AmountPayable }}" required>

                    <input type="hidden" name="type" value="Bill Payment">

                    <button type="submit" class="mt-2">Pay Now</button>

                    @if ($receipts && file_exists( public_path() . '/Bills/' . $receipts->Mem_Id .'-'. $receipts->BillMonthYear . '.pdf'))

                        <a href="{{ asset('Bills/' . $receipts->Mem_Id .'-'. $receipts->BillMonthYear . '.pdf') }}" target="_blank"><button type="button" class="mt-3">View Bill</button></a>

                    @endif

                </form>

            </div>

            

        </div>



    </div>

</div>







@endsection



@section('script')



@endsection