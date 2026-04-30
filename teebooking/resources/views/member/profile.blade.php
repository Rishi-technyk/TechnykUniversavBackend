@extends('layouts.member')

{{-- sidebar --}}
@section('sidebar')
@include('partials.member.sidebar')
@endsection
{{-- .sidebar --}}

@section('content')
<div class="dashboard-right-contents mt-4 mt-lg-0">
    <div class="dashboard-reservation">
        <div class="single-reservation bg-white base-padding">
            <div class="single-reservation-flex mb-4">
                <div class="single-reservation-author">
                    <div class="single-reservation-author-flex">
                        <div class="single-reservation-author-thumb">
                            <img src="{{ asset('public/member/assets/img/single-page/author.jpg') }}" alt="img" />
                        </div>
                        <div class="single-reservation-author-contents">
                            <h5 class="single-reservation-author-contents-title">
                                {{ auth()->user()->DisplayName }}
                            </h5>
                            <p class="single-reservation-author-contents-para">
                                {{ auth()->user()->Email }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="btn-wrapper">
                    <a href="dashboard_edit_profile.html" class="cmn-btn btn-border">
                        Edit Profile
                    </a>
                </div>
            </div>
            <div class="single-reservation-item">
                <div class="single-reservation-contact">
                    <div class="single-reservation-contact-item">
                        <strong>C-ID</strong><br />
                        {{ auth()->user()->SC_ID }}
                    </div>
                    <div class="single-reservation-contact-item">
                        <strong>Membership No.</strong><br />
                        {{ auth()->user()->MemberID }}
                    </div>
                    <div class="single-reservation-contact-item">
                        <strong>Member Category</strong><br />
                        {{ auth()->user()->category->CategoryName }}
                    </div>
                    <div class="single-reservation-contact-item">
                        <strong>Status</strong><br />
                        {{ auth()->user()->Status }}
                    </div>
                </div>
            </div>
        </div>
        <div class="single-reservation bg-white base-padding">
            <div class="single-reservation-flex">
                <div class="single-reservation-author">
                    <div class="single-reservation-author-flex">
                        <div class="single-reservation-author-contents">
                            <h5 class="single-reservation-author-contents-title">
                                Password
                            </h5>
                            <p class="single-reservation-author-contents-para">
                                Last change 4 month ago
                            </p>
                        </div>
                    </div>
                </div>
                <div class="btn-wrapper">
                    <a href="{{ route('changePassword') }}" class="cmn-btn btn-border">
                        Change Password
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection