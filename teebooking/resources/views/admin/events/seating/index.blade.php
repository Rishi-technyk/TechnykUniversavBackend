@extends('layouts.admin')

@section('content')

<main id="main" class="main">

<section class="section dashboard">
<div class="container">

<ul class="nav nav-tabs mb-3">

<li class="nav-item">
<a class="nav-link {{ request()->routeIs('admin.events.seating.index') ? 'active' : '' }}"
   href="{{ route('admin.events.seating.index',$event->id) }}">
Seat Layout
</a>
</li>

<li class="nav-item">
<a class="nav-link {{ request()->routeIs('admin.events.seating.categories') ? 'active' : '' }}"
   href="{{ route('admin.events.seating.categories',$event->id) }}">
Seating Categories
</a>
</li>

</ul>
{{-- ================= HEADER ================= --}}
<div class="d-flex justify-content-between align-items-center mb-4">

    <div class="d-flex align-items-center gap-3">

        <h4 class="mb-0">
            {{ $event->name }} - Seat Layout
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
    <a href="{{ route('admin.events.seating.create', $event->id) }}"
       class="btn btn-primary">
       + Create Seating
    </a>

    <a href="{{ route('admin.events') }}"
       class="btn btn-secondary">
       Back
    </a>
</div>
   

</div>

@php
$allSeats = collect();

foreach($layouts as $layout){
    $allSeats = $allSeats->merge($layout->seats);
}
@endphp
@php

$categoryBookings = [];

foreach($categories as $cat){

    $categoryBookings[$cat->id] = $allSeats
        ->where('category_id',$cat->id)
        ->where('status','booked')
        ->count();

}

@endphp



{{-- ================= SUMMARY ================= --}}
<div class="row mb-4">

<div class="col-md-3">
<div class="card shadow-sm border-0 pt-3">
<div class="card-body text-center">
<h6>Total Seats</h6>
<h3 class="text-primary">{{ $allSeats->count() }}</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0 pt-3">
<div class="card-body text-center">
<h6>Booked Seats</h6>
<h3 class="text-danger">
{{ $allSeats->where('status','booked')->count() }}
</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0 pt-3">
<div class="card-body text-center">
<h6>Available Seats</h6>
<h3 class="text-success">
{{ $allSeats->where('status','available')->count() }}
</h3>
</div>
</div>
</div>

<div class="col-md-3">
<div class="card shadow-sm border-0 pt-3">
<div class="card-body text-center">
<h6>Blocked Seats</h6>
<h3 class="text-warning">
{{ $allSeats->where('status','blocked')->count() }}
</h3>
</div>
</div>
</div>
<div class="row">

@foreach($categories as $cat)

<div class="col-md-3 mb-3 ">

<div class="card border">

<div class="card-body text-center pt-4">

<div style="
width:25px;

height:25px;
background:{{ $cat->color }};
margin:auto;
border-radius:4px;
"></div>

<h6 class="mt-2 ">
{{ $cat->name }}
</h6>

<h4 class="text-danger">

{{ $categoryBookings[$cat->id] }}

</h4>

</div>

</div>

</div>

@endforeach

</div>

</div>

{{-- ================= CATEGORY LEGEND ================= --}}
<div class="card shadow-sm mb-4">
<div class="card-body">

<strong>Seat Categories</strong>

<div class="mt-2 d-flex gap-4 flex-wrap">

@foreach($categories as $cat)

<div class="d-flex align-items-center gap-2">

<div style="
width:20px;
height:20px;
background:{{ $cat->color }};
border-radius:4px;
"></div>

<span>
{{ $cat->name }}
({{ number_format($cat->price,2) }})
</span>

</div>

@endforeach
<div style="
width:20px;
height:20px;
background:#f0f0f0;
border-radius:4px;
"></div>

<span>
Occupied
</span>

<div style="
width:20px;
height:20px;
background:#a6a5a4;
border-radius:4px;
"></div>

<span>
Blocked
</span>
</div>

</div>
</div>




</div>
{{-- ================= SEAT LAYOUT ================= --}}
@foreach($layouts as $layout)

