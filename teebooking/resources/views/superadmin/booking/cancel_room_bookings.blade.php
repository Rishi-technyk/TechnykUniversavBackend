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
               Room Cancellation Register
            </div>
            
        </div>
    </div>
    <div class="card-body">

        <form action="" method="Get" class="mb5">

            <div class="row">

                <div class="col-lg-3">
                    <label>Member ID</label>
                    <input type="text" name="member_id" value="{{ $request->member_id }}" class="form-control" placeholder="Member ID">
                </div>

                <div class="col-lg-3">
                    <label>Booking No.</label>
                    <input type="text" name="booking_no" value="{{ $request->booking_no }}" class="form-control" placeholder="Booking No.">
                </div>

                <div class="col-lg-3">
                    <label>Check In</label>
                    <input type="date" name="checkin" value="{{ $request->checkin }}" class="form-control" placeholder="Check In">
                </div>

                <div class="col-lg-3">
                    <label>Check Out</label>
                    <input type="date" name="checkout" value="{{ $request->checkout }}" class="form-control" placeholder="Check Out">
                </div>

                <div class="col-lg-3 mt-2">
                    <button class="btn btn-sm btn-success mt-4" type="submit">Search</button>
                </div>

            </div>

        </form>
        <hr>
        <div class="table-container">
            <table class="table table-striped" id="example">
                <thead>
                    <tr>
                        <th scope="col">#</th>                                    
                        <th scope="col">Member ID</th>
                        <th scope="col">Name</th>
                        <th scope="col">Room</th>
                        <th scope="col">Nites</th>
                        <th scope="col">Booking No</th>
                        <th scope="col">Booking Date</th>
                        <th scope="col">Check IN</th>
                        <th scope="col">Check OUT</th>
                        <th scope="col">Amount</th>
                        <th scope="col">GST Amt</th>
                        <th scope="col">Total Amt</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datas as $key => $data)
                        <tr>
                            <?php 
                                $charges_Amt = '0';
                                $GST_Amt = '0';

                                $items = App\Models\RoomBookingItem::where('booking_id', $data->id)->get(); 

                                foreach ($items as $itm) {

                                    $charges_Amt += $itm->room_charges*$itm->no_of_days;

                                    $GST_Amt += $itm->gst_amount;
                                }
                            ?>
                            <th scope="row">{{ ++$key }}</th>                                        
                            <td>{{ $data->memberID }}</td>
                            <td>{{ $data->memberName }}</td>
                            <td>
                                @foreach($items as $itms)
                                    <small>{{ $itms->room->name }}</small> <br>
                                @endforeach
                            </td>
                            <td>
                                @foreach($items as $itms)
                                    <small>{{ $itms->no_of_days }}</small> <br>
                                @endforeach
                            </td>
                            <td>{{ $data->booking_number }}</td>
                            <td>{{ $data->created_at ? date("M d, Y", strtotime($data->created_at)) : '' }}</td>
                            <td>{{ $data->checkin ? date("M d, Y", strtotime($data->checkin)) : '' }}</td>
                            <td>{{ $data->checkout ? date("M d, Y", strtotime($data->checkout)) : '' }}</td>
                            <td>{{ $charges_Amt }}</td>
                            <td>{{ $GST_Amt }}</td>
                            <td>{{ $items->sum('room_charge_total') }}</td>
                            
                            <td style="text-align: left; padding-left: 1% !important;">
                                <a href="{{ route('admin.room.booking.details', encrypt($data->id)) }}" class="btn btn-sm btn-secondary">View</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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