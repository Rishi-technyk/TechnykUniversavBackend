@extends('layouts.admin')

@section('content')
<style>
    th{
        color:black;
    }
    .table thead tr th, .table tbody tr td {
        font-size: .90rem;
        text-transform: none;
        padding: 0.60rem;
    }
</style>
    <main id="main" class="main">

        <section class="section dashboard">


    <div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Tee Slot Intervals</h4>
                <a href="{{ route('tee_slot_intervals.create') }}" class="btn btn-primary">Add Tee Slot Interval</a>
            </div>
      
       
        <div class="row mt-3">
            <div class="col-md-12">
                <table class="table datatable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tee Sheet</th>
                            <th>Session Name</th>
                            <th>Session Time</th>
                            <th>Slot Interval</th>
                            <th>Tee Off Hole</th>
                            <!--<th>Is Active</th>-->
                            <!--<th>Created At</th>-->
                            <!--<th>Created By</th>-->
                            <!--<th>Updated At</th>-->
                            <!--<th>Updated By</th>-->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($teeSlotIntervals as $teeSlotInterval)
                            <tr>
                                <td>{{ $teeSlotInterval->id }}</td>
                                <td>{{ $teeSlotInterval->tee_sheet_id }}</td>
                                <td>{{ $teeSlotInterval->session_name_id }}</td>
                                <td>{{ $teeSlotInterval->session_time_id }}</td>
                                <td>{{ $teeSlotInterval->slot_interval }}</td>
                                <td>{{ $teeSlotInterval->tee_off_hole }}</td>
                                <!--<td>{{ $teeSlotInterval->is_active ? 'Yes' : 'No' }}</td>-->
                                <!--<td>{{ $teeSlotInterval->created_at }}</td>-->
                                <!--<td>{{ $teeSlotInterval->created_by }}</td>-->
                                <!--<td>{{ $teeSlotInterval->updated_at }}</td>-->
                                <!--<td>{{ $teeSlotInterval->updated_by }}</td>-->
                                <td>
                                    <a href="{{ route('tee_slot_intervals.edit', $teeSlotInterval->id) }}" class="btn btn-primary">Edit</a>
                                    <!--<form action="{{ route('tee_slot_intervals.destroy', $teeSlotInterval->id) }}" method="POST" style="display: inline;">-->
                                    <!--    @csrf-->
                                    <!--    @method('DELETE')-->
                                    <!--    <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this tee slot interval?')">Delete</button>-->
                                    <!--</form>-->
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
