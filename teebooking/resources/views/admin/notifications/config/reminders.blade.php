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
                            <h1 class="page-header-title" style="font-size: 1.25rem;">Bill Reminders</h1>
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
                                    Sent Users for Notification
                                </h2>
                            </div>
                        </div>
                    </div>
                    {{-- Add table content here --}}
                </div>
            </div>

        </div>
    </section>
</main>
@endsection
