@extends('layouts.admin')

@section('content')

    <main id="main" class="main">
        <section class="section dashboard">


    <!-- resources/views/tee_holes/index.blade.php -->

            <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tee Holes</h4>
                <a href="{{ route('tee_holes.create') }}" class="btn btn-primary">Add Tee Hole</a>
        
            </div>
        
            <div class="row mt-3">
                <div class="col-md-12">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <!-- <th>ID</th> -->
                                    <th>Hole Number</th>
                                    <th>Is Active</th>
                                    <!--<th>Created By</th>-->
                                    <!--<th>Updated By</th>-->
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($teeHoles as $teeHole)
                                    <tr>
                                        <!-- <td>{{ $teeHole->id }}</td> -->
                                        <td>{{ $teeHole->hole_number }}</td>
                                        <!--<td>{{ $teeHole->is_active ? 'Yes' : 'No' }}</td>-->
                                        <td>
                                            <label class="switch switch-status">
                                                <input type="checkbox" class="status"
                                                       id="{{ $teeHole->id }}" {{$teeHole->is_active == 1?'checked':''}}>
                                                <span class="slider round"></span>
                                            </label>
                                        </td>
                                        <!--<td>{{ $teeHole->created_by }}</td>-->
                                        <!--<td>{{ $teeHole->updated_by }}</td>-->
                                        <td>
                                            <a href="{{ route('tee_holes.edit', $teeHole->id) }}" class="btn btn-primary">Edit</a>
                                            <!--<form action="{{ route('tee_holes.destroy', $teeHole->id) }}" method="POST" style="display: inline;">-->
                                            <!--    @csrf-->
                                            <!--    @method('DELETE')-->
                                            <!--    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this tee hole?')">Delete</button>-->
                                            <!--</form>-->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <!-- Include JavaScript to handle dynamic addition of session fields -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
          $(document).ready(function(){
            $('.datatable-input').attr('placeholder', 'Search Hole Number');
    $('.datatable-input').css('width', '230px');

        });
        $(document).on('change', '.status', function () {
            var id = $(this).attr("id");
            if ($(this).prop("checked") == true) {
                var status = 1;
            } else if ($(this).prop("checked") == false) {
                var status = 0;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                }
            });
            $.ajax({
                url: "{{route('tee_holes.status-update')}}",
                method: 'POST',
                data: {
                    id: id,
                    status: status
                },
                success: function (data) {
                    toastr.success('Status updated successfully');
                }
            });
        });
    </script>
@endsection
