

        <div class="container ">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Caddies</h4>
                <a href="{{ route('caddies.create') }}" class="btn btn-primary">Create Caddy</a>
            </div>
            <!-- resources/views/caddies/index.blade.php -->


        <div class="row mt-3">
            <div class="col-md-12">
                <table class="table  datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Is Active</th>
                            <!--<th>Created By</th>-->
                            <!--<th>Updated By</th>-->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($caddies as $caddy)
                            <tr>
                                <td>{{ $caddy->id }}</td>
                                <td>{{ $caddy->name }}</td>
                                <td>{{ $caddy->is_active ? 'Yes' : 'No' }}</td>
                                <!--<td>{{ $caddy->created_by }}</td>-->
                                <!--<td>{{ $caddy->updated_by }}</td>-->
                                <td>
                                    <a href="{{ route('caddies.edit', $caddy->id) }}" class="btn btn-primary">Edit</a>
                                    <!--<form action="{{ route('caddies.destroy', $caddy->id) }}" method="POST" style="display: inline;">-->
                                    <!--    @csrf-->
                                    <!--    @method('DELETE')-->
                                    <!--    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this caddy?')">Delete</button>-->
                                    <!--</form>-->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>