<div class="card shadow-sm mb-4  align-items-center">
    <!--<div class="card-header">-->
    <!--    <strong>Layout {{ $layout->id }}</strong>-->
    <!--</div>-->

    <div class="card-body pt-4">

       @php
$rows = $layout->total_rows;
$cols = $layout->total_columns;
@endphp

@php
$groupedRows = $layout->seats->groupBy('row_label');
@endphp

@foreach($groupedRows as $rowLabel => $rowSeats)

<div class="d-flex mb-2">

<div style="width:30px;font-weight:bold">
{{ $rowLabel }}
</div>

@foreach($rowSeats as $seat)

@php
$color = $seat->category->color ?? '#ccc';

if($seat->status == 'booked'){
    $color = '#f0f0f0'; // red
}elseif($seat->status == 'blocked'){
    $color = '#a6a5a4'; // flat gray
}
@endphp

<div
class="seat-box"
data-id="{{ $seat->id }}"
data-status="{{ $seat->status }}"
style="
width:30px;
height:30px;
background:{{ $color }};
margin-right:5px;
text-align:center;
line-height:30px;
border-radius:4px;
font-size:12px;
cursor:pointer;
">
{{ $seat->seat_number }}
</div>
@endforeach

</div>

@endforeach

    </div>
</div>

@endforeach

<!-- Booking Details Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<div class="modal-header">
<h5 class="modal-title">Seat Booking Details</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body" id="bookingDetails">

<div class="text-center py-4">
Loading booking details...
</div>

</div>

</div>
</div>
</div>
</div>
</section>
</main>

@push('js')

<script>
document.querySelectorAll('.seat-box').forEach(seat => {

seat.addEventListener('click', function(){

let seatId = this.dataset.id
let status = this.dataset.status

/**
 * BOOKED SEAT → OPEN MODAL
 */

if(status === 'booked'){

let modal = new bootstrap.Modal(
document.getElementById('bookingModal')
)

document.getElementById('bookingDetails').innerHTML =
"Loading booking details..."

modal.show()

fetch("{{ route('admin.seats.booking', ':id') }}".replace(':id', seatId))
.then(res => res.json())
.then(data => {

if(!data.status){
document.getElementById('bookingDetails').innerHTML =
"Booking not found"
return
}

let booking = data.booking

let seats = booking.seats
.map(s => s.seat.seat_code)
.join(', ')

document.getElementById('bookingDetails').innerHTML = `
<div class="row">

<div class="col-md-6">
<strong>Booking No:</strong><br>
${booking.booking_no}
</div>

<div class="col-md-6">
<strong>Member:</strong><br>
${booking.member?.DisplayName || 'N/A'}
</div>

<div class="col-md-6 mt-3">
<strong>Mobile:</strong><br>
${booking.member?.SC_ID || '-'}
</div>

<div class="col-md-6 mt-3">
<strong>Seats:</strong><br>
${seats}
</div>

<div class="col-md-6 mt-3">
<strong>Total Amount:</strong><br>
₹${booking.total_amount}
</div>

<div class="col-md-6 mt-3">
<strong>Payment Type:</strong><br>
${booking.payment_type}
</div>

</div>
`

})
return
}

/**
 * AVAILABLE/BLOCKED → TOGGLE
 */

if(!confirm('Change seat status?')) return

fetch("{{ route('admin.seats.toggle') }}",{

method:'POST',

headers:{
'X-CSRF-TOKEN':'{{ csrf_token() }}',
'Content-Type':'application/json'
},

body:JSON.stringify({
seat_id:seatId
})

})
.then(res => res.json())
.then(data => {

if(data.status){
location.reload()

if(data.new_status === 'blocked'){
this.style.background = '#a6a5a4'
}

if(data.new_status === 'available'){
this.style.background = '#28a745'
}

}

})

})

})
</script>
<script>

function changeEvent(eventId){

if(!eventId) return;

window.location.href =
"{{ url('admin/events') }}/"+eventId+"/seating";

}

</script>

@endpush

@endsection