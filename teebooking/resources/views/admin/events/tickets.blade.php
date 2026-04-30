@extends('layouts.admin')

@section('content')

<main id="main" class="main">
<section class="section dashboard">
<div class="container">

{{-- ================= HEADER ================= --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    
    <div class="d-flex align-items-center gap-3">
        
        <h4 class="mb-0">
            🎟 {{ $event->name }} - Ticket Dashboard
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

    <div>
        <a href="{{ route('admin.ticket.landing', $event->id) }}"
           class="btn btn-success">
           + Create Booking
        </a>

        <a href="{{ route('admin.events') }}"
           class="btn btn-secondary">
           Back
        </a>
    </div>

</div>

{{-- ================= SUMMARY CARDS ================= --}}
<div class="row mb-4">

<div class="col-md-4">
<div class="card shadow-sm border-0 pt-4">
<div class="card-body text-center">
    <h6>Total Tickets Sold</h6>
    <h3 class="text-success">{{ $totalSold }}</h3>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm border-0 pt-4">
<div class="card-body text-center">
    <h6>Total Complimentary</h6>
    <h3 class="text-warning">{{ $totalComp }}</h3>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm border-0 pt-4">
<div class="card-body text-center">
    <h6>Total Revenue</h6>
    <h3 class="text-primary">
       ₹  {{ number_format($totalRevenue,2) }}
    </h3>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card shadow-sm border-0 pt-4">
<div class="card-body text-center">
    <h6>Remaining Tickets</h6>
    <h3 class="text-danger">
        {{ $event->max_tickets - $totalSold }}
    </h3>
</div>
</div>
</div>

</div>
<div class="row mb-3">

<div class="col-md-3">
<div class="card text-center pt-4">
<div class="card-body">
<h6>Member</h6>
<h4>{{ $ticketBreakdown['member'] }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center pt-4">
<div class="card-body">
<h6>Spouse</h6>
<h4>{{ $ticketBreakdown['spouse'] }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center pt-4">
<div class="card-body">
<h6>Dependent</h6>
<h4>{{ $ticketBreakdown['dependent'] }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center pt-4">
<div class="card-body">
<h6>VIP</h6>
<h4>{{ $ticketBreakdown['vip'] }}</h4>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card text-center pt-4">
<div class="card-body">
<h6>Guest</h6>
<h4>{{ $ticketBreakdown['guest'] }}</h4>
</div>
</div>
</div>

</div>
{{-- ================= FILTER SECTION ================= --}}
<div class="card mb-3 shadow-sm">
<div class="card-body">

<form method="GET">
<div class="row  pt-4">

<div class="col-md-3">
    <input type="text"
           name="search"
           value="{{ request('search') }}"
           placeholder="Search Member/MemberID"
           class="form-control">
</div>

<div class="col-md-2">
        <select name="payment_status" class="form-control">
        <option value="">All Status</option>
        <option value="paid" {{ request('payment_status')=='paid'?'selected':'' }}>
            Paid
        </option>
        <option value="admin" {{ request('payment_status')=='admin'?'selected':'' }}>
            Admin
        </option>
        <option value="pending" {{ request('payment_status')=='pending'?'selected':'' }}>
            Pending
        </option>
    </select>
</div>

<div class="col-md-2">
    <input type="date"
           name="from_date"
           value="{{ request('from_date') }}"
           class="form-control">
</div>

<div class="col-md-2">
    <input type="date"
           name="to_date"
           value="{{ request('to_date') }}"
           class="form-control">
</div>

<div class="col-md-3">
    <button type="submit" class="btn btn-primary">
        Filter
    </button>

    <a href="{{ route('admin.events.tickets',$event->id) }}"
       class="btn btn-light">
       Reset
    </a>
    <a href="{{ route('admin.events.tickets.export', request()->all() + ['id' => $event->id]) }}"
   class="btn btn-success">
   Export Excel
</a>
</div>

</div>
</form>

</div>
</div>

{{-- ================= TICKET TABLE ================= --}}
<div class="card shadow-sm">
<div class="card-header bg-dark text-white">
    Tickets List
</div>

<div class="table-responsive p-3">
<table class="table table-bordered align-middle">
<thead class="table-dark">
<tr>
    <th>#</th>
    <th>Booking No</th>
      <th>Member ID</th>
      
    <th>Member</th>
    <th>Qty</th>
    <th>Waiters</th>
    <th>Total</th>
    <th>Status</th>
      <th>Seat No.</th>
    <th>Date</th>
    <th>Action</th>
</tr>
</thead>

<tbody>
@forelse($bookings as $booking)

<tr>
    <td>{{ $loop->iteration }}</td>

    <td>
        <strong>{{ $booking->booking_no }}</strong>
    </td>
     <td>
        {{ $booking->member->MemberId ?? 'Admin' }}
    </td>

    <td>
        {{ $booking->member->DisplayName ?? 'Admin' }}
    </td>
    

    <td>
        <span class="badge bg-info">
            {{ $booking->participants->count() }}
        </span>
    </td>
 <td>
        <span class="badge bg-info">
            {{ $booking->waiterBooking->quantity ?? 0 }}
        </span>
    </td>
    <td>
        {{ number_format($booking->total_amount,2) }}
    </td>

  <td>
    @if($booking->payment_status == 'paid')
        <span class="badge bg-success">
            Paid
        </span>

    @elseif($booking->payment_status == 'pending')
        <span class="badge bg-warning">
            Pending
        </span>

    @elseif($booking->payment_status == 'failed')
        <span class="badge bg-danger">
            Failed
        </span>

    @else
        <span class="badge bg-secondary">
            {{ ucfirst($booking->payment_status) }}
        </span>
    @endif
</td>
<td>
@foreach($booking->seat_codes as $seat)
    <span class="badge bg-danger">{{ $seat }}</span>
@endforeach
</td>
    <td>
        {{ $booking->created_at->format('d M Y') }}
    </td>

    <td>
        <button class="btn btn-sm btn-outline-primary"
                data-bs-toggle="collapse"
                data-bs-target="#participants{{ $booking->id }}">
            View
        </button>
    </td>
</tr>

{{-- Collapsible Participants --}}
<tr class="collapse"
    id="participants{{ $booking->id }}">
<td colspan="8" class="bg-light">

<div class="row">

@foreach($booking->participants as $participant)
<div class="col-md-3 mb-3">
<div class="card border-0 shadow-sm">
<div class="card-body p-2">

<strong>{{ $participant->name }}</strong><br>

<small class="text-muted">
{{ $participant->ticketType->type ?? 'N/A' }}
</small>

<hr class="my-1">

₹ {{ number_format($participant->amount,2) }}

<br>

@if($participant->entry_status)
    <span class="badge bg-success">Entered</span>
@endif

@if($participant->food_status)
    <span class="badge bg-warning text-dark">Food Taken</span>
@endif

</div>
</div>
</div>
@endforeach

</div>

</td>
</tr>

@empty
<tr>
<td colspan="8" class="text-center py-4">
    No Tickets Booked Yet
</td>
</tr>
@endforelse
</tbody>
</table>

{{-- Pagination --}}
<div class="d-flex ">
    {{ $bookings->withQueryString()->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>

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
        "{{ url('admin/events') }}/" + eventId + "/tickets";

}
</script>
@endpush
@endsection