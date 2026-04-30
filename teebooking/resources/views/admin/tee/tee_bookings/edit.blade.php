@extends('layouts.admin')

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container mt-4">
                <h2>Edit Tee Booking</h2>
                <h6>Booking Date: {{ \Carbon\Carbon::parse($teeBooking->booking_date)->format('d-m-Y') }}</h6>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <form action="{{ route('tee_bookings.update', $teeBooking->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                          
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label for="golf_start_time" class="form-label">Golf Start Time:</label>
                                    <input type="time" id="golf_start_time" name="golf_start_time" value="{{ $teeBooking->golf_start_time }}" class="form-control" required>
                                </div>
                            
                                <div class="col-6 mb-3">
                                    <label for="golf_end_time" class="form-label">Golf End Time:</label>
                                    <input type="time" id="golf_end_time" name="golf_end_time" value="{{ $teeBooking->golf_end_time }}" class="form-control" required>
                                </div>
                       
                               <!-- <div class="col-6 mb-3">
                                    <label for="from_date" class="form-label">Start Date:</label>
                                    <input type="date" name="from_date" class="form-control" required>
                                </div>
                                <div class="col-6 mb-3">
                                    <label for="to_date" class="form-label">End Date:</label>
                                    <input type="date" name="to_date" class="form-control" required>
                                </div> -->
                            </div>
                         
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
