@extends('layouts.web')
@section('content')
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Member Profile</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/avatar.png') }}"
                            alt="avatar" class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{$member->DisplayName}}</h5>
                        <p class="text-muted mb-3"> {{$member->Email}}</p>
                        <div class="btn-wrapper mt-1">
                        <a class="cmn-btn btn-bg-1" href="{{route('member_edit')}}"> Edit </a>
                    </div>
                    </div>
                </div>
            
            </div>
            <div class="col-lg-8">
                <div class="card mb-1 h-100">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">Member ID</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{$member->MemberID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">C_ID</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{$member->SC_ID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">Category</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"> {{$member->CategoryType}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">Mobile</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0"> {{$member->Mobile}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-3">
                                <p class="mb-0">Status</p>
                            </div>
                            <div class="col-sm-9">
                                <p class="text-muted mb-0">{{$member->Status}}</p>
                            </div>
                        </div>
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