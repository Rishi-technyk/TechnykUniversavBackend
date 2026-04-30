@extends('frontend.layouts.app')

@section('title', ' Prepaid Transactions')
<style>
    .contact-select {
        height: 39px !important;
    }
</style>
@section('content')

<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Prepaid Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        
        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>

            <div class="mb-3 mt-3">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <label>Location</label>
                                <select class="contact-select" id="location" name="location">
                                    <option value="">Select Location</option>
                                    @foreach($member['location'] as $loc)
                                        <option value="{{ $loc }}" {{ request('location') == $loc ? 'selected' : '' }}>{{ $loc }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="paymode">Pay Mode</label>
                            <select class="contact-select" id="paymode" name="paymode">
                                <option value="">Select Pay Mode</option>
                                @foreach($member['paymode'] as $mode)
                                    <option value="{{ $mode }}" {{ request('paymode') == $mode ? 'selected' : '' }}>{{ $mode }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range Filter (Start Date) -->
                        <div class="col-md-3">
                            <label for="start_date">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ request('start_date') }}">
                        </div>

                        <!-- Date Range Filter (End Date) -->
                        <div class="col-md-3">
                            <label for="end_date">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ request('end_date') }}">
                        </div>

                        <!-- Submit Button -->
                        <div class="col-md-12 text-right">
                            <button type="submit" class="btn-sm">Filter</button>
                        </div>
                    </div>
                </form>
            </div>

            <table class="table table-border">

                <thead>

                    <tr>

                        <th scope="col">#</th>

                        <th scope="col">Bill No</th>

                        <th scope="col">Bill Date</th>

                        <th scope="col">Location Name</th>

                        <th scope="col">Pay Mode</th>

                        <th scope="col">Amount</th>

                        <th scope="col">Balance</th>

                    </tr>

                </thead>

                <tbody>

                    @if(count($history))

                        @foreach($history as $key => $val)

                            <tr>

                                <th scope="row">{{ ++$key }}</th>

                                <td>{{ $val->BillNo }} </td>

                                <td>{{ date("d-m-Y", strtotime($val->BillDate)) }}</td>

                                <td>{{ $val->LocationName }}</td>

                                <td>{{ $val->PayMode }}</td>

                                <td>{{ number_format($val->Amount,2) }}</td>

                                <td>{{ number_format($val->Balance,2) }}</td>

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