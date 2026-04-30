<div class="dashboard-left-content">
    <div class="dashboard-close-main">
        <div class="dashboard-bottom">
            <ul class="dashboard-list list-style-none">
                <li class="list {{ (request()->route()->getName()) === 'dashboard' ? 'active' : '' }} ">
                    <a href="{{ route('dashboard') }}">
                        <i class="las la-briefcase"></i> Dashboard
                    </a>
                </li>
                <li
                    class="list {{ (request()->route()->getName() === 'checkout' || request()->route()->getName() === 'rooms' || request()->route()->getName() === 'roomDetails') ? 'active' : '' }}">
                    <a href="{{ route('rooms') }}">
                        <i class="las la-home"></i> Room Booking
                    </a>
                </li>
                <li class="list {{ (request()->route()->getName()) === 'profile' ? 'active' : '' }}">
                    <a href="{{ route('profile') }}">
                        <i class="las la-user"></i> Profile
                    </a>
                </li>
                {{-- <li class="list {{ (request()->route()->getName()) === 'changePassword' ? 'active' : '' }}">
                    <a href="{{ route('changePassword') }}">
                        <i class="las la-eye"></i> Password Change
                    </a>
                </li>
                <li class="list">
                    <a href="{{ route('logout') }}">
                        <i class="las la-sign-out-alt"></i> Log Out
                    </a>
                </li> --}}
            </ul>
        </div>
    </div>
</div>