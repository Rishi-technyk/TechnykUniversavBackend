@extends('layouts.web')
@section('content')

<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Subscription</h5>
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
            <div class="col-lg-4">
                <div class="card mb-1 h-100">
                    <div class="card-body text-center pt-5">
                        <div class="custom--form dashboard-form mt-5">
                            <form id="changePasswordFrom" action="{{route('payment.checkout')}}" method="post">
                                @csrf
                                <div class="dashboard-input mt-5">
                                    <input type="number" name="txtAmountCharged" value="{{ $AmountPayable }}" class="form--control" placeholder="Enter amount" required readonly/>

                                    <input type="hidden" name="type" value="Subscription">

                                    <input type="hidden" name="memberReceiptsId" value="{{ $dataReaderBill?$dataReaderBill->Mem_Id:'' }}">
                                    
                                    <div class="toggle-password">
                                        <span class="eye-icon"></span>
                                    </div>
                                </div>

                                @if(empty($member->Mobile))
                                <input type="number" name="mobile" class="form--control" placeholder="Enter Mobile Number" maxlength="10" required>
                                @endif

                                @if(isset($trans))
                                <p> <b>Date : </b> {{ date("d-m-Y", strtotime($trans->transaction_date)) }}</p>
                                <p> <b>Amount : </b> {{ $trans->amount }}</p>
                                @endif
                                @if(empty($trans))
                                <div class="btn-wrapper mt-4">
                                    <button type="submit" class="cmn-btn btn-bg-1">
                                        Proceed
                                    </button>
                                </div>
                                @else
                                <div class="btn-wrapper mt-4">
                                    <button type="button" class="cmn-btn btn-bg-1" disabled>
                                        Proceed
                                    </button>
                                </div>
                                @endif

                                @if ($dataReaderBill && file_exists( public_path() . '/Bills/' . $dataReaderBill->Mem_Id .'-'. $dataReaderBill->BillMonthYear . '.pdf'))
                                <div class="btn-wrapper mt-1">
                                    <a href="{{ url('public/Bills/' . $dataReaderBill->Mem_Id .'-'. $dataReaderBill->BillMonthYear . '.pdf') }}" target="_blank">
                                        <button type="button" class="cmn-btn btn-bg-1">
                                            View Bill
                                        </button>
                                    </a>
                                </div>
                                @endif
                            </form>
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