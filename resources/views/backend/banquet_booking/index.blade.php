@extends('backend.layouts.app')

@section('title', 'Admin | Banquet Bookings')

@section('style')
<style>
    .buttons-excel {
        display: block;
    }
</style>
@endsection

@section('content')

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Banquet Bookings</h1>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                
                <div class="col-12">

                    <div class="card card-outline card-info">
                        <!-- /.card-header -->
                        <div class="card-body">

                            <div class="mb-2 mt-2">
                                <form action="" method="Get" class="mb-4">

                                    <div class="row">

                                        <div class="col-lg-3">
                                            <label>Function Date</label>
                                            <input type="date" name="fundate" value="{{ $request->fundate }}" class="form-control" placeholder="Function Date">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Session</label>
                                            <select class="form-control" name="session">
                                                <option value="">Select Session</option>
                                                @foreach($session as $sess)
                                                    <option value="{{ $sess->id }}" {{ $request->session==$sess->id?'selected':'' }}>{{ $sess->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Card ID</label>
                                            <input type="text" name="card_id" value="{{ $request->card_id }}" class="form-control" placeholder="Card ID">
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Member ID</label>
                                            <input type="text" name="member_id" value="{{ $request->member_id }}" class="form-control" placeholder="Member ID">
                                        </div>

                                        <div class="col-lg-3 mt-2">
                                            <label>Occupant Type</label>
                                            <select class="form-control" name="occupant_type">
                                                <option value="">Select Occupant Type</option>
                                                @foreach($occupant_type as $occ)
                                                    <option value="{{ $occ->id }}" {{ $request->occupant_type==$occ->id?'selected':'' }}>{{ $occ->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mt-2">
                                            <label>Status</label>
                                            <select class="form-control" name="status">
                                                <option value="">Select Status</option>
                                                <option value="Active" {{ $request->status=='Active'?'selected':'' }}>Booked</option>
                                                <option value="Cancelled" {{ $request->status=='Cancelled'?'selected':'' }}>Cancelled</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mt-2 align-self-end">
                                            <button class="btn btn-sm btn-outline-info mt-4" type="submit">Search</button>
                                            <a href="{{ route('admin.banquet_bookings') }}" class="btn btn-sm btn-outline-danger mt-4">Reset</a>
                                        </div>

                                    </div>

                                </form>
                            </div>

                            <table id="example1" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Booking No.</th>
                                        <th scope="col">Booking Date</th>
                                        <th scope="col">Card ID</th>
                                        <th scope="col">Member ID</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Function Date</th>
                                        <th scope="col">Occupant Type</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">PAX</th>
                                        <th scope="col">Remarks</th>
                                        <th scope="col">Amount Paid</th>
                                        <th scope="col">Venue Name</th>
                                        <th scope="col">Session</th>
                                        <th scope="col">Venue Amt</th>
                                        <th scope="col">GSTPer</th>
                                        <th scope="col">GSTAmt</th>
                                        <th scope="col">NetAmount</th>
                                        <th scope="col">Payment Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($datas as $key => $data)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
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
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>

@endsection

@section('script')

@endsection