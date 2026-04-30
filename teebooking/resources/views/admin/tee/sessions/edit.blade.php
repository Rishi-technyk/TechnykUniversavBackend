@extends('layouts.admin')

@section('content')
<style>
    
    </style>
    <main id="main" class="main">

        <section class="section dashboard">
        <!-- resources/views/sessions/create.blade.php -->

            <div class="container mt-4">
                <h2>Edit Session</h2>
                <div class="row mt-3">
                    <form action="{{ route('sessions.update', $session->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label for="session_name" class="form-label">Session Name:</label>
                                <input type="text" name="session_name" class="form-control" value="{{ $session->session_name }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="categories" class="form-label">Categories:</label>
                                <select name="categories[]" class="form-select" multiple>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->Code }}" {{ in_array($category->Code, $selectedCategories) ? 'selected' : '' }} >{{ $category->CategoryType }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="start_time" class="form-label">Start Time:</label>
                                <input type="time" name="start_time" class="form-control" value="{{ $session->start_time }}" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label for="end_time" class="form-label">End Time:</label>
                                <input type="time" name="end_time" class="form-control" value="{{ $session->end_time }}" required>
                            </div>
                            <div class="col-12 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="dependent_allowed" name="dependent_allowed"  {{ $session->dependent_allowed ? 'checked' : '' }}>
                                    <label class="form-check-label" for="dependent_allowed">
                                        Dependent Allowed
                                    </label>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
@endsection

