@extends('frontend.layouts.app')

@section('title', 'MMR Registration')

@section('content')

<div class="container">

    <!-- Breadcrumb Section Begin -->
    <div class="card-section-title-box">
        <span class="card-section-bar"></span>
        <h4 class="card-section-title">MMR Registration for {{ date("d-M-Y", strtotime($data->start_date ?? '')) }}</h4>
    </div>
    <!-- Breadcrumb Section End -->

    @if(Session::has('message'))
    <p class="alert {{ Session::get('alert-class', 'alert-danger') }}">{{ Session::get('message') }}</p>
    @endif

    <div class="card-section-title-box">
        <span class="card-section-bar"></span>

        <form action="{{ route('mmr.registration.store') }}" method="post">
            @csrf
            
            <div class="row">
                <div class="col-lg-4 col-sm-4"></div>
                <div class="col-lg-2 col-sm-2">
                    <div class="mb-2"> <b>Member ID</b></div>
                    <div class="mb-2"> <b>Member Name</b></div>
                    <div class="mb-2"> <b>Last Date</b></div>
                </div>
                <div class="col-lg-2 col-sm-2">
                    <div class="mb-2">: {{ $member->SC_ID ?? '' }}</div>
                    <div class="mb-2">: {{ $member->DisplayName ?? '' }}</div>
                    <div class="mb-2">: {{ date("d-M-Y", strtotime($data->end_date ?? '')) }}</div>
                </div>
            </div>

            <div class="text-center mt-4">
                <button type="submit">Register Now</button>
            </div>

        </form>

    </div>

</div>

@endsection

@section('script')

@endsection