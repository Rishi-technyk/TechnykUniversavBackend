@extends('layouts.admin')
@Section('content')
{{-- @php
dd(request()->route()->getName());
@endphp --}}
<main id="main" class="main">

    <div class="pagetitle">
        <h1>Room Price Add</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Rooms</a></li>
                <li class="breadcrumb-item active">Price Add</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        {{-- <h5 class="card-title">Custom Styled Validation</h5>
                        <p>For custom Bootstrap form validation messages, you’ll need to add the <code>novalidate</code>
                            boolean</p> --}}
                        <form class="row g-3 mt-4 mb-4 needs-validation" method="post"
                            action="{{ route('admin.roomPriceStore') }}" novalidate>
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label">Room Type</label>
                                <select name="room_type" class="form-select" required>
                                    <option value="">Select One</option>
                                    @foreach ($rooms as $room)
                                    <option value="{{ $room->id }}">{{ $room->title }}</option>
                                    @endforeach
                                </select>
                                @error('room_type')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Member Category</label>
                                <select name="member_category" class="form-select">
                                    <option value="">Select One</option>
                                    @foreach ($master_categories as $master_categorie)
                                    <option value="{{ $master_categorie->code }}">{{ $master_categorie->CategoryName }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('member_category')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Occupant Type</label>
                                <select name="occupant_type" class="form-select" required>
                                    <option value="">Select One</option>
                                    @foreach ($occupant_types as $occupant_type)
                                    <option value="{{ $occupant_type->id }}">{{ $occupant_type->name }}</option>
                                    @endforeach
                                </select>
                                @error('occupant_type')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Price</label>
                                <input type="number" class="form-control" name="price" required>
                                @error('price')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">GST</label>
                                <input type="number" class="form-control" name="gst" value="0" required>
                                @error('gst')
                                <div class="invalid-feedback" style="display:block;">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <button class="btn btn-primary" type="submit">Submit</button>
                                <button type="reset" class="btn btn-secondary">Reset</button>
                            </div>
                        </form><!-- End Custom Styled Validation -->

                    </div>
                </div>
            </div>
        </div>
    </section>

</main>
@endsection