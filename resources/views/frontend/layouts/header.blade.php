<!-- Offcanvas Menu Section Begin -->

<div class="offcanvas-menu-overlay"></div>

<div class="canvas-open">

    <i class="icon_menu"></i>

</div>

<div class="offcanvas-menu-wrapper">

    <div class="canvas-close">

        <i class="icon_close"></i>

    </div>

    <nav class="mainmenu mobile-menu">

        <ul>

            <li class="active"><a href="{{ route('student.dashboard') }}">Home</a></li>

            <li><a href="{{ route('bill.payments') }}">Bill Payments</a></li>

            <!--<li><a href="{{ route('recharge') }}">Recharge</a></li>-->

            <li><a href="javascript:;">Transactions</a>
                <li><a href="{{ route('transaction') }}">Gateway Transactions</a></li>
                <li><a href="{{ route('prepaid.transaction') }}">Prepaid Transactions</a></li>
                <li><a href="{{ route('postpaid.transaction') }}">Postpaid Transactions</a></li>
            </li>

            <li><a href="javascript:;">Bookings </a>

                <ul class="dropdown">

                    @if($setting && $setting->activity_booking_form=='Active')

                    <li><a href="{{ route('activity.booking') }}">Activity Booking</a></li>

                    <li><a href="{{ route('activity.booking.transactions') }}">Activity Transactions</a></li>

                    @endif

                    @if($setting && $setting->table_booking_form=='Active')

                    <li><a href="{{ route('table.booking') }}">Table Booking</a></li>

                    <li><a href="{{ route('table.transaction') }}">Table Transactions</a></li>

                    @endif

                    <li><a href="{{ url('teebooking/login/' . Auth::guard('student')->user()->MemberID) }}" target="_blank">Tee Booking</a></li>

                    @if($setting && $setting->room_booking_module=='Active')

                    <li><a href="{{ route('room.booking') }}">Room Booking</a></li>

                    <li><a href="{{ route('room.transaction') }}">Room Transactions</a></li>

                    @endif



                    @if($setting && $setting->banquest_booking_form=='Active')

                    <li><a href="{{ route('banquet.booking') }}">Banquet Booking</a></li>

                    <li><a href="{{ route('banquet.transaction') }}">Banquet Transactions</a></li>

                    @endif



                    @if($registrationSetting)

                    <li><a href="{{ route('mmr.registration') }}">MMR Registration</a></li>

                    @endif

                    @foreach($document as $doc)

                    <li><a href="{{ asset($doc->file_path) }}" target="_blank">{{ $doc->label }}</a></li>

                    @endforeach

                </ul>

            </li>

            @if(count($menus))

            <li><a href="javascript:;">Party Menu</a>

                <ul class="dropdown">

                    @foreach($menus as $menu)

                        @if ($menu->data && File::exists(public_path($menu->data)))

                        <li><a href="{{ asset($menu->data) }}" target="_blank">{{ $menu->name }}</a></li>

                        @endif

                    @endforeach

                </ul>

            </li>

            @endif

            @if(count($document))

            <li><a href="javascript:;">News % Updates</a>

                <ul class="dropdown">

                    @foreach($document as $doc)

                    <li><a href="{{ asset($doc->file_path) }}" target="_blank">{{ $doc->label }}</a></li>

                    @endforeach

                </ul>

            </li>

            @endif

            <li><a href="javascript:;">Setting</a>

                <ul class="dropdown">

                    <li><a href="{{ route('student.profile') }}">Profile</a></li>

                    <li><a href="{{ route('student.change.password') }}">Change Password</a></li>

                    <li><a href="javascript:;" onclick="document.getElementById('logout-form').submit();">Logout</a></li>

                    <form action="{{ route('student.logout') }}" method="post" id="logout-form">

                        @csrf

                    </form>

                </ul>

            </li>



        </ul>

    </nav>

    <div id="mobile-menu-wrap"></div>

    <ul class="top-widget mt-4">

        <li><i class="fa fa-phone"></i> {{ $setting->phone }}</li>

        <li><i class="fa fa-envelope"></i> {{ $setting->email }}</li>

    </ul>

</div>

<!-- Offcanvas Menu Section End -->



<!-- Header Section Begin -->

