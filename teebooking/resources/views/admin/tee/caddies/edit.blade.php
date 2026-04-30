@extends('layouts.admin')

@section('content')
<main id="main" class="main">
        <div class="pagetitle">
            <h1>Room Add</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Room</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

       <!-- resources/views/caddies/edit.blade.php -->

    <div class="container mt-4">
        <h2>Edit Caddy</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('caddies.update', $caddy->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="{{ $caddy->name }}" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $caddy->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Is Active:</label>
                    </div>
                
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

</main>
@endsection
