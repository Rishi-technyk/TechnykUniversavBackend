@extends('layouts.admin')
@Section('content')
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Room Price Update</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Rooms</a></li>
                <li class="breadcrumb-item active">Price Edit</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                       
                        <form class="row g-3 mt-4 mb-4 needs-validation" method="post"
                            action="" novalidate>
                            @csrf
                            <div class="col-md-3">
                                <label class="form-label">Date</label>
                                <input type="text" class="form-control" name="date" value=""
                                    required>
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Submit</button>
                            </div>
                        </form><!-- End Custom Styled Validation -->

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection