@extends('layouts.web')
@section('content')
<style>
    .table {
        width: 100%;
    }
</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Transactions</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="row d-none d-lg-flex card mb-1 h-100">
                    <div class="card-body">
                        <!-- List View for Larger Screens -->
                        <table class="table table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Transaction ID</th>
                                    <th scope="col">Amount</th>
                                    <th scope="col">Payment Status</th>
                                    <th scope="col">Payment Type</th>
                                    <th scope="col">Payment Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tt as $key => $val)
                                <tr>
                                    <th scope="row">{{ ++$key }}</th>
                                    <td> <small>{{ $val->order_id }}</small> </td>
                                    <td>{{ number_format($val->amount, 2) }}</td>
                                    <td>
                                        @if($val->payment_status == 'Paid')
                                            <span class="text-success">Paid</span>
                                        @elseif($val->payment_status == 'Failed')
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span class="text-danger">Not Paid</span>
                                        @endif
                                    </td>
                                    <td>{{ $val->type }}</td>
                                    <td>{{ date("d-m-Y", strtotime($val->created_at)) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div >
                            {!! $tt->links() !!}
                        </div>
                    </div>
                </div>

                <div class="row d-lg-none mt-3">
                    <!-- Card View for Mobile Screens -->
                    @foreach($tt as $key => $val)
                        <div class="col-12 mb-3">
                            <div class="card h-100">
                                <div class="card-body">
                                    <p><strong>#{{ ++$key }}</strong></p>
                                    <h5 class="card-title">Order ID: {{ $val->order_id }}</h5>
                                    <p class="card-text"><strong>Bank Tr. ID:</strong> {{ $val->razorpay_order_id }}</p>
                                    <p class="card-text"><strong>Amount:</strong> {{ number_format($val->amount, 2) }}</p>
                                    <p class="card-text"><strong>Payment Status:</strong>
                                        @if($val->payment_status == 'Paid')
                                            <span class="text-success">Paid</span>
                                        @elseif($val->payment_status == 'Failed')
                                            <span class="text-danger">Failed</span>
                                        @else
                                            <span class="text-danger">Not Paid</span>
                                        @endif
                                    </p>
                                    <p class="card-text"><strong>Payment Type:</strong> {{ $val->type }}</p>
                                    <p class="card-text"><strong>Payment Date:</strong> {{ date("d-m-Y", strtotime($val->created_at)) }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection