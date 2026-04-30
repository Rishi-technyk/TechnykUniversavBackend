@extends('layouts.admin')

@section('content')

<main id="main"class="main">
<section class="section dashboard">

<div class="container">

<div class="d-flex justify-content-between mb-3">

<h4>👨‍💼 Admin / Event Staff</h4>

<button class="btn btn-primary"
data-bs-toggle="modal"
data-bs-target="#addStaff">

+ Add Staff

</button>

</div>
<h6>Password will be 'Temp@1234' by default</h6>

<div class="card shadow-sm">

<div class="card-header bg-dark text-white">
Staff List
</div>

<div class="table-responsive p-3">

<table class="table table-bordered">

<thead class="table-dark">

<tr>
<th>#</th>
<th>Member ID</th>
<th>Name</th>
<th>Mobile</th>
<th>Role</th>
<th>Action</th>
</tr>

</thead>

<tbody>

@foreach($users as $user)

<tr>

<td>{{ $loop->iteration }}</td>

<td>{{ $user->MemberID }}</td>

<td>{{ $user->DisplayName }}</td>

<td>{{ $user->Mobile }}</td>

<td>
<span class="badge bg-info">
{{ $user->role }}
</span>
</td>


<td>

<button
class="btn btn-sm btn-warning"
data-bs-toggle="modal"
data-bs-target="#editStaff{{ $user->id }}">

Edit

</button>

</td>

</tr>


{{-- EDIT MODAL --}}

<div class="modal fade"
id="editStaff{{ $user->id }}">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST"
action="{{ route('admin.events.staff.update',$user->id) }}">

@csrf

<div class="modal-header">

<h5>Edit Staff</h5>

<button class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Name</label>

<input type="text"
name="DisplayName"
class="form-control"
value="{{ $user->DisplayName }}">

</div>

<div class="mb-3">

<label>Mobile</label>

<input type="text"
name="Mobile"
class="form-control"
value="{{ $user->Mobile }}">

</div>

<div class="mb-3">

<label>Role</label>

<select name="role" class="form-control">

<option value="Event Admin" {{ $user->role=='Event Admin'?'selected':'' }}>Event Admin</option>
<option value="EventEntry" {{ $user->role=='EventEntry'?'selected':'' }}>Event Entry</option>
<option value="EventFood" {{ $user->role=='EventFood'?'selected':'' }}>Event Food</option>
<option value="EventDrinks" {{ $user->role=='EventDrinks'?'selected':'' }}>Event Drinks</option>

</select>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-success">
Update
</button>

</div>

</form>

</div>
</div>
</div>

@endforeach

</tbody>

</table>

</div>

</div>

</div>

</section>
</main>


{{-- ADD STAFF MODAL --}}

<div class="modal fade" id="addStaff">

<div class="modal-dialog">

<div class="modal-content">

<form method="POST"
action="{{ route('admin.events.staff.store') }}">

@csrf

<div class="modal-header">

<h5>Add Staff</h5>

<button class="btn-close"
data-bs-dismiss="modal"></button>

</div>

<div class="modal-body">

<div class="mb-3">

<label>Member ID</label>

<input type="text"
name="MemberID"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Name</label>

<input type="text"
name="DisplayName"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Mobile</label>

<input type="text"
name="Mobile"
class="form-control">

</div>

<div class="mb-3">

<label>Role</label>

<select name="role" class="form-control">

<option value="Event Admin">Event Admin</option>
<option value="EventEntry">Event Entry</option>
<option value="EventFood">Event Food</option>
<option value="EventDrinks">Event Drinks</option>

</select>

</div>

</div>

<div class="modal-footer">

<button class="btn btn-primary">
Save
</button>

</div>

</form>

</div>
</div>
</div>

@endsection