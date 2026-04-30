@extends('layouts.admin')

@section('content')

<main id="main" class="main">

        <div class="container ">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tee Bookings</h4>
                <a href="{{ route('tee_bookings.create') }}" class="btn btn-primary">Add Tee Booking</a>
            </div>


            <div class="row mt-3">
                <div class="col-md-12">
                    <table class="table datatableBooking">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking Date</th>
                                <th>Golf Start Time</th>
                                <th>Golf End Time</th>
                                <th>Is Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teeBookings as $teeBooking)
                                <tr>
                                    <td>{{ $teeBooking->id }}</td>
                                    <!--<td>{{ $teeBooking->booking_date }}</td>-->
                                    <td><a href="{{ route('tee.tee_sheets.show', ['id' => $teeBooking->id]) }}">
                                        {{ $teeBooking->booking_date }}
                                    </a></td>
                                    <td>{{ $teeBooking->golf_start_time }}</td>
                                    <td>{{ $teeBooking->golf_end_time }}</td>
                                    <!--<td>{{ $teeBooking->is_active ? 'Yes' : 'No' }}</td>-->
                                    <td>
                                        <label class="switch switch-status">
                                            <input type="checkbox" class="status"
                                                   id="{{ $teeBooking->id }}" {{$teeBooking->is_active == 1?'checked':''}}>
                                            <span class="slider round"></span>
                                        </label>
                                    </td>
                                    <td>
                                        <a href="{{ route('tee_bookings.edit', $teeBooking->id) }}" class="btn btn-primary">Edit</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        </section>
    </main>
    <!-- Include JavaScript to handle dynamic addition of session fields -->
  
    @push('js')
    <script>
//   $('.datatableBooking').DataTable({
//     "order": [[ 1, "asc" ]] 
// });

$(document).ready(function () {
    new simpleDatatables.DataTable('.datatableBooking', {
      sort: true,
      sortOrder: 'DESC', 
      sortColumnIndex: 3
    });
  });
   $(document).on('change', '.status', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var status = 1;
            } else if ($(this).prop("checked") == false) {
                var status = 0;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('tee_bookings.status-update')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function (data) {
                    toastr.success('Status updated successfully');
                }
            });
        });
    </script>
    @endpush
@endsection
