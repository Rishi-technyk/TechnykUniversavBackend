@extends('layouts.admin')

@section('content')
<main id="main" class="main">
        <div class="pagetitle">
            <h1>Edit Session Name</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Tee Booking</a></li>
                    <li class="breadcrumb-item active">Edit Session Name</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        <section class="section dashboard">
    <div class="container mt-4">
        <!--<h4>Edit Session Name</h4>-->
        <div class="row mt-3">
            <div class="col-md-12">
                <form action="{{ route('session_names.update', $sessionName->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="{{ $sessionName->name }}" required>
                    </div>
                    <!--<div class="mb-3 form-check">-->
                    <!--    <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $sessionName->is_active ? 'checked' : '' }}>-->
                    <!--    <label for="is_active" class="form-check-label">Is Active:</label>-->
                    <!--</div>-->
                    <!--<div class="mb-3">-->
                    <!--    <label for="created_by" class="form-label">Created By:</label>-->
                    <!--    <input type="number" name="created_by" class="form-control" value="{{ $sessionName->created_by }}">-->
                    <!--</div>-->
                    <!--<div class="mb-3">-->
                    <!--    <label for="updated_by" class="form-label">Updated By:</label>-->
                    <!--    <input type="number" name="updated_by" class="form-control" value="{{ $sessionName->updated_by }}">-->
                    <!--</div>-->
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>
</section>
</main>
@endsection
