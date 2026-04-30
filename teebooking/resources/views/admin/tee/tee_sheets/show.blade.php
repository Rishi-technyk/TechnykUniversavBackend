@extends('layouts.admin')

@section('content')


<style>
th {
    color: black;
}

.table thead tr th,
.table tbody tr td {
    font-size: .80rem;
    text-transform: none;
    padding: 0.60rem;
}

.table tbody tr td button {
    font-size: .80rem;
}

.booking-details {
    border-bottom: 1px solid #dee2e6;
}

.card-title {
    padding: 13px 0 1px 0;
    font-size: 18px;
    font-weight: 500;
    color: #012970;
    font-family: "Poppins", sans-serif;
}

.card-title {
    padding: 0px 0 1px 0;
    font-size: 18px;
    font-weight: 500;
    color: #012970;
    font-family: "Poppins", sans-serif;
    margin-bottom: 0px;

}

.card {
    margin-bottom: 14px;
    border: none;
    border-radius: 5px;
    box-shadow: 0px 0 30px rgba(1, 41, 112, 0.1);
}

.multiselect-container>li>a>label {
    padding: 4px 20px 3px 20px;
}
.dropdown-toggle {
    width: 100% !important;
}
.btn-group {
    width: 100% !important;
}
.multiselect-container {
    width: 100% !important;
}
#multiselect+.btn-group>.btn-default {
    background-color: #fff !important;
    border: 1px solid #cbced4 !important;
}

