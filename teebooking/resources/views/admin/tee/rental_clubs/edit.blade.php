@extends('layouts.admin')

@section('content')
<main id="main" class="main">
      

        <section class="section dashboard">

    <div class="container mt-4">
        <h2>Edit Rental Club</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('rental_clubs.update', $rentalClub->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="{{ $rentalClub->name }}" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

</section>
</main>
@endsection
