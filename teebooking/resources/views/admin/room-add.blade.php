@extends('layouts.admin')
@Section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Room Add</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Room</a></li>
                    <li class="breadcrumb-item active">Add</li>
                </ol>
            </nav>
        </div>
        <!-- End Page Title -->

        <section class="section dashboard">
            <div class="row">
                <!-- Left side columns -->
                <div class="col-lg-12">
                    <div class="row">
                        <!-- Reports -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Add New Room</h5>

                                    <!-- Vertical Form -->
                                    <form action="" class="row g-3" method="post">
                                        @csrf
                                        <div class="col-12">
                                            <label for="room_type" class="form-label">Room Type</label>
                                            <input type="text" name="room_type" class="form-control" id="room_type" placeholder="Enter Room Type (Single Room/Double Room)" value="{{old('room_type')}}" />
                                            <span class="invalid-feedback-1 text-danger">
                                                @error('room_type')
                                                    {{$message}}
                                                @enderror
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label for="short_description" class="form-label">Sort Description</label>
                                            <input type="text" name="short_description" class="form-control" id="short_description" placeholder="Enter Sort Description" value="{{old('short_description')}}" />
                                            <span class="invalid-feedback-1 text-danger">
                                                @error('short_description')
                                                    {{$message}}
                                                @enderror
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label for="total_rooms" class="form-label">Total Rooms</label>
                                            <input type="number" name="total_rooms" class="form-control" id="total_rooms" placeholder="Number of Total Room available" value="{{old('total_rooms')}}" />
                                            <span class="invalid-feedback-1 text-danger">
                                                @error('total_rooms')
                                                    {{$message}}
                                                @enderror
                                            </span>
                                        </div>
                                        <div class="col-12">
                                            <label for="max_guest" class="form-label">Maximum Guest</label>
                                            <input type="number" name="max_guest" class="form-control" id="max_guest" placeholder="Maximum Guest Allowed per Room" value="{{old('max_guest')}}" />
                                            <span class="invalid-feedback-1 text-danger">
                                                @error('max_guest')
                                                    {{$message}}
                                                @enderror
                                            </span>
                                        </div>

                                        <div class="text-center">
                                            <button type="submit" class="btn btn-primary">Submit</button>
                                            <button type="reset" class="btn btn-secondary">Reset</button>
                                        </div>
                                    </form><!-- Vertical Form -->

                                </div>
                            </div>
                        </div>
                        <!-- End Reports -->
                    </div>
                </div>
                <!-- End Left side columns -->
            </div>
        </section>
    </main>
@endsection
