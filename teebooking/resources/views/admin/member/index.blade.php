@extends('layouts.admin')
@Section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Member List</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="#">Member</a></li>
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
                                <div class="card-body pt-4 pb-4">
                                    <!--<h5 class="card-title">Member List</h5>-->
                                    <p>

                                    </p>
                                    <!-- Small tables -->
                                    <table class="table datatable">
                                        <thead>
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Scid</th>
                                                <th scope="col">Name</th>
                                                <th scope="col">Email</th>
                                                <th scope="col">Phone</th>
                                                <th scope="col">Gender</th>
                                                <th scope="col">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($members as $key=>$member)
                                                <tr>
                                                    <td>{{ $key + 1 }}</td>
                                                    <td>{{ $member->SC_ID }}</td>
                                                    <td>{{ $member->DisplayName }}</td>
                                                    <td>{{ $member->Email }}</td>
                                                    <td>{{ $member->Phone }}</td>
                                                    <td>{{ $member->Gender }}</td>

                                                    <td><button type="button" class="btn btn-primary"><i class="bi bi-pencil-fill"></i></button>
                                                    </td>
                                                @empty
                                                    <td colspan=6>No Data</td>
                                            @endforelse
                                            </tr>
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
