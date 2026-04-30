@extends('layouts.admin')

@section('content')
<style>
    .session-container {
        background: #e9f3ff;
        padding: 15px;
        border-radius: 8px;
    }
</style>
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container mt-4">
                <h2>Tee Booking Settings</h2>
                <form action="{{ route('tee_bookings.config.store') }}" method="POST">
                            @csrf
                            <h4>Booking Start Time: {{\App\CPU\Helpers::get_setting('booking_start_time')}}</h4>
                            <br>
                            <br>
                            <div class="row">
                                <!-- <div class="col-md-3 mb-3">
                                    <label for="booking_start_time" class="form-label">Booking Start Time:</label>
                                    <input type="time" id="booking_start_time" name="booking_start_time" value="{{\App\CPU\Helpers::get_setting('booking_start_time')}}" class="form-control" required>
                                </div> -->
                            
                                
                                <div class="col-md-3 mb-3">
                                    <label for="" class="form-label">Hours Before Booking:</label>
                                    <input type="number" name="hour_before_booking" class="form-control" value="{{\App\CPU\Helpers::get_setting('hour_before_booking')}}" required>
                                    <small>Start Time: {{$startTime}}</small>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <label for="" class="form-label">Hours Booking Range:</label>
                                    <input type="number" name="hour_booking_range" class="form-control" value="{{\App\CPU\Helpers::get_setting('hour_booking_range')}}"  required>
                                   <small>End Time: {{$endTime}}</small>

                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="" class="form-label">Total day of open booking:</label>
                                    <input type="number" name="day_open_booking" class="form-control" value="{{\App\CPU\Helpers::get_setting('day_open_booking')}}"  required>
                                  
                                </div>
                               
                            </div>
                            
                
                            <!-- Submit Button -->
                            <button type="submit" class="btn btn-success">Update</button>
                        </form>
            </div>
        
         
            </script>

        </section>
    </main>
@endsection
