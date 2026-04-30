@extends('layouts.admin')

@section('content')
<main class="main">
<div class="container">

<div class="card">
<div class="card-header">
    <h5>Edit Event</h5>
</div>
<div class="card-body">

<form action="{{ route('admin.events.update',$event->id) }}" 
      method="POST" 
      enctype="multipart/form-data">
@csrf

<div class="row">
<div class="col-md-6 mb-3">
    <label>Event Name</label>
    <input type="text" name="name" value="{{ $event->name }}" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
    <label>Location</label>
    <input type="text" name="location" value="{{ $event->location }}" class="form-control">
</div>
</div>

<div class="row">
<div class="col-md-4 mb-3">
    <label>Event Date</label>
    <input type="datetime-local" name="event_date"
        value="{{ \Carbon\Carbon::parse($event->event_date)->format('Y-m-d\TH:i') }}"
        class="form-control">
</div>

<div class="col-md-4 mb-3">
    <label>Booking Start</label>
    <input type="datetime-local" name="booking_start_at"
        value="{{ \Carbon\Carbon::parse($event->booking_start_at)->format('Y-m-d\TH:i') }}"
        class="form-control">
</div>

<div class="col-md-4 mb-3">
    <label>Booking End</label>
    <input type="datetime-local" name="booking_end_at"
        value="{{ \Carbon\Carbon::parse($event->booking_end_at)->format('Y-m-d\TH:i') }}"
        class="form-control">
</div>
</div>

<div class="row">
<div class="col-md-4 mb-3">
    <label>Max Tickets</label>
    <input type="number" name="max_tickets" value="{{ $event->max_tickets }}" class="form-control">
</div>

<div class="col-md-4 mb-3">
    <label>Max Per Member</label>
    <input type="number" name="max_per_member_tickets" value="{{ $event->max_per_member_tickets }}" class="form-control">
</div>
<div class="col-md-4 mb-3">
    <label>Complimentary Age</label>
    <input type="number" name="complimentary_age" value="{{ $event->complimentary_age }}" class="form-control">
</div>
<div class="col-md-2 mb-3">
    <label>GST %</label>
    <input type="number" name="gst" value="{{ $event->gst }}" class="form-control">
</div>

<div class="col-md-2 mb-3">
    <label>Service %</label>
    <input type="number" name="service_charge" value="{{ $event->service_charge }}" class="form-control">
</div>
</div>

<div class="mb-3">
<label>Current Image</label><br>
<img src="{{ asset('public/event/'.$event->image) }}" width="120">
</div>

<div class="mb-3">
<label>Replace Image</label>
<input type="file" name="image" class="form-control">
</div>

<button type="submit" class="btn btn-success">Update Event</button>

</form>
</div>
</div>

</div>
</main>
@endsection