@extends('layouts.admin')

@section('content')
 <style>
        .btn-success {
            background-color: #012970 !important;
        }
          .permitted-users {
        cursor: pointer;
        transition: color 0.3s;
    }

    .permitted-users:hover {
        color: #0d6efd; /* Bootstrap primary blue */
        text-decoration: underline;
    }
    </style>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="container">
            <!-- Notification Form (Hidden by default) -->
            <div class="row gx-2 gx-lg-3" style="display: none;" id="showNotificationBtn">
                <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                    <div class="card">
                        
                       <div class="card-header">
                                <h1 class="page-header-title" style="font-size: 1.25rem;">Notification </h1>
                            </div>
                        <div class="card-body">
                            <form action="{{ route('store') }}" method="post"
                                style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                                enctype="multipart/form-data">
                                @csrf
                                <div class="form-group mt-3">
                                    <label class="input-label">Title</label>
                                    <input type="text" name="title" class="form-control" placeholder="New notification" required>
                                </div>
                                <div class="row">
                                    <div class="form-group mt-3 col-md-6">
                                        <label class="input-label">Date</label>
                                        <input type="date" name="date" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-3 col-md-6">
                                        <label class="input-label">Time</label>
                                        <input type="time" name="time" class="form-control" required>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="input-label">Address</label>
                                    <input name="address" class="form-control" required>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="input-label">Short Intro</label>
                                    <textarea name="short_descriptions" class="form-control" maxlength="100" required></textarea>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="input-label">Description</label>
                                    <textarea id="description" name="description"></textarea>
                                </div>
                                <div class="mb-3 mt-3">
                                    <label class="form-label">Image</label>
                                    <input class="form-control form-control-sm" id="formFileSm" name="banner"
                                        type="file" onchange="previewImage(event)">
                                </div>
                                <img id="imagePreview" src="#" alt="Preview"
                                    style="display:none; width: 20%; border: 1px solid; border-radius: 10px;">
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sent Users Table -->
            <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                <div class="card">
                    <div class="card-header" style="background-color:#23384c;">
                        <div class="row justify-content-between align-items-center flex-grow-1 mx-1">
                                                            <div class="flex-between">
                                     <h2 style="font-size: 1.25rem; color: white;">
        Sent Users for Notification: {{ $notification->title }}
    </h2>

                                </div>
                                 <div style="width: 20vw; position: absolute; right: 0;">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend">
                                                {{-- <div class="input-group-text">
                                                <i class="bi bi-search"></i>
                                            </div> --}}
                                            </div>
                                           <input id="datatableSearch_" 
       type="search" 
       name="search" 
       class="form-control"
       placeholder="Search by Name, Email or Phone"
       aria-label="Search orders"
       value="{{ request('search') }}">

                                            <button type="submit" class="btn btn-light"><i
                                                    class="bi bi-search"></i></button>
                                            <div style="margin-right: 10px;"></div>
                                           <a href="{{ url()->current() }}" class="btn btn-light" onclick="window.location = window.location.pathname;">Reset</a>

                                        </div>
                                    </form>
                                    <!-- End Search -->
                                </div>
                        </div>
                    </div>

                    <div class="table-responsive datatable-custom" style="padding:20px;">
                        <table style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                            <thead class="thead-light">
                                <tr>
                                    <th>S/N</th>
                                    <th>Name</th>
                                    <th>Sent At</th>
                                    <th>Email / Phone</th>
                                    <th>Broadcast</th>
                                   
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($sentUsers as $index => $entry)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $entry->user->DisplayName ?? 'N/A' }}</td>
                                        <td>{{ $entry->formatted_sent_at }}</td>
                                        <td>{{ $entry->user->Email ?? $entry->user->Phone ?? 'N/A' }}</td>
                                        <td>
                                           <button class="btn btn-success" onclick="toggleBroadcast({{ $entry->id }})">
                                            Resend
                                        </button>
                                        </td>
                                       
                                       
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        {{-- Optional pagination if supported --}}
                        {{-- {!! $sentUsers->links() !!} --}}
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- Image Preview Script --}}
<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function(){
            const output = document.getElementById('imagePreview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
  <script>
    function toggleBroadcast(notificationUserId) {
        const userConfirmed = confirm('Are you sure you want to resend this notification?');
        if (!userConfirmed) return;

        fetch(`{{ url('admin/notifications/resend') }}/${notificationUserId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Notification resent successfully!');
            } else {
                alert('Failed to resend notification.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
    }
</script>
<script>
    const notificationData = @json($notification);
    console.log('Notification:', notificationData);
</script>

@endsection
