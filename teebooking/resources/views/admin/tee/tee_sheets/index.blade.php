@extends('layouts.admin')

@section('content')
<style>
    th{
        color:black;
    }
    .table thead tr th, .table tbody tr td {
        font-size: .80rem;
        text-transform: none;
        padding: 0.60rem;
    }
    .table tbody tr td a {
        font-size: .80rem;
    }
</style>
<main id="main" class="main">

    <section class="section dashboard">


        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tee Sheets</h4>
                <!--<a href="{{ route('tee_sheets.create') }}" class="btn btn-primary">Add Tee Sheet</a>-->
            </div>


            <div class="row mt-3">
                <div class="col-md-12">
                    <table class="table datatable">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Booking Date</th>
                                <th>Tee Time</th>
                                <th>Golf Start Time</th>
                                <th>Golf End Time</th>
                                <th>Session</th>
                                <th>Session Start Time</th>
                                <th>Session End Time</th>
                                <th>Tee Off Hole</th>
                                <th>Is Locked by Admin</th>
                                <th>Is Active</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teeSheets as $teeSheet)
                            <tr>
                                <td>{{ $teeSheet->id }}</td>
                                <td>{{ $teeSheet->teeBooking->booking_date }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->tee_time)->format('g:iA') }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->teeBooking->golf_start_time)->format('g:iA') }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->teeBooking->golf_end_time)->format('g:iA') }}</td>
                                <td>{{ $teeSheet->session->session_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->session->start_time)->format('g:iA') }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->session->end_time)->format('g:iA') }}</td>
                                <td>{{ $teeSheet->teeHole->hole_number }}</td>
                                <td>
                                    <label class="switch switch-status-is-locked">
                                        <input type="checkbox" class="status"
                                               id="{{ $teeSheet->id }}" {{$teeSheet->is_locked_by_admin == 1?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="switch switch-status">
                                        <input type="checkbox" class="status"
                                               id="{{ $teeSheet->id }}" {{$teeSheet->is_active == 1?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <a href="{{ route('tee_sheets.edit', $teeSheet->id) }}"
                                        class="btn btn-primary">Edit</a>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
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
                url: "{{route('tee_sheets.status-update')}}",
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
@endsection