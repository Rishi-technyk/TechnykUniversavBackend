@extends('layouts.web')
@section('content')
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>OTP</h5>
                </nav>
            </div>
        </div>

        <div class="row">
        <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/avatar.png') }}" alt="avatar"
                            class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{auth()->user()->DisplayName}}</h5>
                        <p class="text-muted mb-3"> {{auth()->user()->Email}}</p>
                        <div class="btn-wrapper mt-1">
                            <a class="cmn-btn btn-bg-1" href="{{route('member_edit')}}"> Edit </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card mb-4 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/otp.jpg') }}"
                            alt="avatar" class="rounded-circle img-fluid" style="width: 150px;padding-left: 25px;">
                        <h3 class="my-3">{{$otp}}</h3>
                        <div class="btn-wrapper mt-5 pt-4">
                        <a class="cmn-btn btn-bg-1" href="{{route('member_otp')}}">   Refresh </a>
                    </div>
                        <!-- <p class="text-muted mb-3"> </p> -->
                        <!-- <div class="d-flex justify-content-center mb-2">
                            <button type="button" class="btn btn-primary">Follow</button>
                        </div> -->
                    </div>
                </div>
            
            </div>
            
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->
@push('js')

@endpush()
@endsection