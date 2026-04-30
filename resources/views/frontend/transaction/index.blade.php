@extends('frontend.layouts.app')

@section('title', 'Transactions')

@section('content')

<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        
        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>

            <table class="table table-border">

                <thead>

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

                    @if(count($tt))

                        @foreach($tt as $key => $val)

                            <tr>

                                <th scope="row">{{ ++$key }}</th>

                                <td>{{ $val->order_id }} </td>

                                <td>{{ format_price($val->amount, 2) }}</td>

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

                    @else

                        <tr>

                            <td colspan="6" class="text-center">No Data Available!</td>

                        </tr>

                    @endif

                </tbody>

            </table>

            <div class="pagination-wrapper">
                {{ $tt->links('pagination::bootstrap-5') }}
            </div>

        </div>
        

    </div>

</section>



@endsection



@section('script')



@endsection