@extends('layouts.admin')

@section('content')

<main id="main" class="main">

<section class="section dashboard">
<div class="container">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<h4>
🎟 Create Seating Layout - {{ $event->name }}
</h4>

<a href="{{ route('admin.events.seating.index',$event->id) }}"
class="btn btn-secondary">
Back
</a>

</div>

{{-- FORM --}}
<div class="card shadow-sm">

<div class="card-header bg-dark text-white">
Seat Layout Configuration
</div>

<div class="card-body">

<form method="POST"
action="{{ route('admin.events.seating.store') }}">

@csrf

<input type="hidden"
name="event_id"
value="{{ $event->id }}">

<div class="row">

<div class="col-md-4 mb-3">
<label>Total Rows</label>
<input type="number"
name="rows"
class="form-control"
required>
</div>

<div class="col-md-4 mb-3">
<label>Seats Per Row</label>
<input type="number"
name="columns"
class="form-control"
required>
</div>

<div class="col-md-4 mb-3">
<label>Seat Category</label>

<select name="category_id"
class="form-control"
required>

@foreach($categories as $cat)

<option value="{{ $cat->id }}">
{{ $cat->name }} - ₹{{ $cat->price }}
</option>

@endforeach

</select>

</div>

</div>

<div class="alert alert-info mt-3">

Seats will be generated like:

<br><br>

A1 A2 A3 A4 A5  
B1 B2 B3 B4 B5  
C1 C2 C3 C4 C5  

</div>

<button class="btn btn-success mt-3">
Generate Seats
</button>

</form>

</div>

</div>

</div>
</section>

</main>

@endsection