

@extends('layouts.admin')

@section('content')
<main id="main" class="main">
    <section class="section dashboard">
 
    <div class="container mt-4">
        <h2>Edit Tee Slot Interval</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('tee_slot_intervals.update', $teeSlotInterval->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="tee_sheet_id" class="form-label">Tee Sheet:</label>
                        <select name="tee_sheet_id" class="form-select" required>
                            <option value="" disabled>Select Tee Sheet</option>
                            @foreach ($teeSheets as $teeSheet)
                                <option value="{{ $teeSheet->id }}" {{ $teeSheet->id == $teeSlotInterval->tee_sheet_id ? 'selected' : '' }}>{{ $teeSheet->id }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="session_name_id" class="form-label">Session Name:</label>
                        <select name="session_name_id" class="form-select" required>
                            <option value="" disabled>Select Session Name</option>
                            @foreach ($sessionNames as $sessionName)
                                <option value="{{ $sessionName->id }}" {{ $sessionName->id == $teeSlotInterval->session_name_id ? 'selected' : '' }}>{{ $sessionName->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="session_time_id" class="form-label">Session Time:</label>
                        <select name="session_time_id" class="form-select" required>
                            <option value="" disabled>Select Session Time</option>
                            @foreach ($sessionTimes as $sessionTime)
                                <option value="{{ $sessionTime->id }}" {{ $sessionTime->id == $teeSlotInterval->session_time_id ? 'selected' : '' }}>{{ $sessionTime->session_time_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="slot_interval" class="form-label">Slot Interval:</label>
                        <input type="text" name="slot_interval" class="form-control" value="{{ $teeSlotInterval->slot_interval }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="tee_off_hole" class="form-label">Tee Off Hole:</label>
                        <input type="text" name="tee_off_hole" class="form-control" value="{{ $teeSlotInterval->tee_off_hole }}" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $teeSlotInterval->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Is Active:</label>
                    </div>

                    <div class="mb-3">
                        <label for="created_by" class="form-label">Created By:</label>
                        <input type="number" name="created_by" class="form-control" value="{{ $teeSlotInterval->created_by }}">
                    </div>

                    <div class="mb-3">
                        <label for="updated_by" class="form-label">Updated By:</label>
                        <input type="number" name="updated_by" class="form-control" value="{{ $teeSlotInterval->updated_by }}">
                    </div>

                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</section>
</main>
@endsection