</style>
<main id="main" class="main">

    <section class="section dashboard">


        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tee Sheets</h4>
            </div>
            <div class="row mt-3 ">

                <!-- Booking Date Card -->
                <div class="col-md-4">
                    <div class="card sales-card">
                        <div class="p-3">
                            <h5 class="card-title ml-4">Booking Date: <span
                                    class="ml-4 text-success small pt-1 fw-bold">
                                    {{ date('d-m-Y', strtotime(@$teeSheetsData[0]->teeBooking->booking_date)) }}

                                </span></h5>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card sales-card">
                        <div class="p-3">
                            <h5 class="card-title ml-4">Golf Start Time: <span
                                    class="ml-4 text-success small pt-1 fw-bold">
                                    {{ \Carbon\Carbon::parse(@$teeSheetsData[0]->teeBooking->golf_start_time)->format('g:iA')}}
                                </span></h5>

                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card sales-card">
                        <div class="p-3">
                            <h5 class="card-title ml-4">Golf End Time: <span
                                    class="ml-4 text-success small pt-1 fw-bold">
                                    {{ \Carbon\Carbon::parse(@$teeSheetsData[0]->teeBooking->golf_end_time)->format('g:iA')}}
                                </span></h5>
                        </div>
                    </div>
                </div>

            </div>
            <form class="mt-2" action="{{route('tee.tee_sheets.export')}}" method="post" id="">
                @csrf
                <input type="hidden" name="id" value="{{$id}}" id="searchDate">
                <div class="user-info-box">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="user-info-list">
                               
                                <label for="exampleInputEmail1">Tee Holes</label>
                                <select name="teeHole" id="teeHole" class="form-select" aria-label="Default select example">

                                    @foreach ($teeHoles as $value)
                                    <option value="{{$value->id}}" {{($value->id==@$teeHole)?'selected':''}}>
                                        {{$value->hole_number}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="user-info-list">
                                <label for="exampleInputEmail1">Session</label>
                                <select name="session_id" id="session_id" class="form-select" aria-label="Default select example">
                                    <option value="">All</option>
                                    @foreach ($teeSessions as $value)
                                    <option value="{{$value->id}}" {{($value->id==@$session_id)?'selected':''}}>
                                        {{$value->session_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <label for="exampleInputEmail1">&nbsp;</label>
                            <button type="submit" class="btn btn-primary ">Export Sheet</button>
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <form class="mt-2" action="{{route('tee.tee_sheets.show_search')}}" method="post" id="searchForm">
               
                <input type="hidden" name="teeHole" id="teeHoleSearch">
                <input type="hidden" name="session_id" id="session_id_search">
                <input type="hidden" name="id" value="{{$id}}" >
                @csrf
            </form>


            <div class="row mt-3">
                <div class="col-md-12">
                    <table id="tee_sheet" class="table">
                        <thead>
                            <tr>
                                <!-- <th>ID</th> -->
                                <!-- <th>Booking Date</th> -->
                                <th>Tee Time</th>
                                <!-- <th>Golf Start Time</th>
                                <th>Golf End Time</th> -->
                                <th>Session</th>
                                <!-- <th>Session Start Time</th>
                                <th>Session End Time</th> -->
                                <th>Tee Off Hole</th>
                                <th>Is Locked by Admin</th>
                                <th>Is Active</th>
                                <!-- <th>Actions</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($teeSheets as $teeSheet)
                            <tr>
                                <!-- <td>{{ $teeSheet->id }}</td> -->
                                <!-- <td>{{ $teeSheet->teeBooking->booking_date }}</td> -->
                                <td>{{ \Carbon\Carbon::parse($teeSheet->tee_time)->format('g:iA') }}</td>
                                <!-- <td>{{ \Carbon\Carbon::parse($teeSheet->teeBooking->golf_start_time)->format('g:iA') }}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->teeBooking->golf_end_time)->format('g:iA') }}
                                </td> -->
                                <td>{{ $teeSheet->session->session_name }}</td>
                                <!-- <td>{{ \Carbon\Carbon::parse($teeSheet->session->start_time)->format('g:iA') }}</td>
                                <td>{{ \Carbon\Carbon::parse($teeSheet->session->end_time)->format('g:iA') }}</td> -->
                                <td>{{ $teeSheet->teeHole->hole_number }}</td>
                                <td>
                                    <label class="switch switch-status-is-locked">
                                        <input type="checkbox" class="is_locked_by_admin_status"
                                            id="{{ $teeSheet->tee_sheet_pk }}"
                                            {{$teeSheet->is_locked_by_admin == 1?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <td>
                                    <label class="switch switch-status">
                                        <input type="checkbox" class="status" id="{{ $teeSheet->tee_sheet_pk }}"
                                            {{$teeSheet->active_status == 1?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                <!-- <td>
                                   
                                    <button class="btn btn-primary book-now-btn"
                                        data-booking-date="{{ $teeSheet->teeBooking->booking_date }}"
                                        data-tee-time="{{ \Carbon\Carbon::parse($teeSheet->tee_time)->format('g:iA') }}"
                                        data-session-name="{{ $teeSheet->session->session_name }}"
                                        data-tee-off-hole="{{ $teeSheet->teeHole->hole_number }}" data-toggle="modal"
                                        data-target="#bookNowModal">
                                        Book
                                    </button>
                                </td> -->
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- The modal structure -->
        <div class="modal" id="bookNowModal" tabindex="-1" role="dialog" aria-labelledby="bookNowModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bookNowModalLabel">Book Now</h5>
                        <button type="button" class="close cancelButton" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <!-- Populate this section with the pre-populated details and form for player details -->
                        <div class="row booking-details mb-3 small">
                            <div class="col-6">
                                <p class="mb-1"><b>Booking Date:</b> <span id="bookingDate"></span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><b>Tee Time:</b> <span id="teeTime"></span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><b>Session:</b> <span id="sessionName"></span></p>
                            </div>
                            <div class="col-6">
                                <p class="mb-1"><b>Tee Off Hole:</b> <span id="teeOffHole"></span></p>
                            </div>
                        </div>

                        <!-- Form for player details -->
                        <form id="playerDetailsForm">
                            <div class="row">
                                <div class="form-group col-md-6 mb-2">
                                    <label for="player1Name">Player 1 Name/Member ID</label>

                                    <input type="hidden" id="player1NameSelected" name="player1NameSelected">
                                    <input type="text" id="player1Name" class="form-control player-list"
                                        list="datalistOptions1">
                                    <datalist id="datalistOptions1">

                                    </datalist>
                                </div>
                                <!--<div class="form-group col-md-6 mb-2">-->
                                <!--    <label for="player1ID">Player 1 ID</label>-->
                                <!--    <input type="text" class="form-control" id="player1ID" placeholder="Player 1 ID">-->
                                <!--</div>-->
                                <div class="form-group col-md-6 mb-2">
                                    <label for="player2Name">Player 2 Name/Member ID</label>
                                    <input type="hidden" id="player2NameSelected" name="player2NameSelected">
                                    <input type="text" id="player2Name" class="form-control player-list"
                                        list="datalistOptions2">
                                    <datalist id="datalistOptions2">

                                    </datalist>
                                </div>
                                <!--<div class="form-group col-md-6 mb-2">-->
                                <!--    <label for="player2ID">Player 2 ID</label>-->
                                <!--    <input type="text" class="form-control" id="player2ID" placeholder="Player 2 ID">-->
                                <!--</div>-->
                                <div class="form-group col-md-6 mb-2">
                                    <label for="player3Name">Player 3 Name/Member ID</label>
                                    <input type="hidden" id="player3NameSelected" name="player3NameSelected">
                                    <input type="text" id="player3Name" class="form-control player-list"
                                        list="datalistOptions3">
                                    <datalist id="datalistOptions3">

                                    </datalist>
                                </div>
                                <!--<div class="form-group col-md-6 mb-2">-->
                                <!--    <label for="player3ID">Player 3 ID</label>-->
                                <!--    <input type="text" class="form-control" id="player3ID" placeholder="Player 3 ID">-->
                                <!--</div>-->
                                <div class="form-group col-md-6 mb-2">
                                    <label for="player4Name">Player 4 Name/Member ID</label>
                                    <input type="hidden" id="player4NameSelected" name="player4NameSelected">
                                    <input type="text" id="player4Name" class="form-control player-list"
                                        list="datalistOptions4">
                                    <datalist id="datalistOptions4">

                                    </datalist>
                                </div>
                                <!--<div class="form-group col-md-6 mb-2">-->
                                <!--    <label for="player4ID">Player 4 ID</label>-->
                                <!--    <input type="text" class="form-control" id="player4ID" placeholder="Player 4 ID">-->
                                <!--</div>-->
                            </div>
                            <!-- Repeat the above form rows for Player 2, Player 3, and Player 4 -->

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary cancelButton" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
@push('js')

<link href="https://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" rel="Stylesheet">
<link href="https://cdn.datatables.net/2.0.5/css/dataTables.dataTables.min.css" rel="Stylesheet">
<script src="https://code.jquery.com/ui/1.10.2/jquery-ui.js"></script>
<script src="https://cdn.datatables.net/2.0.5/js/dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        $(document).ready(function() {
        // Initialize DataTable without server-side processing
        var table = $('#tee_sheet').DataTable({
            "paging": true, // Enable pagination
            "pageLength": 10, // Set page length
            "ordering": false
            // Add your other DataTable options here
        });
    });


     $('#teeHole,#session_id').change(function() {
        $("#teeHoleSearch").val($("#teeHole").val());
        $("#session_id_search").val($("#session_id").val());
        $('#searchForm').submit();
    });



    $('.datatable-input').attr('placeholder', 'Search Tee Time, Session, Off Hole');
    $('.datatable-input').css('width', '300px');
    // Handle "Book Now" button click
    $('.book-now-btn').click(function() {
        // alert("jhi");
        // Get the row id from the data attribute
        const bookingDate = $(this).data('booking-date');
        const teeTime = $(this).data('tee-time');
        const sessionName = $(this).data('session-name');
        const teeOffHole = $(this).data('tee-off-hole');

        // Populate the modal with dynamic data
        $('#bookingDate').text(bookingDate);
        $('#teeTime').text(teeTime);
        $('#sessionName').text(sessionName);
        $('#teeOffHole').text(teeOffHole);

        // Show the modal
        $('#bookNowModal').modal('show');
    });

    $('.cancelButton').click(function() {
        // Hide the modal with fade-out effect
        $('.modal-backdrop').remove();
    });

    $('#bookNowModal').on('hidden.bs.modal', function() {
        // Use a small delay to ensure that the removal happens after the modal is hidden
        setTimeout(function() {
            // Remove the modal backdrop manually
            $('.modal-backdrop').remove();
        }, 100);
    });

    // Handle form submission
    $('#playerDetailsForm').submit(function(e) {
        e.preventDefault();

        // Handle form submission logic here

        // Close the modal
        $('#bookNowModal').modal('hide');
    });

    $(document).on('change', '.status', function() {
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
            success: function(data) {
                toastr.success('Status updated successfully');
                //window.location.reload();
            }
        });
    });

    $(document).on('change', '.is_locked_by_admin_status', function() {
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
            url: "{{route('tee_sheets.is-locked-by-admin-status-update')}}",
            method: 'POST',
            data: {
                id: id,
                status: status
            },
            success: function(data) {
                toastr.success('Status updated successfully');
                //window.location.reload();
                // window.location.href = data;
                // $('#tee_sheet').DataTable().ajax.reload();
            }
        });
    });

});

