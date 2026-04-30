
<style>
td {
    text-align: left;
}
</style>
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Subscription Response</h5>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center ">
                        <img src="{{ asset('public/admin/assets/img/avatar.png') }}" alt="avatar"
                            class="rounded-circle img-fluid" style="width: 150px;">
                        <h5 class="my-3">{{$member->DisplayName}}</h5>
                        <p class="text-muted mb-2"> {{$member->Email}}</p>


                        <div class="btn-wrapper mt-1">
                            <a class="cmn-btn btn-bg-1" href="{{route('member_edit')}}"> Edit </a>
                        </div>

                    </div>
                </div>

            </div>
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Member ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->MemberID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">C_ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->SC_ID}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Category</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0"> {{$member->CategoryType}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Mobile</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0"> {{$member->Mobile}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Status</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$member->Status}}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-4 ">
                <div class="card mb-1 h-100">
                    <div class="card-body  ">

                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0"><b>Status</b></p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-{{$order_status=="Success"?'success':'danger'}} mb-0"><b>{{$order_status}}</b></p>
                            </div>
                        </div>
                        <hr>

                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0"> Ref No:</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0" style="font-size: 0.9rem;">{{$order_id}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Payment ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0">{{$tracking_id}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Txn ID</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0 " >{{$bank_ref_no}}</p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-4">
                                <p class="mb-0">Paid Amount</p>
                            </div>
                            <div class="col-sm-8">
                                <p class="text-muted mb-0"><?=number_format((float)$ReceivedAmount, 2, '.', '');?></p>
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
