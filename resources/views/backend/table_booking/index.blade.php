@extends('backend.layouts.app')



@section('title', 'Admin | Table Bookings')



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

                    <h1>Table Bookings</h1>

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

                                            <label>Booking Date</label>

                                            <input type="date" name="booking_date" value="{{ $request->booking_date }}"
                                                class="form-control" placeholder="Booking Date">

                                        </div>



                                        <div class="col-lg-3">

                                            <label>Venue</label>

                                            <select class="form-control" name="venue">

                                                <option value="">Select Venue</option>

                                                @foreach($venues as $venue)

                                                <option value="{{ $venue->id }}"
                                                    {{ $request->venue==$venue->id?'selected':'' }}>{{ $venue->name }}
                                                </option>

                                                @endforeach

                                            </select>

                                        </div>



                                        <div class="col-lg-3">

                                            <label>Time</label>

                                            <select class="form-control" name="time">

                                                <option value="">Select Time</option>

                                                @foreach($times as $time)

                                                <option value="{{ $time->id }}"
                                                    {{ $request->time==$time->id?'selected':'' }}>{{ $time->time }}
                                                </option>

                                                @endforeach

                                            </select>

                                        </div>



                                        <div class="col-lg-3 mt-2 align-self-end">

                                            <button class="btn btn-sm btn-outline-info mt-4"
                                                type="submit">Search</button>

                                            <a href="{{ route('admin.table_bookings') }}"
                                                class="btn btn-sm btn-outline-danger mt-4">Reset</a>

                                        </div>



                                    </div>



                                </form>

                            </div>



                            <table id="example1" class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th scope="col">#</th>

                                        <th scope="col">Member</th>

                                        <th scope="col">Booking Date</th>

                                        <th scope="col">Venue</th>

                                        <th scope="col">Meal</th>

                                        <th scope="col">Time</th>

                                        <th scope="col">Table</th>

                                        <th scope="col">Booking Status</th>

                                        <th scope="col">Created Date</th>

                                    </tr>

                                </thead>

                                <tbody>

                                    @foreach($datas as $key => $data)

                                    <tr>

                                        <td>{{ $key+1 }}</td>

                                        <td>{{ $data->member->DisplayName ?? '' }} ({{ $data->member->MemberID }})</td>

                                        <td>{{ $data->booking_date ? date("d-m-Y", strtotime($data->booking_date)) : '' }}
                                        </td>

                                        <td>{{ $data->venue->name ?? '' }}</td>

                                        <td>{{ $data->meal->name ?? '' }}</td>

                                        <td>{{ $data->time->time ?? '' }}</td>

                                        <td>{{ $data->table->name ?? '' }}</td>

                                        <td>
                                            @if($data->status=='Booked')
                                            <span class="text-success">Booked</span>
                                            @else
                                            <span class="text-danger">Cancelled</span>
                                            @endif
                                        </td>

                                        <td>{{ $data->created_at ? date("d-m-Y H:s:i", strtotime($data->created_at)) : '' }}
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