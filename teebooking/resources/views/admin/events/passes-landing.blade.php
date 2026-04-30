@extends('layouts.admin')

@section('content')

<main class="main">
<section class="section dashboard">
<div class="container">

    <h4>Select Event To Manage Passes</h4>

</div>
</section>
</main>

<!-- Event Select Modal -->
<div class="modal fade" id="eventSelectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">

      <div class="modal-header">
        <h5 class="modal-title">Select Event</h5>
      </div>

      <div class="modal-body">

        <select id="eventDropdown" class="form-control">
            <option value="">Choose Event</option>
            @foreach($events as $event)
                <option value="{{ $event->id }}">
                    {{ $event->name }}
                </option>
            @endforeach
        </select>

      </div>

      <div class="modal-footer">
        <button type="button"
                class="btn btn-primary w-100"
                onclick="goToPasses()">
            View Passes
        </button>
      </div>

    </div>
  </div>
</div>

@push('js')
<script>
document.addEventListener("DOMContentLoaded", function() {

    // Auto open modal on page load
    let modal = new bootstrap.Modal(
        document.getElementById('eventSelectModal')
    );
    modal.show();

});

function goToPasses() {

    let eventId = document.getElementById('eventDropdown').value;

    if (!eventId) {
        alert('Please select an event');
        return;
    }

    window.location.href =
        "/member/event/admin/events/" + eventId + "/passes";
}
</script>
@endpush

@endsection