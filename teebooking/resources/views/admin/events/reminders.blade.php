@extends('layouts.admin')

@section('content')
  <style>
        .btn-success {
            background-color: #012970 !important;
        }
    </style>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
  <main id="main" class="main">
        <section class="section dashboard">
     <div class="container">
  <div class="row">        
        <div>
            <div class="card-header" >
    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0"></h6>
                        <button     class="btn btn-success billpaymentremindertoall">
                           Send Notification to all
                        </button>
                    </div>
                     </div>
            <!-- Sent Users Table -->
              <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header" style="background-color:#23384c;">
    <div class="row">
        <div class="d-flex justify-content-between align-items-center w-100">
            <h2 style="font-size: 1.25rem; color: white; margin: 0;">
                Notification for Bill Payment
            </h2>
            <form class="d-flex flex-wrap gap-1 align-items-center" action="{{ url()->current() }}" method="GET">
    <!-- Radio buttons -->
    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="status" id="statusAll" value=""
            {{ request('status') == '' ? 'checked' : '' }}>
        <label class="form-check-label"style="color: white; for="statusAll">All</label>
    </div>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="status" id="statusPaid" value="Success"
            {{ request('status') == 'Success' ? 'checked' : '' }}>
        <label class="form-check-label"style="color: white; for="statusPaid">Paid</label>
    </div>

    <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="status" id="statusNotPaid" value="pending"
            {{ request('status') == 'pending' ? 'checked' : '' }}>
        <label class="form-check-label"style="color: white; for="statusNotPaid">Not Paid</label>
    </div>
<div class="form-check form-check-inline">
    <!-- Search by Member ID -->
    <input type="search" name="search" class="form-control" placeholder="Search by Member ID"
        value="{{ request('search') }}">
 </div>
    <!-- Submit button -->
    <button type="submit" class="btn btn-light"><i class="bi bi-search"></i></button>

    <!-- Reset button -->
    <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
</form>
            <!--<form class="d-flex gap-2" action="{{ url()->current() }}" method="GET">-->
            <!--    <input type="search" name="search" class="form-control"-->
            <!--        placeholder="Search by Title" required>-->
            <!--    <button type="submit" class="btn btn-light"><i class="bi bi-search"></i></button>-->
            <!--    <a href="{{ route('notifications') }}" class="btn btn-light">Reset</a>-->
            <!--</form>-->
        </div>
    </div>
</div>

                    {{-- Add table content here --}}
                     <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Sr. No.</th>
                                    <th>Member ID</th>
                                    <th>Bill Amount</th>
                                    <th>Bill Month</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                               @forelse($member as $index => $m)
        <tr>
            {{-- Calculate Sr. No. with pagination offset --}}
            <td>{{ $member->firstItem() + $index }}</td>

            <td>{{ $m->Mem_Id }}</td>
            <td>{{ number_format($m->BillAmt, 2) }}</td>
            <td>{{ \Carbon\Carbon::createFromDate(null, $m->BillMonth, 1)->format('F') }}</td>
            <td class="text-center">
    @if($m->BillAmt <= 0)
        <span class="badge bg-success">Paid</span>
    @else
           @csrf
        <button 
            class="btn sendNotificationBtn"
            style="
                background: linear-gradient(45deg, #0d6efd, #6610f2);
                color: white;
                border: none;
                border-radius: 20px;
                padding: 6px 14px;
                font-size: 0.85rem;
                font-weight: 500;
                transition: all 0.3s ease;
            "
            onmouseover="this.style.boxShadow='0 4px 12px rgba(13, 110, 253, 0.4)';"
            onmouseout="this.style.boxShadow='none';"
            data-memid="{{ $m->Mem_Id }}">
            <i class="bi bi-send-fill"></i> Send Notification
        </button>
    @endif
</td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">No records found.</td>
        </tr>
    @endforelse
                            </tbody>
                        </table>
                    </div>
                 <div class="d-flex justify-content-end mt-3" style="margin-right:20px;">
    {{ $member->links('pagination::bootstrap-5') }}
</div>

                </div>
            </div>

        </div>
    </section>
    </main>
   <script>
document.querySelectorAll('.sendNotificationBtn').forEach(button => {
    button.addEventListener('click', function() {
        const memId = this.getAttribute('data-memid');

        fetch('{{ route("notifications.billpayment") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ mem_id: memId })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Notification sent successfully!');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to send notification.');
        });
    });
});
</script>
<script>
document.querySelectorAll('.billpaymentremindertoall').forEach(function(button) {
    button.addEventListener('click', function() {
        fetch("{{ route('notifications.billpaymentremindertoall') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Notifications sent successfully');
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error sending notifications');
        });
    });
});
</script>

@endsection