<header class="header-section header-normal">

    <div class="top-nav">

        <div class="container">

            <marquee behavior="scroll" class="mt-2" direction="left" style="color:red; font-size:18px;">

            {{ $setting->student_header_message ?? 'Welcome to Member Portal Management System' }}

            </marquee>

        </div>

    </div>

    <div class="menu-item">

        <div class="container">

            <div class="row">

                <div class="col-lg-2">

                    <div class="logo">

                        <a href="{{ route('student.dashboard') }}">

                            <img src="{{ asset($setting->logo) }}" style="max-width: 100% !important; margin-top: 9%;" alt="">

                        </a>

                    </div>

                </div>

                <div class="col-lg-10">

                    <div class="nav-menu">

                        <nav class="mainmenu">

                            <ul>

                                <li class="{{ Request::segment(2)=='dashboard'?'active':'' }}"><a href="{{ route('student.dashboard') }}">Home</a></li>

                                <li class="{{ Request::segment(2)=='bill'?'active':'' }}"><a href="{{ route('bill.payments') }}">Bill Payments</a></li>

                                <!--<li class="{{ Request::segment(2)=='recharge'?'active':'' }}"><a href="{{ route('recharge') }}">Recharge</a></li>-->

                                <li class="{{ Request::segment(2)=='transaction'?'active':'' }} {{ Request::segment(3)=='transaction'?'active':'' }}"><a href="javascript:;">Transactions</a>
                                    <ul class="dropdown">
                                        <li><a href="{{ route('transaction') }}">Gateway Transactions</a></li>
                                        <li><a href="{{ route('prepaid.transaction') }}">Prepaid Transactions</a></li>
                                        <li><a href="{{ route('postpaid.transaction') }}">Postpaid Transactions</a></li>
                                    </ul>
                                </li>

                                <li class="{{ Request::segment(2)=='room'?'active':'' }} {{ Request::segment(2)=='table-booking'?'active':'' }} {{ Request::segment(2)=='banquet'?'active':'' }}"><a href="javascript:;">Bookings</a>

                                    <ul class="dropdown">

                                        @if($setting && $setting->activity_booking_form=='Active')

                                        <li><a href="{{ route('activity.booking') }}">Activity Booking</a></li>

                                        <li><a href="{{ route('activity.booking.transactions') }}">Activity Transactions</a></li>

                                        @endif

                                        @if($setting && $setting->table_booking_form=='Active')

                                        <li><a href="{{ route('table.booking') }}">Table Booking</a></li>

                                        <li><a href="{{ route('table.transaction') }}">Table Transactions</a></li>

                                        @endif

                                        <li><a href="{{ url('teebooking/login/' . Auth::guard('student')->user()->MemberID) }}" target="_blank">Tee Booking</a></li>

                                        @if($setting && $setting->room_booking_module=='Active')

                                        <li><a href="{{ route('room.booking') }}">Room Booking</a></li>

                                        <li><a href="{{ route('room.transaction') }}">Room Transactions</a></li>

                                        @endif

                                        

                                        @if($setting && $setting->banquest_booking_form=='Active')

                                        <li><a href="{{ route('banquet.booking') }}">Banquet Booking</a></li>

                                        <li><a href="{{ route('banquet.transaction') }}">Banquet Transactions</a></li>

                                        @endif

                                        

                                        @if($registrationSetting)

                                        <li><a href="{{ route('mmr.registration') }}">MMR Registration</a></li>

                                        @endif                                        

                                    </ul>

                                </li>

                                @if(count($menus))

                                <li><a href="javascript:;">Party Menu</a>

                                    <ul class="dropdown">

                                        @foreach($menus as $menu)

                                            @if ($menu->data && File::exists(public_path($menu->data)))

                                            <li><a href="{{ asset($menu->data) }}" target="_blank">{{ $menu->name }}</a></li>

                                            @endif

                                        @endforeach

                                    </ul>

                                </li>

                                @endif

                                @if(count($document))

                                <li><a href="javascript:;">News & Updates</a>

                                    <ul class="dropdown">

                                        @foreach($document as $doc)

                                        <li><a href="{{ asset($doc->file_path) }}" target="_blank">{{ $doc->label }}</a></li>

                                        @endforeach

                                    </ul>

                                </li>

                                @endif

                                <li class="user-profile">

                                    <a href="javascript:void(0);" class="profile-toggle">

                                        <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQBJKXJmuqA4KJfAhb4pCutAoqJxG9WmJtCMg&s" alt="User" class="profile-img">

                                        <!-- <span class="user-name">{{ Auth::guard('student')->user()->DisplayName }}</span> -->

                                        <i class="fa fa-angle-down"></i>

                                    </a>

                                    <ul class="dropdown">

                                        <li><a href="{{ route('student.profile') }}">Profile</a></li>

                                        <li><a href="{{ route('student.change.password') }}">Change Password</a></li>

                                        <li><a href="javascript:;" onclick="document.getElementById('logout-form').submit();">Logout</a></li>

                                        <form action="{{ route('student.logout') }}" method="post" id="logout-form">

                                            @csrf

                                        </form>

                                    </ul>

                                </li>

                            </ul>

                        </nav>

                    </div>

                </div>

            </div>

        </div>

    </div>

</header>

<!-- Header End -->