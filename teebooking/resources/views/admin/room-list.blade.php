@extends('layouts.admin')
@Section('content')
<main id="main" class="main">
    <div class="pagetitle">
        <h1>Room List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Room</a></li>
                <li class="breadcrumb-item active">List</li>
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
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Room List</h5>
                                <p>
                                    Showing {{ (count($rooms))? 1 : 0 }}-{{ count($rooms) }} of {{ count($rooms) }} results
                                </p>
                                <!-- Small tables -->
                                <table class="table table-hover table-bordered">
                                    <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Room Type</th>
                                        <th scope="col">Short Description</th>
                                        <th scope="col">Total Room</th>
                                        <th scope="col">Max. Guest</th>
                                        <th scope="col">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($rooms as  $index => $room)
                                    <tr>
                                        <th scope="row">{{$index+1}}</th>
                                        <td>{{$room->title}}</td>
                                        <td>{{$room->short_description}}</td>
                                        <td>{{$room->total_rooms}}</td>
                                        <td>{{$room->max_guest}}</td>
                                        <td>
                                            <a href="#">
                                                Set Price
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <th scope="row" colspan="6">No rooms found.</th>
                                        </tr>
                                    @endforelse
                                    </tbody>
                                </table>
                                <!-- End small tables -->

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
