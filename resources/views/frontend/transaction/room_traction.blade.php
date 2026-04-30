@extends('frontend.layouts.app')



@section('title', 'Room Transactions')



@section('content')



<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Room Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        
        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>

            <table class="table table-border">

                <thead>

                    <tr>

                        <th scope="col">#</th>                                    

                        <th scope="col">Member ID</th>

                        <th scope="col">Booking No</th>

                        <th scope="col">Booking Date</th>

                        <th scope="col">Check IN</th>

                        <th scope="col">Check OUT</th>

                        <th scope="col">Payment Status</th>

                        <th scope="col">Booking Status</th>

                        <th scope="col">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @if(isset($datas))

                    @foreach($datas as $key => $data)

                        <tr>

                            <?php $transaction = DB::table('transactions')->where('transID', $data->booking_number)->first(); ?>

                            <th scope="row">{{ ++$key }}</th>                                        

                            <td>{{ $data->memberID }}</td>

                            <td>{{ $data->booking_number }}</td>

                            <td>{{ $data->created_at ? date("M d, Y", strtotime($data->created_at)) : '' }}</td>

                            <td>{{ $data->checkin ? date("M d, Y", strtotime($data->checkin)) : '' }}</td>

                            <td>{{ $data->checkout ? date("M d, Y", strtotime($data->checkout)) : '' }}</td>

                            <td>

                                

                                @if($transaction && $transaction->payment_status=='Paid')

                                <span class="text-success">{{ $transaction->payment_status }}</span>

                                @else

                                <span class="text-danger">Not Paid</span>

                                @endif

                            </td>

                            <td>

                                

                                @if($data->status == 'Active')

                                    <span class="text-success">Active</span>

                                @elseif($data->status == 'Cancelled')

                                    <span class="text-danger">Cancelled</span>

                                @else

                                    <span class="text-warning">Pending</span>

                                @endif

                            </td>

                            <td>

                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="{{ route('room.booking.details', encrypt($data->id)) }}"><button type="button" class="btn btn-sm btn-secondary">View</button></a>
                                    @if($transaction && $transaction->payment_status=='Paid')
                                    <a href="{{ route('room.booking.cancel', encrypt($data->id)) }}"><button type="button" class="btn btn-sm btn-secondary">Cancel</button></a>
                                    @endif
                                </div>

                            </td>

                        </tr>

                    @endforeach

                    @else

                        <tr>

                            <td colspan="9" class="text-center">No Booking</td>

                        </tr>

                    @endif

                </tbody>

            </table>

            <div class="pagination-wrapper">
                {{ $datas->links('pagination::bootstrap-5') }}
            </div>
            
        </div>
        

    </div>

</section>



@endsection



@section('script')



@endsection