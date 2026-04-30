@extends('layouts.admin')

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container mt-4">
                <h2>Edit Session</h2>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <form action="{{ route('tee_bookings.update', $teeBooking->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label for="golf_timing_id" class="form-label">Golf Timing ID:</label>
                                <input type="number" name="golf_timing_id" class="form-control" value="{{ $teeBooking->golf_timing_id }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="Date" class="form-label">Date:</label>
                                <input type="date" name="Date" class="form-control" value="{{ $teeBooking->Date }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="Day" class="form-label">Day:</label>
                                <input type="text" name="Day" class="form-control" value="{{ $teeBooking->Day }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="Remarks" class="form-label">Remarks:</label>
                                <input type="text" name="Remarks" class="form-control" value="{{ $teeBooking->Remarks }}">
                            </div>
                            <div class="mb-3">
                                <label for="booking_status" class="form-label">Booking Status:</label>
                                <input type="text" name="booking_status" class="form-control" value="{{ $teeBooking->booking_status }}" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $teeBooking->is_active ? 'checked' : '' }}>
                                <label for="is_active" class="form-check-label">Is Active:</label>
                            </div>
                            <div class="mb-3">
                                <label for="created_by" class="form-label">Created By:</label>
                                <input type="number" name="created_by" class="form-control" value="{{ $teeBooking->created_by }}">
                            </div>
                            <div class="mb-3">
                                <label for="updated_by" class="form-label">Updated By:</label>
                                <input type="number" name="updated_by" class="form-control" value="{{ $teeBooking->updated_by }}">
                            </div>
                            <button type="submit" class="btn btn-primary">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
