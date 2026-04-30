@extends('layouts.admin')

@section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Create New Session Time</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Session</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('tee-session-times.index') }}">Times</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        <section class="section dashboard">
            <div class="container">
                <div class="row">
                    <div class="col-md-8 offset-md-2">
                        <form action="{{ route('tee-session-times.store') }}" method="POST">
                            @csrf
                            <div class="mb-3 form-group">
                                <label for="session_name_id">Session Name</label>
                                <input type="number" class="form-control" id="session_name_id" name="session_name_id" required>
                                <!--<select name="session_name_id" class="form-select" required>-->
                                <!--    <option value="" disabled selected>Select Session Name</option>-->
                                <!--    @foreach ($sessionNames as $sessionName)-->
                                <!--        <option value="{{ $sessionName->id }}">{{ $sessionName->name }}</option>-->
                                <!--    @endforeach-->
                                <!--</select>-->
                            </div>
                            <div class="mb-3 form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" class="form-control" id="start_time" name="start_time" required>
                            </div>
                            <div class="mb-3 form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" class="form-control" id="end_time" name="end_time" required>
                            </div>
                            <input type="hidden" name="original_start_time" value="{{ $teeSessionTime->start_time }}">
                            <input type="hidden" name="original_end_time" value="{{ $teeSessionTime->end_time }}">

                            <!--<div class="form-group">-->
                            <!--    <label for="is_active">Is Active</label>-->
                            <!--    <select class="form-control" id="is_active" name="is_active" required>-->
                            <!--        <option value="1">Yes</option>-->
                            <!--        <option value="0">No</option>-->
                            <!--    </select>-->
                            <!--</div>-->
                            <!--<div class="form-group">-->
                            <!--    <label for="created_by">Created By</label>-->
                            <!--    <input type="number" class="form-control" id="created_by" name="created_by" required>-->
                            <!--</div>-->
                            <!--<div class="form-group">-->
                            <!--    <label for="updated_by">Updated By</label>-->
                            <!--    <input type="number" class="form-control" id="updated_by" name="updated_by" required>-->
                            <!--</div>-->
                            <button type="submit" class="btn btn-primary">Create Session Time</button>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
@endsection
