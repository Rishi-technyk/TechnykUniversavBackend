@extends('layouts.admin')

@section('content')

    <main id="main" class="main">
        <section class="section dashboard">

            <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Sessions</h4>
                <a href="{{ route('tee-session-times.create') }}" class="btn btn-primary">Add Session</a>

            </div>
     
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table  datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Session Name</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Is Active</th>
                                        <!--<th>Created By</th>-->
                                        <!--<th>Updated By</th>-->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($teeSessionTimes as $sessionTime)
                                    <tr>
                                        <td>{{ $sessionTime->id }}</td>
                                        <td>{{ $sessionTime->sessionName->name }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sessionTime->start_time)->format('g:iA') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($sessionTime->end_time)->format('g:iA') }}</td>
                                        <td>
                                            <label class="switcher mx-auto">
                                                <input type="checkbox" class="status switcher_input"
                                                       id="{{$sessionTime->id}}" {{$sessionTime->id == 1 ? 'checked':''}}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        </td>
                                        <!--<td>{{ $sessionTime->created_by }}</td>-->
                                        <!--<td>{{ $sessionTime->updated_by }}</td>-->
                                        <td>
                                            <!--<a href="{{ route('tee-session-times.show', $sessionTime->id) }}" class="btn btn-info btn-sm">View</a>-->
                                            <a href="{{ route('tee-session-times.edit', $sessionTime->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                            <!-- Add delete functionality with a form and method spoofing -->
                                            <!--<form action="{{ route('tee-session-times.destroy', $sessionTime->id) }}" method="POST" style="display: inline;">-->
                                            <!--    @csrf-->
                                            <!--    @method('DELETE')-->
                                            <!--    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this session time?')">Delete</button>-->
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
        </section>
    </main>
@endsection