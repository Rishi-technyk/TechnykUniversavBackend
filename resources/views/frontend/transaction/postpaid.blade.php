@extends('frontend.layouts.app')

@section('title', ' Postpaid Transactions')

@section('content')

<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Postpaid Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        <div class="card-section-title-box">
            <span class="card-section-bar"></span>  
            <div class="row">
                <div class="col-lg-12">
                    <div class="card mb-1 h-100">
                        <div class="card-body ">
                            
                            <div class="row">
                                <div class="col-lg-2">
                                    Opening Balance
                                </div>
                                <div class="col-lg-2">
                                    {{$opening_balance}}
                                </div>
                                <div class="col-lg-2">
                                    Total Credit
                                </div>
                                <div class="col-lg-2">
                                    {{$total_credit}} Cr
                                </div>
                            </div>
                    
                            <div class="row">
                                <div class="col-lg-2">
                                Closing Balance
                                </div>
                                <div class="col-lg-2">
                                    {{$closing_balance}}
                                </div>
                                <div class="col-lg-2">
                                    Total Debit
                                </div>
                                <div class="col-lg-2">
                                    {{$total_debit}} Dr
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>  
            
            <div class="mb-3 mt-3">

                <form method="GET" action="">
                    <div class="row">
                        <!-- Date Range Filter (Start Date) -->
                        <div class="col-md-3 mb-3">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>

                        <!-- Date Range Filter (End Date) -->
                        <div class="col-md-3 mb-3">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-6 btn-wrapper mt-3">
                            <button type="submit" class="btn-sm mt-4">Filter</button>
                        </div>
                    </div>
                </form>
                
            </div>

            <table class="table table-border">

                <thead>

                    <tr>

                        <th scope="col">#</th>

                        <th scope="col">Vch. No</th>

                        <th scope="col">Vch Date</th>

                        <th scope="col">Particulars</th>

                        <th scope="col">Cr. Amt.</th>

                        <th scope="col">Dr. Amt</th>

                        <th scope="col" style="width: 200px;">Narration</th>

                    </tr>

                </thead>

                <tbody>

                    @if(count($history))

                        @foreach($history as $key => $val)

                            <tr>

                                <th scope="row">{{ ++$key }}</th>

                                <td>{{ $val->voucher_no }}</td>

                                <td>{{ date("d-m-Y", strtotime($val->voucher_date)) }}</td>

                                <td>{{ $val->particulars }}</td>

                                <td>{{ number_format($val->credit_amt,2) }}</td>

                                <td>{{ number_format($val->debit_amt,2) }}</td>

                                <td>{{ $val->narrations }}</td>

                            </tr>

                        @endforeach

                    @else

                        <tr>

                            <td colspan="6" class="text-center">No Data Available!</td>

                        </tr>

                    @endif

                </tbody>

            </table>

            <div class="pagination-wrapper">
                {{ $history->links('pagination::bootstrap-5') }}
            </div>

        </div>
        

    </div>

</section>

@endsection

@section('script')

@endsection