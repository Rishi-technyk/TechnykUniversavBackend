@extends('layouts.admin')

@section('content')

<main id="main" class="main">
<section class="section dashboard">
<div class="container">

<div class="d-flex justify-content-between align-items-center mb-3">
    <h6>Total Events ({{ $events->total() }})</h6>
    <a href="#" onclick="toggleEventForm()" class="btn btn-success">
        <i class="bi bi-plus"></i> Add Event
    </a>
</div>

{{-- Create Event Form --}}
<div id="eventForm" style="display:none;">
<div class="card mb-3">
<div class="card-header">
    <h5>Create Event</h5>
</div>
<div class="card-body">

<form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data">
@csrf

<div class="row">
<div class="col-md-6 mb-3">
    <label>Event Name</label>
    <input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
    <label>Location</label>
    <input type="text" name="location" class="form-control">
</div>
</div>

<div class="row">
<div class="col-md-4 mb-3">
    <label>Event Date</label>
    <input type="datetime-local" name="event_date" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
    <label>Booking Start</label>
    <input type="datetime-local" name="booking_start_at" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
    <label>Booking End</label>
    <input type="datetime-local" name="booking_end_at" class="form-control" required>
</div>
</div>

<div class="row">
<div class="col-md-4 mb-3">
    <label>Max Tickets</label>
    <input type="number" name="max_tickets" class="form-control" required>
</div>

<div class="col-md-4 mb-3">
    <label>Max Per Member</label>
    <input type="number" name="max_per_member_tickets" class="form-control" required>
</div>
<div class="col-md-4 mb-3">
    <label>Complimentary Age</label>
    <input type="number" name="complimentary_age" class="form-control" required>
</div>
<div class="col-md-2 mb-3">
    <label>GST %</label>
    <input type="number" name="gst" class="form-control">
</div>

<div class="col-md-2 mb-3">
    <label>Service %</label>
    <input type="number" name="service_charge" class="form-control">
</div>
</div>

<div class="mb-3">
<label>Event Image</label>
<input type="file" name="image" class="form-control" required>
</div>

<button type="submit" class="btn btn-success">Create Event</button>

</form>
</div>
</div>
</div>

{{-- Event Table --}}
<div class="card">
<div class="card-header bg-dark text-white">
    Events List
</div>

<div class="table-responsive p-3">
<table class="table table-bordered">
<thead>
<tr>
    <th>ID</th>
    <th>Name</th>
    <th>Image</th>
    <th>Event Date</th>
    <th>Booking Window</th>
    <th>Max Tickets</th>
    <th>Max/Member</th>
    <th>Comp. Age</th>
    <th>Status</th>
    <th>Action</th>
   <th>Tickets</th>
</tr>
</thead>

<tbody>
@foreach($events as $event)
<tr>
<td>{{ $event->id }}</td>
<td>{{ $event->name }}</td>
<td>
    <img src="{{ asset('public/event/'.$event->image) }}" width="80">
</td>
<td>{{ $event->event_date }}</td>
<td>
    {{ $event->booking_start_at }} <br>
    to <br>
    {{ $event->booking_end_at }}
</td>
<td>{{ $event->max_tickets }}</td>

  
<td>{{ $event->max_per_member_tickets }}</td>
<td>{{ $event->complimentary_age }}</td>

<td>
<label class="switch">
<input type="checkbox" class="event-status"
    data-id="{{ $event->id }}"
    {{ $event->status == 'active' ? 'checked' : '' }}>
<span class="slider round"></span>
</label>
</td>
<td>
    <a href="{{ route('admin.events.edit',$event->id) }}" class="btn btn-sm btn-primary">
        <i class="bi bi-pencil"></i>
    </a>

    <form action="{{ route('admin.events.delete',$event->id) }}" 
          method="POST" 
          style="display:inline-block;"
          onsubmit="return confirm('Are you sure you want to delete this event?')">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</td>
<td>
    <a href="{{ route('admin.events.tickets', $event->id) }}"
       class="btn btn-sm btn-info">
       🎟 View Tickets
    </a>
</td>
</tr>
@endforeach
</tbody>
</table>

{{ $events->links() }}

</div>
</div>

</div>
</section>
</main>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
@stack('js')
<script>
function toggleEventForm() {
    let form = document.getElementById('eventForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}

$('.event-status').change(function() {
    let id = $(this).data('id');
    let status = $(this).prop('checked') ? 'active' : 'closed';

    $.post("{{ route('admin.events.status') }}", {
        _token: "{{ csrf_token() }}",
        id: id,
        status: status
    });
});
</script>

@endsection