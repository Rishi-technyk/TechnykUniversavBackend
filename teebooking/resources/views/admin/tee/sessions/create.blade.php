@extends('layouts.admin')

@section('content')

    <main id="main" class="main">

        <section class="section dashboard">
        <!-- resources/views/sessions/create.blade.php -->

    <div class="container mt-4">
        <h2>Create Session</h2>
        <div class="row mt-3">
            <form action="{{ route('sessions.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-6 mb-3">
                        <label for="session_name" class="form-label">Session Name:</label>
                        <input type="text" name="session_name" class="form-control" placeholder="Morning, Afternoon, Evening..." required>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="categories" class="form-label">Categories:</label>
                        <select name="categories[]" class="form-select" multiple>
                            @foreach ($categories as $category)
                                <option value="{{ $category->Code }}">{{ $category->CategoryType }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="start_time" class="form-label">Start Time:</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label for="end_time" class="form-label">End Time:</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-12 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="0" id="dependent_allowed" name="dependent_allowed">
                            <label class="form-check-label" for="dependent_allowed">
                                Dependent Allowed
                            </label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>


        </section>
    </main>
@endsection
