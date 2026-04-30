@extends('frontend.layouts.app')

@section('title', 'Table Booking')
<style>
    .contact-select {
        height: 38px !important;
    }

    .nice-select .list {
        width: 100%; 
    }
</style>
@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">Table Booking</h4>
    </div>
    <!-- Breadcrumb Section End -->

    @if(Session::has('message'))
    <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('message') }}</p>
    @endif

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <form action="" method="get">
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="form-group">
                        <input type="date" class="form-control" name="booking_date" id="dateInput" value="{{ $request->booking_date }}">
                    </div>
                </div>
                
                <div class="col-lg-3 col-sm-6">
                    <div class="form-group">
                        <select name="venue_id" id="venueSelect" class="contact-select" required>
                            <option value="">Select Venue</option>
                            @foreach($venues as $venue)
                                <option value="{{ $venue->id }}" {{ $request->venue_id == $venue->id ? 'selected' : '' }}>{{ $venue->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="form-group">
                        <select name="meal_id" id="mealSelect" class="contact-select" onchange="getTimes(this.value)" required>
                            <option value="">Select Meal</option>
                            @foreach($meals as $meal)
                                <option value="{{ $meal->id }}" {{ $request->meal_id == $meal->id ? 'selected' : '' }}>{{ $meal->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="form-group" id="timSelectField">
                        <select name="time_id" id="timSelect" class="contact-select" required>
                            <option value="">Select Time</option>
                            @foreach($times as $time)
                                <option value="{{ $time->id }}" {{ $request->time_id == $time->id ? 'selected' : '' }}>{{ $time->time }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>

            <div class="text-center mt-4">

                <button type="submit">Show Availability</button>

            </div>

        </form>

        <div class="row">
            @if($table)
                <div class="container mt-4">

                    <form action="{{ route('table.booking.store') }}" method="post">
                        @csrf
                        <input type="hidden" name="booking_date" value="{{ $request->booking_date }}">
                        <input type="hidden" name="venue_id" value="{{ $request->venue_id }}">
                        <input type="hidden" name="meal_id" value="{{ $request->meal_id }}">
                        <input type="hidden" name="time_id" value="{{ $request->time_id }}">
                        <div class="row">
                            <div class="col-lg-3"></div>
                            <div class="col-lg-6 form-group">
                                <select name="table_id" class="contact-select" required>
                                    @if(count($table))
                                        <option value="">Select Table</option>
                                        @foreach($table as $t)
                                            <option value="{{ $t->id }}" {{ $request->table_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                                        @endforeach
                                    @else
                                        <option value="">Sorry! There is no table available.</option>
                                    @endif
                                </select>
                            </div>

                        </div>

                        <div class="text-center mt-4">

                            <button type="submit">Book Table</button>

                        </div>

                    </form>

                </div>
            @endif
            
        </div>

    </div>

</div>

@endsection

@section('script')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        let input = document.getElementById("dateInput");

        let today = new Date();
        let tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);

        // YYYY-MM-DD format
        let formatDate = (date) => date.toISOString().split('T')[0];

        input.min = formatDate(today);     // ✅ today
        input.max = formatDate(tomorrow);  // ✅ today + 1
    });
</script>
<script>
    function getTimes(mealId) {
        let venueId = document.getElementById('venueSelect').value;
        let bookingDate = document.getElementById('dateInput').value;

        if (!mealId || !bookingDate) {
            toastr.warning('Please select meal, venue, and date first.');
            return;
        }

        $.ajax({
            url: "{{ route('get.times') }}",
            type: "POST",
            data: {mealId:mealId, _token:'{{ csrf_token() }}'},
            success: function(res){
                let timeSelect = $('#timSelectField');
                $(timeSelect).html(res);
            },
            error: function(xhr){
                let msg = 'Something went wrong';
                if(xhr.responseJSON && xhr.responseJSON.message){
                    msg = xhr.responseJSON.message;
                }
                toastr.error(msg);
            }
        });

    }
</script>
@endsection