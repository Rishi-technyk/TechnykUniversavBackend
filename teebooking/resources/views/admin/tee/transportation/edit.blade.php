@extends('layouts.admin')

@section('content')
<main id="main" class="main">
    

        <section class="section dashboard">
  
    <div class="container mt-4">
        <h2>Edit Transportation</h2>
        <div class="row mt-3">
            <div class="col-md-6">
                <form action="{{ route('transportations.update', $transportation->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" name="name" class="form-control" value="{{ $transportation->name }}" required>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" name="is_active" class="form-check-input" value="1" {{ $transportation->is_active ? 'checked' : '' }}>
                        <label for="is_active" class="form-check-label">Is Active:</label>
                    </div>
                    <div class="mb-3">
                        <label for="created_by" class="form-label">Created By:</label>
                        <input type="number" name="created_by" class="form-control" value="{{ $transportation->created_by }}">
                    </div>
                    <div class="mb-3">
                        <label for="updated_by" class="form-label">Updated By:</label>
                        <input type="number" name="updated_by" class="form-control" value="{{ $transportation->updated_by }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Update</button>
                </form>
            </div>
        </div>
    </div>

</section>
</main>
@endsection