$(document).ready(function() {
    $('.player-list').on('input', function() {

        var currentPlayerInput = $(this);
        var userInput = currentPlayerInput.val();

        if (userInput.length >= 2) {
            $.ajax({
                url: "{{ route('autocomplete-members') }}",
                method: 'GET',
                data: {
                    userInput: userInput
                },
                success: function(response) {
                    var formattedOptions = formatResponse(response);
                    populateOptions(currentPlayerInput, formattedOptions);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                }
            });
        } else {
            clearOptions(currentPlayerInput);
        }
    });

    function formatResponse(response) {
        var formattedOptions = [];
        response.forEach(function(obj) {
            formattedOptions.push({
                label: obj.label,
                value: obj.value
            });
        });
        return formattedOptions;
    }

    $('.player-list').change(function() {
        var selectedLabel = $(this).val();
        var selectedId = $(this).siblings('datalist').find('option[value="' + selectedLabel + '"]')
            .data('value');
        var playerId = $(this).attr('id');
        // alert(playerId);
        $('#selectedLabel').text(selectedLabel);
        $('#' + playerId + 'Selected').val(selectedId);
    });

    function populateOptions(currentPlayerInput, options) {
        var datalist = currentPlayerInput.siblings('datalist');
        datalist.empty();

        options.forEach(function(option) {
            // console.log(option.label);
            datalist.append(`<option value="${option.label}" data-value="${option.value}">`);
        });
    }

    function clearOptions(currentPlayerInput) {
        var datalist = currentPlayerInput.siblings('datalist');
        datalist.empty();
    }
});
</script>
@endpush
@endsection