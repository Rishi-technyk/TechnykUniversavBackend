@extends('layouts.web')
@section('content')
<section style="background-color: #eee;">
    <div class="container py-5">
        <div class="row">
            <div class="col">
                <nav aria-label="breadcrumb" class="bg-light rounded-3 p-3 mb-4 border-left">
                    <h5>Member Edit</h5>
                </nav>
            </div>
        </div>

        <div class="row">

            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="single-reservation bg-white base-padding">
                            <h3 class="single-reservation-title"></h3>
                            <div class="custom--form dashboard-form">
                                <form id="changePasswordFrom" action="{{ route('member_update') }}" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title"> Member's DOB </label>
                                                <input type="date" value="{{$member->DOB}}" name="DOB" class="form--control"
                                                    placeholder="Member's DOB" required />
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title"> Spouse Name </label>
                                                <input type="text" value="{{$member->SpouseName}}" name="SpouseName" class="form--control"
                                                    placeholder="Enter spouse name" required />
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title"> Spouse DOB </label>
                                                <input type="date" name="SpouseDOB" value="{{$member->SpouseDOB}}" class="form--control"
                                                    placeholder="Enter Spouse DOB" required />
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">Anniversary Date</label>
                                                <input type="date" name="AnniversaryDate" value="{{$member->AnniversaryDate}}" class="form--control"
                                                    placeholder="Enter Anniversary Date" required />
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">Email_ID :</label>
                                                <input type="email" name="Email" value="{{$member->Email}}" class="form--control"
                                                    placeholder="Enter Email ID" required />
                                                
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">Mobile :</label>
                                                <input type="number" name="Mobile" value="{{$member->Mobile}}" class="form--control"
                                                    placeholder="Enter Mobile" required />
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">State </label>
                                                <select name="state"  class="form--control form-select">
                                                    <option>wedwe</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">City </label>
                                                <select name="city"  value="{{$member->MemberDOB}}" class="form--control form-select">
                                                    <option>wedwe</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-4 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">Pin </label>
                                                <input type="number" name="pin" value="{{$member->pin}}" class="form--control"
                                                    placeholder="Enter Mobile" required />
                                            </div>
                                        </div>
                                        <div class="col-lg-12 mb-3">
                                            <div class="dashboard-input mt-1">
                                                <label class="label-title">Address </label>
                                                <textarea name="Address" class="form--control">{{$member->Address}}
                                                </textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="btn-wrapper mt-1">
                                        <button type="submit" class="cmn-btn btn-bg-1">
                                            Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>
<!-- !!- ===================================== Content End ======================== -!! -->

@endsection