@extends('layouts.admin')

@section('content')
<main id="main" class="main">
        <div class="pagetitle">
            <h1>Add Session Name</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Tee Booking</a></li>
                    <li class="breadcrumb-item active">Add Session Name</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        <section class="section dashboard">
    <div class="container mt-4">
        <!--<h4>Add Session Name</h4>-->
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('session_names.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <!--<div class="mb-3 form-check">-->
                    <!--    <input type="checkbox" name="is_active" class="form-check-input" value="1">-->
                    <!--    <label for="is_active" class="form-check-label">Is Active:</label>-->
                    <!--</div>-->
                    <!--<div class="mb-3">-->
                    <!--    <label for="created_by" class="form-label">Created By:</label>-->
                    <!--    <input type="number" name="created_by" class="form-control">-->
                    <!--</div>-->
                    <!--<div class="mb-3">-->
                    <!--    <label for="updated_by" class="form-label">Updated By:</label>-->
                    <!--    <input type="number" name="updated_by" class="form-control">-->
                    <!--</div>-->
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>
</section>
</main>
@endsection
