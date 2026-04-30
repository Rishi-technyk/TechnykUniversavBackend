@extends('frontend.layouts.app')

@section('title', 'Banquet Transactions')

@section('content')

<section class="room-details-section">

    <div class="container">

        <!-- Breadcrumb Section Begin -->

        <div class="card-section-title-box">

            <span class="card-section-bar"></span>

            <h4 class="card-section-title">Banquet Transactions</h4>

        </div>

        <!-- Breadcrumb Section End -->

        
        <div class="card-section-title-box">
            
            <span class="card-section-bar"></span>

            <table class="table table-border">

                <thead>

                    <tr>

                        <th scope="col">#</th>                                    

                        <th scope="col">Name</th>

                        <th scope="col">Booking No</th>

                        <th scope="col">Booking Date</th>

                        <th scope="col">No. of Person</th>

                        <th scope="col">Function Date</th>

                        <th scope="col">Payment Status</th>

                        <th scope="col">Booking Status</th>

                        <th scope="col">Action</th>

                    </tr>

                </thead>

                <tbody>

                    @if(isset($datas))

                    @foreach($datas as $key => $data)

                        <tr>

                            <?php $transaction = DB::table('transactions')->where('banquet_booking_id', $data->id)->first(); ?>

                            <th scope="row">{{ ++$key }}</th>                                        

                            <td>{{ $data->memberName }}</td>

                            <td>{{ $transaction->transID ?? 'N/A' }}</td>

                            <td>@if($transaction) {{ $transaction->created_at ? date("d-m-Y", strtotime($transaction->created_at)) : '' }} @endif</td>

                            <td>{{ $data->noofPerson }}</td>

                            <td>{{ $data->funDate ? date("d-m-Y", strtotime($data->funDate)) : '' }}</td>

                            <td>

                                

                                @if($transaction && $transaction->payment_status=='Paid')

                                <span class="text-success">{{ $transaction->payment_status }}</span>

                                @else

                                <span class="text-danger">{{ $transaction->payment_status ?? 'Not Paid' }}</span>

                                @endif

                            </td>

                            <td>

                                <?php $checkVenues = App\Models\BanquetBookingCharges::where('banquet_booking_id', $data->id)->where('status','Active')->exists(); ?>



                                @if($data->status != 'Pending')

                                    @if($checkVenues)

                                        <span class="text-success">Active</span>

                                    @else

                                        <span class="text-danger">Cancelled</span>

                                    @endif

                                @else

                                    <span class="text-warning">Pending</span>

                                @endif

                            </td>

                            <td>

                                <div class="btn-group" role="group" aria-label="Basic example">
                                    <a href="{{ route('banquet.booking.details', encrypt($data->id)) }}"><button type="button" class="btn btn-sm btn-secondary">View</button></a>
                                    @if($transaction && $transaction->payment_status=='Paid')
                                    <a href="{{ route('banquet.booking.cancel', encrypt($data->id)) }}"><button type="button" class="btn btn-sm btn-secondary">Cancel</button></a>
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