<div class="container ">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Transportation</h4>
        <a href="{{ route('transportations.create') }}" class="btn btn-primary">Add Transportation</a>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <table class="table datatable">
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
                    @foreach ($transportations as $transportation)
                    <tr>
                        <td>{{ $transportation->id }}</td>
                        <td>{{ $transportation->name }}</td>
                        <td>{{ $transportation->is_active ? 'Yes' : 'No' }}</td>
                        <!--<td>{{ $transportation->created_by }}</td>-->
                        <!--<td>{{ $transportation->updated_by }}</td>-->
                        <td>
                            <a href="{{ route('transportations.edit', $transportation->id) }}"
                                class="btn btn-primary">Edit</a>
                            <!--<form action="{{ route('transportations.destroy', $transportation->id) }}"-->
                            <!--    method="POST" style="display: inline;">-->
                            <!--    @csrf-->
                            <!--    @method('DELETE')-->
                            <!--    <button type="submit" class="btn btn-danger"-->
                            <!--        onclick="return confirm('Are you sure you want to delete this transportation?')">Delete</button>-->
                            <!--</form>-->
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>