@extends('layouts.admin')

@section('content')
    <main id="main" class="main">
  
        <!-- End Page Title -->

        <section class="section dashboard">
        <!-- resources/views/sessions/create.blade.php -->


    <div class="container mt-4">
        <h2>Create Tee Hole</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('tee_holes.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="hole_number" class="form-label">Hole Number:</label>
                        <input type="number" name="hole_number" class="form-control" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1">
                        <label for="is_active" class="form-check-label">Is Active:</label>
                    </div>
                    <div class="mb-3">
                        <label for="created_by" class="form-label">Created By:</label>
                        <input type="number" name="created_by" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="updated_by" class="form-label">Updated By:</label>
                        <input type="number" name="updated_by" class="form-control">
                    </div>
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>




        </section>
    </main>
@endsection
