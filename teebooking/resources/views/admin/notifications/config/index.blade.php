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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">

    <main id="main" class="main">
        <section class="section dashboard">

            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Total permitted users {{ $permittedUserCount }}</h6>


                    
                    <a href="#" onclick="addNotificationBtn()" class="btn btn-success"><i class="bi bi-plus"></i>Add
                        Notification</a>
                </div>
                {{-- @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>  --}}
                {{-- @endif --}}
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
                                        <label class="input-label" for="exampleFormControlInput1">Title </label>
                                        <input type="text" name="title" class="form-control"
                                            placeholder="New notification" required>
                                    </div>
                                    <div class="row">
                                        <div class="form-group mt-3 col-md-6">
                                            <label class="input-label" for="exampleFormControlInput1">Date</label>
                                            <input type="date" name="date" class="form-control" required>
                                        </div>
                                        <div class="form-group mt-3 col-md-6">
                                            <label class="input-label" for="exampleFormControlInput1">Time</label>
                                            <input type="time" name="time" class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="input-label" for="exampleFormControlInput1">Address</label>
                                        <input name="address" class="form-control" required>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="input-label" for="exampleFormControlInput1">Short Intro</label>
                                        <textarea name="short_descriptions" class="form-control" maxlength="100" required></textarea>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label class="input-label" for="description">Description</label>
                                        <textarea id="description" name="description"></textarea>
                                    </div>

                                    <div class="mb-3 mt-3">
                                        <label for="formFileSm" class="form-label">Image</label>
                                        <input class="form-control form-control-sm" id="formFileSm" name="banner"
                                            type="file" onchange="previewImage(event)">
                                    </div>
                                    <img id="imagePreview" src="#" alt="Preview"
                                        style="display:none; width: 20%; border: 1px solid; border-radius: 10px;">
                                    <hr>
                                    <button type="submit" class="btn btn-success">Create Notification</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-12 col-lg-12 mb-3 mb-lg-2">
                    <div class="card">
                        <div class="card-header" style="background-color:#23384c;">
                            <div class="row justify-content-between align-items-center flex-grow-1 mx-1">
                                <div class="flex-between">
                                    <div>
                                        <h2 style="font-size: 1.25rem;color:white;">
                                            Notification({{ $notification->total() }})</h2>
                                    </div>

                                </div>
                                <div style="width: 20vw; position: absolute; right: 0;">
                                    <form action="{{ url()->current() }}" method="GET">
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend">
                                                {{-- <div class="input-group-text">
                                                <i class="bi bi-search"></i>
                                            </div> --}}
                                            </div>
                                            <input id="datatableSearch_" type="search" name="search" class="form-control"
                                                placeholder="Search by Title" aria-label="Search orders" value=""
                                                required>
                                            <button type="submit" class="btn btn-light"><i
                                                    class="bi bi-search"></i></button>
                                            <div style="margin-right: 10px;"></div>
                                            <a href="{{ route('notifications') }}" class="btn btn-light">Reset</a>
                                        </div>
                                    </form>
                                    <!-- End Search -->
                                </div>
                            </div>
                        </div>
                        <!-- Table -->
                        <div class="table-responsive datatable-custom" style="padding:20px;">
                            <table style="text-align: {{ Session::get('direction') === 'rtl' ? 'right' : 'left' }};"
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>S/N</th>
                                        <th style="width: 30%">Title</th>
                                        <!--<th style="width: 30%">Description</th>-->
                                        <th>Banner</th>
                                        <th>Date</th>
                                        <th>Users Count</th>
                                        <th>Status</th>
                                        <th>Broadcast</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($notification as $key => $notificationItem)
                                        <tr>
                                            <td>{{ $notificationItem->id }}</td>
                                            <td>
                                                <span class="d-block font-size-sm text-body">
                                                    {{ $notificationItem->title }}
                                                </span>
                                            </td>
                                            <!--<td>{{ $notificationItem->short_descriptions }}</td>-->
                                            <td>
                                                <img style="height: 75px"
                                                src="{{ url('get-notification-image/' . $notificationItem->image) }}">
                                            </td>
                                            <td>{{ $notificationItem->date }}</td>
                                           <td>
                                                <a href="{{ route('admin.notifications.sentUsers', $notificationItem->id) }}">
                                                    {{ $notificationItem->sent_user_count }}
                                                </a>
                                            </td>
                                            <td>
                                                <label class="switch">
                                                    <input type="checkbox" class="status"
                                                        id="{{ $notificationItem->id }}"
                                                        {{ $notificationItem->active_status == 1 ? 'checked' : '' }}>
                                                    <span class="slider round"></span>
                                                </label>
                                            </td>
                                            <td>
                                                <!--<label class="switch">-->
                                                <!--    <input type="checkbox" class="toggle-switch-input"-->
                                                <!--        onclick="toggleBroadcast({{ $notificationItem->id }}, {{ $notificationItem->broadcast == 1 ? 'false' : 'true' }})"-->
                                                <!--        {{ $notificationItem->broadcast ? 'checked' : '' }}>-->
                                                <!--    <span class="slider round"></span>-->
                                                <!--</label>-->
                                                <button class="btn btn-success" onclick="toggleBroadcast({{ $notificationItem->id }}, 'false')">
                                                    Send
                                                </button>
                                            </td>
                                            <td>
                                                <a href="{{ route('edit', [$notificationItem['id']]) }}"
                                                    class="btn btn-light"><i class="bi bi-pencil"></i></a>
                                                <a href="{{ route('delete', [$notificationItem['id']]) }}"
                                                    class="btn btn-light"> <i class="bi bi-trash"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <table>
                                <tfoot>
                                    {!! $notification->links() !!}
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
   <script src="https://cdn.ckeditor.com/ckeditor5/35.3.0/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/tinymce@5/tinymce.min.js"></script>

    <script>
        ClassicEditor
            .create(document.querySelector('#description'))
            .catch(error => {
                console.error(error);
            });
    </script>
    <script>
        $(document).ready(function() {
            $('.dropdown-toggle').dropdown();
        });

        $(document).on('ready', function() {
            // INITIALIZATION OF DATATABLES
            // =======================================================
            var datatable = $.HSCore.components.HSDatatables.init($('#columnSearchDatatable'));

            $('#column1_search').on('keyup', function() {
                datatable
                    .columns(1)
                    .search(this.value)
                    .draw();
            });


            $('#column3_search').on('change', function() {
                datatable
                    .columns(2)
                    .search(this.value)
                    .draw();
            });


            // INITIALIZATION OF SELECT2
            // =======================================================
            $('.js-select2-custom').each(function() {
                var select2 = $.HSCore.components.HSSelect2.init($(this));
            });
        });

        $(document).on('change', '.status', function() {
            var previousChecked = $(this).prop('checked');
            const userConfirmed = confirm('Are you sure you want to change the notificaiton status?');
            if (userConfirmed) {
                var id = $(this).attr("id");
                var status = $(this).prop("checked") ? 1 : 0;
                $.ajax({
                    url: "{{ route('status') }}", // Use the JavaScript variable
                    method: 'POST',
                    data: {
                        id: id,
                        status: status,
                        _token: $('meta[name="_token"]').attr('content') // Ensure CSRF token is sent
                    },
                    success: function(data) {
                        toastr.success('Status updated successfully');
                    },
                    error: function(xhr, status, error) {
                        console.error(error); // Log any errors to the console for debugging
                    }
                });
            } else {
                $(this).prop('checked', !previousChecked);
            }
        });
    </script>
    <script>
        function toggleBroadcast(notificationId, status) {
            const userConfirmed = confirm('Are you sure you want to send a notification to all the registered members?');
         
            if (userConfirmed) {
                $.ajax({
                    url: "{{ route('broadcast') }}",
                    method: "POST",
                    data: {
                        id: notificationId,
                        status: status,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        // Show alert box with the broadcast status change
                        let statusText = status ? true : false;
                        alert(`Notification ID: ${notificationId}, Broadcast status changed to ${statusText}`);
                    },
                    error: function(xhr, status, error) {
                        // Handle error if AJAX request fails
                        console.error(error);
                    }
                });
            } else {
                // User clicked Cancel, do nothing
                console.log('User cancelled the broadcast change.');
            }
        }
    </script>
    <script>
        function previewImage(event) {
            var input = event.target;
            var reader = new FileReader();

            reader.onload = function() {
                var imagePreview = document.getElementById('imagePreview');
                imagePreview.src = reader.result;
                imagePreview.style.display = 'block';
            };

            reader.readAsDataURL(input.files[0]);
        }

        function addNotificationBtn() {
            var element = document.getElementById("showNotificationBtn");
            if (element.style.display === "none") {
                element.style.display = "block";
            } else {
                element.style.display = "none";
            }
        }
    </script>
@endsection
