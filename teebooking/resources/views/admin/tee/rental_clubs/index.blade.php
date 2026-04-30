
    <div class="d-flex justify-content-between align-items-center">
        <h2 class="mb-0">Rental Clubs</h2>
        <a href="{{ route('rental_clubs.create') }}" class="btn btn-primary">Create Rental Club</a>

    </div>

    <div class="container mt-4">
        <div class="row mt-3">
            <div class="col-md-12s">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Is Active</th>
                            <th>Created By</th>
                            <th>Updated By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rentalClubs as $rentalClub)
                        <tr>
                            <td>{{ $rentalClub->id }}</td>
                            <td>{{ $rentalClub->name }}</td>
                            <td>{{ $rentalClub->is_active ? 'Yes' : 'No' }}</td>
                            <td>{{ $rentalClub->created_by }}</td>
                            <td>{{ $rentalClub->updated_by }}</td>
                            <td>
                                <a href="{{ route('rental_clubs.edit', $rentalClub->id) }}"
                                    class="btn btn-primary">Edit</a>
                                <form action="{{ route('rental_clubs.destroy', $rentalClub->id) }}" method="POST"
                                    style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this rental club?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        </div>
  