

    <div class="container">
    <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Session Name</h4>
                <a href="{{ route('session_names.create') }}" class="btn btn-primary">Add Session Name</a>

            </div>
     
       
        <div class="row mt-3">
            <div class="col-md-12">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Is Active</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessionNames as $sessionName)
                            <tr>
                                <td>{{ $sessionName->id }}</td>
                                <td>{{ $sessionName->name }}</td>
                                <td>{{ $sessionName->is_active ? 'Yes' : 'No' }}</td>
                                <td>
                                    <a href="{{ route('session_names.edit', $sessionName->id) }}" class="btn btn-primary">Edit</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

