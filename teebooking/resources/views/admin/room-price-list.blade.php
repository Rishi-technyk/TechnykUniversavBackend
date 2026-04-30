@extends('layouts.admin')
@Section('content')
{{-- @php
dd($roomPrices);
@endphp --}}
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Room Price List</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Rooms</a></li>
                <li class="breadcrumb-item active">Price List</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card overflow-auto pt-4">
                    <div class="card-body">
                        <table class="table datatable">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Room Type</th>
                                    <th scope="col">Member Category</th>
                                    <th scope="col">Occupant Type</th>
                                    <th scope="col">Price</th>
                                    <th scope="col">GST</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roomPrices as $key => $roomPrice)
                                <tr>
                                    <th scope="row">{{ $key + 1 }}.</th>
                                    <td>{{ $roomPrice->room->title }}</td>
                                    <td>{{ $roomPrice->categoryMaster->CategoryName }}</td>
                                    <td>{{ $roomPrice->occupants->name }}</td>
                                    <td>{{ $roomPrice->price }}</td>
                                    <td>{{ $roomPrice->gst }}</td>
                                    <td>
                                        <a href="{{ route('admin.roomPriceEdit', [$roomPrice->id]) }}"
                                            class="btn btn-primary btn-sm">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="#" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th scope="row" colspan="7">No data found.</th>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection