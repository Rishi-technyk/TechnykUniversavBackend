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

        <!-- resources/views/caddies/create.blade.php -->


    <div class="container mt-4">
        <h2>Create Caddy</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('caddies.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                  
                
                    <button type="submit" class="btn btn-primary">Create</button>
                </form>
            </div>
        </div>
    </div>


 
   
</main>
@endsection
