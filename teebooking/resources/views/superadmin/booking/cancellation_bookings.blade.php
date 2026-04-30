@extends('layouts.admin_web')
@section('content')
<style>
    table td {
        text-align: center !important;
    }

    .dataTables_filter {
        display: none !important;
    }

    .mb5 {
        margin-bottom: 5%;
    }

    .buttons-excel {
        background-color: #198754 !important;
        color: white !important;
    }

    .buttons-excel {
        display: block !important;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    th, td {
        padding: 8px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .table-container {
        width: 100%;
        overflow-x: auto;
    }

    .dataTables_scrollHeadInner {
        width: auto !important;
    }
</style>


<div class="card mb-1 h-100">
    <div class="card-header">                       
        
        <div class="row">
            <div class="col-lg-6">
                <div class="sideButton">
                    <a href="javascript:" id="sideButton"> <i class="fa fa-bars" aria-hidden="true"></i> </a>
                </div>
               Cancel Bookings
            </div>
            
        </div>
    </div>
    <div class="card-body">

        <form action="" method="Get" class="mb5">

            <div class="row">
            
                <div class="col-lg-3">
                    <input type="date" name="fundate" value="{{ $request->fundate }}" class="form-control" placeholder="Function Date">
                </div>

                <div class="col-lg-3">
                    <select class="form-control" name="session">
                        <option value="">Select Session</option>
                        @foreach($session as $sess)
                            <option value="{{ $sess->id }}" {{ $request->session==$sess->id?'selected':'' }}>{{ $sess->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3">
                    <input type="text" name="card_id" value="{{ $request->card_id }}" class="form-control" placeholder="Card ID">
                </div>

                <div class="col-lg-3">
                    <input type="text" name="member_id" value="{{ $request->member_id }}" class="form-control" placeholder="Member ID">
                </div>

                <div class="col-lg-3">
                    <select class="form-control" name="occupant_type">
                        <option value="">Select Occupant Type</option>
                        @foreach($occupant_type as $occ)
                            <option value="{{ $occ->id }}" {{ $request->occupant_type==$occ->id?'selected':'' }}>{{ $occ->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 mt-4">
                    <button class="btn btn-sm btn-success" type="submit">Search</button>
                </div>

            </div>

        </form>
        <hr>
        <table class="table table-striped" id="example">
          <thead>
                <tr>
                    <th data-breakpoints="lg">Booking No.</th>
                    <th data-breakpoints="lg">Booking Date</th>
                    <th data-breakpoints="lg">Card ID</th>
                    <th data-breakpoints="lg">Member ID</th>
                    <th data-breakpoints="lg">Name</th>
                    <th data-breakpoints="lg">Function Date</th>
                    <th data-breakpoints="lg">Occupant Type</th>
                    <th data-breakpoints="lg">Email</th>
                    <th data-breakpoints="lg">PAX</th>
                    <th data-breakpoints="lg">Remarks</th>
                    <th data-breakpoints="lg">Amount Paid</th>
                    <th data-breakpoints="lg">Venue Name</th>
                    <th data-breakpoints="lg">Session</th>
                    <th data-breakpoints="lg">Venue Amt</th>
                    <th data-breakpoints="lg">GSTPer</th>
                    <th data-breakpoints="lg">GSTAmt</th>
                    <th data-breakpoints="lg">NetAmount</th>
                    <th data-breakpoints="lg">Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datas as $key => $data)
                    <tr>
                        <td>{{ $data->banquet->booking_ID ?? '' }}</td>
                        <td>{{ $data->created_at ? date("d-m-Y", strtotime($data->created_at)) : '' }}</td>
                        <td>{{ $data->banquet->cardID ?? '' }}</td>
                        <td>{{ $data->banquet->memberID ?? '' }}</td>
                        <td>{{ $data->banquet->memberName ?? '' }}</td>
                        <td>{{ $data->funDate ? date("d-m-Y", strtotime($data->funDate)) : '' }}</td>
                        <td>{{ $data->banquet->occupant->name ?? '' }}</td>
                        <td>{{ $data->banquet->memberEmail ?? '' }}</td>
                        <td>{{ $data->banquet->noofPerson ?? '' }}</td>
                        <td>{{ $data->banquet->remark ?? '' }}</td>
                        <?php
                            $total_a = DB::table('banquet_booking_charges')->where('banquet_booking_id', $data->banquet->id ?? '')->sum('total');
                        ?>
                        <td>{{ number_format($total_a, 2) }}</td>
                        <td>{{ $data->venue->name ?? '' }}</td>
                        <?php
                            $session = DB::table('sessions')->where('id', $data->session_id)->first();
                        ?>
                        <td>{{ $session->name ?? '' }}</td>
                        <td>{{ number_format($data->charges ?? '0', 2) }}</td>
                        <td>{{ $data->gst_per ?? '' }}%</td>
                        <td>{{ number_format($data->gst_amount ?? '0', 2) }}</td>
                        <td>{{ number_format($data->total ?? '0', 2) }}</td>
                        <?php $transaction = DB::table('transactions')->where('banquet_booking_id', $data->banquet->id ?? '')->first(); ?>
                        <td>
                            @if($transaction && $transaction->payment_status=='Paid')
                            <span class="text-success">{{ $transaction->payment_status }}</span>
                            @else
                            <span class="text-danger">{{ $transaction->payment_status ?? '' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('js')
<script>
    $(document).ready(function () {
        const categoriesSideBar = document.querySelector('#categoriesSideBar')

        categoriesSideBar.classList.toggle('categoriesSideBar--is-open')
    });
</script>
@endpush()
@endsection