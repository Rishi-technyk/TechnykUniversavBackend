@extends('layouts.web')
@section('content')
<style>
.timeline-container .timeline-list .inactive {
    background-color: #d5d5d5;
}

.is-available {
    background-color: #ccecc2;
}

.is-booked {
    background-color: #fca6a6;
}
</style>

{{-- .sidebar --}}

@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-reservation">
      Card Recharge
        
    </div>
</div>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection