@extends('frontend.layouts.app')



@section('title', 'Home')



@section('content')

<div class="container">

    <div class="card-section-title-box">

        <span class="card-section-bar"></span>

        <h4 class="card-section-title">Home</h4>

    </div>

    <div class="card-section-title-box">

        <span class="card-section-bar"></span>

        <div class="row">
            
            <div class="col-lg-3">

                <div class="contact-text">

                    <div class="text-center">

                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBJKXJmuqA4KJfAhb4pCutAoqJxG9WmJtCMg&s" alt="Student Profile">

                    </div>

                    <div class="text-center">
                        <button> <a href="{{ route('student.profile') }}" class="text-white">Edit Profile</a> </button>
                    </div>

                    <!-- <h2>Student Name</h2> -->

                </div>

            </div>

            <div class="col-lg-8 offset-lg-1 contact-text">

                <table class="mt-4">

                    <tbody>

                        <tr>

                            <td class="c-o">Name:</td>

                            <td> {{ $member->DisplayName }} </td>

                        </tr>

                        <tr>

                            <td class="c-o">ID:</td>

                            <td>{{ $member->MemberID }}</td>

                        </tr>

                        <tr>

                            <td class="c-o">Phone:</td>

                            <td>{{ $member->Mobile }}</td>

                        </tr>

                        <tr>

                            <td class="c-o">Email:</td>

                            <td>{{ $member->Email }}</td>

                        </tr>

                        <tr>

                            <td class="c-o">Address:</td>

                            <td>{{ $member->Address }}, {{ $member->city }}, {{ $member->state }}</td>

                        </tr>

                    </tbody>

                </table>

            </div>
        </div>

    </div>

</div>

@endsection