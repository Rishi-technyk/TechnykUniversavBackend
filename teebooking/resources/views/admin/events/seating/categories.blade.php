@extends('layouts.admin')

@section('content')

<main id="main" class="main">

<section class="section dashboard">
<div class="container">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">

<h4>
🎟 {{ $event->name }} - Seating Categories
</h4>

<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#addCategory">
+ Add Category
</button>

</div>


{{-- TABLE --}}
<div class="card shadow-sm">

<div class="card-header bg-dark text-white">
Seat Categories
</div>

<div class="table-responsive p-3">

<table class="table table-bordered">

<thead class="table-dark">

<tr>

<th>#</th>
<th>Name</th>
<th>Seat Type</th>
<th>Rows</th>
<th>Price</th>
<th>Color</th>
<th>Max Booking</th>
<th>Status</th>

</tr>

</thead>

<tbody>

@forelse($categories as $cat)

<tr>

<td>{{ $loop->iteration }}</td>

<td>
<strong>{{ $cat->name }}</strong>
<br>
<small>{{ $cat->description }}</small>
</td>

<td>
<span class="badge bg-info">
{{ ucfirst($cat->seat_type) }}
</span>
</td>

<td>

{{ $cat->start_row }} - {{ $cat->end_row }}

</td>

<td>

₹ {{ number_format($cat->price,2) }}

</td>

<td>

<span style="
display:inline-block;
width:25px;
height:25px;
background:{{ $cat->color }};
border-radius:4px;
"></span>

</td>

<td>

{{ $cat->max_per_booking }}

</td>

<td>

@if($cat->status=='active')

<span class="badge bg-success">Active</span>

@else

<span class="badge bg-danger">Inactive</span>

@endif

</td>

</tr>

@empty

<tr>
<td colspan="8" class="text-center">
No Categories Created
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

</div>

</div>
</section>
</main>


{{-- ADD CATEGORY MODAL --}}
<div class="modal fade" id="addCategory">

<div class="modal-dialog modal-lg">

<div class="modal-content">

<form method="POST"
action="{{ route('admin.events.seating.categories.store') }}">

@csrf

<div class="modal-header">

<h5>Add Seat Category</h5>

<button type="button"
class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<input type="hidden"
name="event_id"
value="{{ $event->id }}">

<div class="row">

<div class="col-md-4 mb-3">

<label>Name</label>

<input type="text"
name="name"
class="form-control"
required>

</div>


<div class="col-md-4 mb-3">

<label>Seat Type</label>

<select name="seat_type"
class="form-control">
    <option value="vip">VIP</option>
<option value="complimentary">Complimentary</option>
<option value="regular">Regular</option>



</select>

</div>


<div class="col-md-4 mb-3">

<label>Price</label>

<input type="number"
name="price"
class="form-control">

</div>


<div class="col-md-4 mb-3">

<label>Start Row</label>

<input type="text"
name="start_row"
class="form-control">

</div>


<div class="col-md-4 mb-3">

<label>End Row</label>

<input type="text"
name="end_row"
class="form-control">

</div>


<div class="col-md-4 mb-3">

<label>Max Per Booking</label>

<input type="number"
name="max_per_booking"
value="10"
class="form-control">

</div>


<div class="col-md-4 mb-3">

<label>Color</label>

<input type="color"
name="color"
class="form-control">

</div>


<div class="col-md-4 mb-3">

<label>Status</label>

<select name="status"
class="form-control">

<option value="active">Active</option>
<option value="inactive">Inactive</option>

</select>

</div>


<div class="col-md-12">

<label>Description</label>

<textarea name="description"
class="form-control"></textarea>

</div>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-success">
Save Category
</button>

</div>

</form>

</div>

</div>

</div>

@endsection