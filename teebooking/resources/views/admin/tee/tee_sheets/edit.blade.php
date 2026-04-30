@extends('layouts.admin')

@section('content')
<main id="main" class="main">

    <section class="section dashboard">
        <!-- resources/views/sessions/edit.blade.php -->


        <div class="container mt-4">
            <h2>Edit Tee Sheet</h2>
            <div class="row mt-3">
                <div class="col-md-6">
                    <form action="{{ route('tee_sheets.update', $teeSheet->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="tee_booking_id" class="form-label">Tee Booking ID:</label>
                            <input type="number" name="tee_booking_id" class="form-control"
                                value="{{ $teeSheet->tee_booking_id }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="tee_time" class="form-label">Tee Time:</label>
                            <input type="text" name="tee_time" class="form-control" value="{{ $teeSheet->tee_time }}"
                                required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_locked_by_admin" class="form-check-input" value="1"
                                {{ $teeSheet->is_locked_by_admin ? 'checked' : '' }}>
                            <label for="is_locked_by_admin" class="form-check-label">Is Locked by Admin:</label>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="is_active" class="form-check-input" value="1"
                                {{ $teeSheet->is_active ? 'checked' : '' }}>
                            <label for="is_active" class="form-check-label">Is Active:</label>
                        </div>
                        <div class="mb-3">
                            <label for="created_by" class="form-label">Created By:</label>
                            <input type="number" name="created_by" class="form-control"
                                value="{{ $teeSheet->created_by }}">
                        </div>
                        <div class="mb-3">
                            <label for="updated_by" class="form-label">Updated By:</label>
                            <input type="number" name="updated_by" class="form-control"
                                value="{{ $teeSheet->updated_by }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </div>


    </section>
</main>
@endsection