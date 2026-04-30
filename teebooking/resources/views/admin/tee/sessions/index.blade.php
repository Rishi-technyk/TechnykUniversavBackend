@extends('layouts.admin')

@section('content')

    <main id="main" class="main">
        <section class="section dashboard">

        <div class="container ">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Sessions</h2>
                <a href="{{ route('sessions.create') }}" class="btn btn-primary">Add Session</a>
            </div>
      

        <div class="row mt-3">
            <div class="col-md-12">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Session Name</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Categories</th>
                            <th>Is Active</th>
                        
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sessions as $session)
                            <tr>
                                <td>{{ $session->id }}</td>
                                <td>{{ $session->session_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($session->start_time)->format('g:iA') }}</td>
                                <td>{{ \Carbon\Carbon::parse($session->end_time)->format('g:iA') }}</td>
                                <td>
                                    @foreach ($session->sessionCategories as $category)
                                        @if ($category->categoryType)
                                            {{ $category->categoryType->CategoryType }}
                                            @if (!$loop->last)
                                                ,
                                            @endif
                                        @endif
                                    @endforeach
                                </td>
                                <!--<td>{{ $session->is_active ? 'Yes' : 'No' }}</td>-->
                                <td>
                                    <label class="switch switch-status">
                                        <input type="checkbox" class="status"
                                               id="{{ $session->id }}" {{$session->is_active == 1?'checked':''}}>
                                        <span class="slider round"></span>
                                    </label>
                                </td>
                                
                                <td>
                                    <a href="{{ route('sessions.edit', $session->id) }}" class="btn btn-primary">Edit</a>
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
            $('.datatable-input').attr('placeholder', 'Search Name, Time, Category');
    $('.datatable-input').css('width', '300px');

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
                url: "{{route('sessions.status-update')}}",
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