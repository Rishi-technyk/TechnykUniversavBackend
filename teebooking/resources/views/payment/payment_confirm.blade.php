@extends('layouts.web')
@section('content')
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                   <h5>Payment Info</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-1 h-100">
                    <div class="card-body">

                        <div class="row mb-4">

                            <div class="col-lg-3"> <b>Transaction ID</b> </div>

                            <div class="col-lg-9"> <b>{{$tt->transID}}</b> </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Name</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{$member->DisplayName}}</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Email</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{$member->Email}}</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Member ID</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{$member->MemberID}}</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">C_ID</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{$member->SC_ID}}</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Payment Status</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">
                                    <p class="text-muted mb-0">
                                        <b>
                                            @if($tt->payment_status=='Paid')
                                                <span class="text-success">Paid</span>
                                            @elseif($tt->payment_status=='Failed')
                                                <span class="text-danger">Failed</span>
                                            @else
                                                <span class="text-danger">Not Paid</span>
                                            @endif
                                        </b>
                                    </p>
                                </p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Payment Type</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{$tt->type}}</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Paid Amount</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{number_format($tt->amount,2)}}</p>
                            </div>

                            <div class="col-lg-3 mt-4">
                                <p class="mb-0">Payment Date</p>
                            </div>
                            
                            <div class="col-lg-3 mt-4">
                                <p class="text-muted mb-0">{{ date("d-m-Y", strtotime($tt->created_at)); }}</p>
                            </div>

                        </div>

                        @if($tt->type=='Banquet Booking')
                        <div class="text-center mt-4">
                            <a href="{{ route('banquet.traction') }}" class="btn btn-success">My Bookings</a>
                        </div>
                        @endif


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