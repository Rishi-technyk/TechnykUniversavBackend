@extends('layouts.admin')

@section('content')

<main id="main" class="main">
<section class="section dashboard">
<div class="container">
<div class="d-flex justify-content-between align-items-center mb-4">

    <h4>🎟 {{ $event->name }} - Passes</h4>

    <div class="d-flex gap-2 align-items-center">

        {{-- Event Dropdown --}}
        <select class="form-select"
                style="width:220px"
                onchange="changeEvent(this.value)">

            @foreach(\App\Models\Event::orderBy('event_date','desc')->get() as $ev)
                <option value="{{ $ev->id }}"
                    {{ $event->id == $ev->id ? 'selected' : '' }}>
                    {{ $ev->name }}
                </option>
            @endforeach

        </select>

        <button class="btn btn-success"
                onclick="toggleForm()">
            + Add Pass
        </button>

    </div>

</div>

{{-- Add Pass Form --}}
<div id="passForm" style="display:none;">
<div class="card mb-4">
<div class="card-body">

<form action="{{ route('admin.events.passes.store',$event->id) }}"
      method="POST"
      enctype="multipart/form-data">
@csrf

<div class="row">

<div class="col-md-3">
<label>Name</label>
<input type="text" name="name" class="form-control" required>
</div>

<div class="col-md-2">
<label>Type</label>
<select name="type" class="form-select" required>
    <option value="">Select Type</option>

    @foreach($types as $type)
        <option value="{{ $type }}">
            {{ ucfirst($type) }}
        </option>
    @endforeach
</select>
</div>

<div class="col-md-2">
<label>Amount</label>
<input type="number" step="0.01" name="amount" class="form-control" required>
</div>

<div class="col-md-2">
<label>Max / Member</label>
<input type="number" name="max_per_member" class="form-control" required>
</div>

<div class="col-md-3">
<label>Background Image</label>
<input type="file" name="image_background" class="form-control">
</div>

</div>

<button class="btn btn-primary mt-3">Save</button>

</form>

</div>
</div>
</div>

{{-- Passes Table --}}
<div class="card">
<div class="card-header bg-dark text-white">
    Passes List
</div>

<div class="table-responsive p-3">
<table class="table table-bordered align-middle">

<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Background Image</th>
    <th>Name</th>
    <th>Type</th>
    <th>Amount</th>
    <th>Max/Member</th>
    <th>Status</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
@foreach($passes as $pass)
<tr>

<td>{{ $loop->iteration }}</td>

<td>
@if($pass->image_background)
    <img src="{{ asset('public/passes/'.$pass->image_background) }}"
         width="60">
@endif
</td>

<td>{{ $pass->name }}</td>

<td>{{ $pass->type }}</td>

<td>₹ {{ number_format($pass->amount,2) }}</td>

<td>{{ $pass->max_per_member }}</td>

<td>
<label class="form-check form-switch">
  <input class="form-check-input pass-status"
         type="checkbox"
         data-id="{{ $pass->id }}"
         {{ $pass->status == 1 ? 'checked' : '' }}>
</label>
</td>

<td>

    <!-- Edit -->
 <button type="button"
        class="btn btn-primary btn-sm"
        onclick="openEditModal(
            {{ $pass->id }},
            '{{ $pass->name }}',
            {{ $pass->amount }},
            {{ $pass->max_per_member }}
        )">
    <i class="bi bi-pencil"></i>
</button>

    <!-- Delete -->
<form action="{{ route('admin.passes.delete',$pass->id) }}"
      method="POST"
      style="display:inline-block"
      onsubmit="return confirm('Delete this pass?')">
    @csrf

    <button type="submit" class="btn btn-danger btn-sm">
        <i class="bi bi-trash"></i>
    </button>
</form>

</td>

</tr>
@endforeach
</tbody>
</table>
<!-- Edit Pass Modal -->
<div class="modal fade" id="editPassModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <form id="editPassForm" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title">Edit Pass</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <div class="mb-3">
            <label>Name</label>
            <input type="text"
                   name="name"
                   id="edit_name"
                   class="form-control"
                   required>
          </div>

          <div class="mb-3">
            <label>Amount</label>
            <input type="number"
                   step="0.01"
                   name="amount"
                   id="edit_amount"
                   class="form-control"
                   required>
          </div>

          <div class="mb-3">
            <label>Max Per Member</label>
            <input type="number"
                   name="max_per_member"
                   id="edit_max"
                   class="form-control"
                   required>
          </div>

          <!-- TYPE FIELD REMOVED (NOT EDITABLE) -->

          <div class="mb-3">
            <label>Replace Image</label>
            <input type="file"
                   name="image_background"
                   class="form-control">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
        </div>

      </form>

    </div>
  </div>
</div>
{{ $passes->links() }}

</div>
</div>

</div>
</section>
</main>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
@stack('js')
<script>
function openEditModal(id, name, amount, max) {


    document.getElementById('edit_name').value = name;
    document.getElementById('edit_amount').value = amount;
    document.getElementById('edit_max').value = max;

    let form = document.getElementById('editPassForm');

    let urlTemplate = "{{ route('admin.passes.update', ':id') }}";
    form.action = urlTemplate.replace(':id', id);

    let modal = new bootstrap.Modal(
        document.getElementById('editPassModal')
    );
    modal.show();
}
</script>
@push('js')
<script>
document.addEventListener("DOMContentLoaded", function() {

    const urlParams = new URLSearchParams(window.location.search);

    if (urlParams.get('open') == 1) {
        let modal = new bootstrap.Modal(
            document.getElementById('editPassModal')
        );
        modal.show();
    }

});
</script>
<script>
console.log("Pass JS Loaded");

$(document).ready(function(){

 $(document).on('change', '.pass-status', function(){

        let checkbox = $(this);
        let id = checkbox.data('id');
        let status = checkbox.is(':checked') ? 1 : 0;

        console.log("Toggle clicked");
        console.log("ID:", id);
        console.log("Status:", status);

        $.ajax({
            url: "{{ route('admin.passes.status') }}",
            type: "POST",
            data: {
                id: id,
                status: status,
                _token: "{{ csrf_token() }}"
            },
            success: function(response){
                console.log("Server Response:", response);
                toastr.success("Status Updated");
            },
            error: function(xhr){
                console.log("Error:", xhr.responseText);
                checkbox.prop('checked', !checkbox.is(':checked'));
                toastr.error("Update Failed");
            }
        });

    });

});
</script>
<script>
function changeEvent(eventId) {
    window.location.href = "{{ url('admin/events') }}/" + eventId + "/passes";
}
</script>
@endpush
@push('js')
<script>

function changeEvent(eventId) {

    if (!eventId) return;

    if(confirm("Switch to selected event passes?")) {
        window.location.href =
            "{{ url('admin/events') }}/" + eventId + "/passes";
    }

}

</script>
@endpush

@push('js')
<script>
function toggleForm() {

    let form = document.getElementById('passForm');

    if (form.style.display === 'none' || form.style.display === '') {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }

}
</script>
@endpush
@endsection