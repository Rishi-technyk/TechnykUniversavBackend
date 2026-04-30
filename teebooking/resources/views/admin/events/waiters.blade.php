@extends('layouts.admin')

@section('content')

<main id="main" class="main">
<section class="section dashboard">
<div class="container">

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <h4 class="mb-0">
            👨‍🍳 {{ $event->name }} - Waiter Settings
        </h4>

        {{-- Event Dropdown --}}
        <select class="form-select form-select-sm"
                style="width:220px"
                onchange="changeEvent(this.value)">

            @foreach($events as $ev)
                <option value="{{ $ev->id }}"
                    {{ $event->id == $ev->id ? 'selected' : '' }}>
                    {{ $ev->name }}
                </option>
            @endforeach
        </select>
    </div>

    <a href="{{ route('admin.events') }}"
       class="btn btn-secondary">
       Back
    </a>
</div>

{{-- WAITER CONFIG CARD --}}
<div class="card shadow-sm">
<div class="card-header bg-dark text-white">
    Waiter Configuration
</div>

<div class="card-body">

<form action="{{ route('admin.events.waiters.update', $event->id) }}"
      method="POST">
    @csrf
    @method('POST')

<div class="row">

    <div class="col-md-4 mb-3">
        <label class="form-label">Max Waiters (Event)</label>
        <input type="number"
               name="max_waiters"
               value="{{ $waiter->max_waiters }}"
               class="form-control"
               min="0"
               required>
        <small class="text-muted">
            Total waiters allowed for this event
        </small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Max Waiters Per Member</label>
        <input type="number"
               name="max_waiters_per_member"
               value="{{ $waiter->max_waiters_per_member }}"
               class="form-control"
               min="0"
               required>
        <small class="text-muted">
            Maximum waiters a member can book
        </small>
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">Waiter Cost (₹)</label>
        <input type="number"
               step="0.01"
               name="waiter_cost"
               value="{{ $waiter->waiter_cost }}"
               class="form-control"
               required>
        <small class="text-muted">
            Cost per waiter
        </small>
    </div>

</div>

<div class="row align-items-center mt-3">

    <div class="col-md-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select">
            <option value="active"
                {{ $waiter->status == 'active' ? 'selected' : '' }}>
                Active
            </option>
            <option value="inactive"
                {{ $waiter->status == 'inactive' ? 'selected' : '' }}>
                Inactive
            </option>
        </select>
    </div>

    <div class="col-md-3 mt-4">
     <button type="submit" class="btn btn-success px-4">
    <i class="bi bi-check-circle me-1"></i> Save Settings
</button>
    </div>

</div>

</form>

</div>
</div>

</div>
</section>
</main>

@push('js')
<script>
function changeEvent(eventId) {
    if (!eventId) return;

    window.location.href =
        "{{ url('admin/events') }}/" + eventId + "/waiters";
}
</script>
@endpush

@endsection