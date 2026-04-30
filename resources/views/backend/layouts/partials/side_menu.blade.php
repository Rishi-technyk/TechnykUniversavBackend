<!-- Sidebar Menu -->
<nav class="mt-2">
    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
        @can('dashboard.manage')
        <li class="nav-item">
            <a href="{{ route('dashboard') }}" class="nav-link {{ Request::segment(2)=='dashboard'?'active':'' }}">
                <i class="nav-icon fas fa-tachometer-alt"></i>
                <p>
                    Dashboard
                </p>
            </a>
        </li>
        @endcan

        @can('role.manage')
        <li class="nav-item">
            <a href="{{ route('admin.roles') }}" class="nav-link {{ Request::segment(2)=='roles'?'active':'' }} {{ Request::segment(2)=='role'?'active':'' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>
                    Roles
                </p>
            </a>
        </li>
        @endcan

        @can('user.manage')
        <li class="nav-item">
            <a href="{{ route('admin.users') }}" class="nav-link {{ Request::segment(2)=='users'?'active':'' }} {{ Request::segment(2)=='user'?'active':'' }}">
                <i class="nav-icon fas fa-users"></i>
                <p>
                    Admins
                </p>
            </a>
        </li>
        @endcan

        @can('room-occupant.manage')
        <li class="nav-item">
            <a href="{{ route('admin.occupants') }}" class="nav-link {{ Request::segment(2)=='occupants'?'active':'' }} {{ Request::segment(2)=='occupant'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Occupant Masters
                </p>
            </a>
        </li>
        @endcan

        @can('room-category.manage')
        <li class="nav-item">
            <a href="{{ route('admin.room_categories') }}" class="nav-link {{ Request::segment(2)=='room-categories'?'active':'' }} {{ Request::segment(2)=='room-category'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Room Categories
                </p>
            </a>
        </li>
        @endcan

        @can('room-charges.manage')
        <li class="nav-item">
            <a href="{{ route('admin.room_charges') }}" class="nav-link {{ Request::segment(2)=='room-charges'?'active':'' }} {{ Request::segment(2)=='room-charge'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Room Charges
                </p>
            </a>
        </li>
        @endcan

        @can('room-block.manage')
        <li class="nav-item">
            <a href="{{ route('admin.block_rooms') }}" class="nav-link {{ Request::segment(2)=='block-rooms'?'active':'' }} {{ Request::segment(2)=='block-room'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Block Rooms
                </p>
            </a>
        </li>
        @endcan

        @can('room-cancellation-policy.manage')
        <li class="nav-item">
            <a href="{{ route('admin.room_cancellation_policies') }}" class="nav-link {{ Request::segment(2)=='room-cancellation-policies'?'active':'' }} {{ Request::segment(2)=='room-cancellation-policy'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Cancellation Policies
                </p>
            </a>
        </li>
        @endcan

        @can('room-booking.manage')
        <li class="nav-item">
            <a href="{{ route('admin.room_bookings') }}" class="nav-link {{ Request::segment(2)=='room-bookings'?'active':'' }} {{ Request::segment(2)=='room-booking'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Room Bookings
                </p>
            </a>
        </li>
        @endcan

        @can('document.manage')
        <li class="nav-item">
            <a href="{{ route('admin.documents') }}" class="nav-link {{ Request::segment(2)=='documents'?'active':'' }} {{ Request::segment(2)=='document'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Documents
                </p>
            </a>
        </li>
        @endcan

        @can('table-party-menu.manage')

        <li class="nav-item">
            <a href="{{ route('admin.menus') }}" class="nav-link {{ Request::segment(2)=='menus'?'active':'' }} {{ Request::segment(2)=='menu'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Party Menu
                </p>
            </a>
        </li>

        @endcan

        @can('room-SOP.manage')
        <li class="nav-item">
            <a href="{{ route('admin.room_sops') }}" class="nav-link {{ Request::segment(2)=='room-sops'?'active':'' }} {{ Request::segment(2)=='room-sop'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    SOP
                </p>
            </a>
        </li>
        @endcan

        @php
            $menuOpen = in_array(Request::segment(2), [
                'venue-masters',
                'venue-master',
                'session',
                'sessions',
                'banquet-occupants',
                'banquet-occupant',
                'functions',
                'function',
                'venue-charges',
                'venue-charge'
            ]);
        @endphp

        @canany(['banquet-venue.manage', 'banquet-session.manage', 'banquet-occupant.manage', 'banquet-function.manage', 'banquet-venue-charge.manage'])
        <li class="nav-item {{ $menuOpen ? 'menu-is-opening menu-open' : '' }}">
            <a href="#" class="nav-link {{ $menuOpen ? 'active' : '' }}">
                <i class="nav-icon fas fa-table"></i>
                <p>
                    Masters
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @can('banquet-venue.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.venue_masters') }}" class="nav-link {{ Request::segment(2)=='venue-masters'?'active':'' }} {{ Request::segment(2)=='venue-master'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Venue</p>
                    </a>
                </li>
                @endcan

                @can('banquet-session.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.sessions') }}" class="nav-link {{ Request::segment(2)=='sessions'?'active':'' }} {{ Request::segment(2)=='session'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Session</p>
                    </a>
                </li>
                @endcan
                
                @can('banquet-occupant.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.banquet_occupants') }}" class="nav-link {{ Request::segment(2)=='banquet-occupants'?'active':'' }} {{ Request::segment(2)=='banquet-occupant'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Occupant</p>
                    </a>
                </li>
                @endcan

                @can('banquet-function.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.functions') }}" class="nav-link {{ Request::segment(2)=='functions'?'active':'' }} {{ Request::segment(2)=='function'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Function</p>
                    </a>
                </li>
                @endcan

                @can('banquet-venue-charge.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.venue_charges') }}" class="nav-link {{ Request::segment(2)=='venue-charges'?'active':'' }} {{ Request::segment(2)=='venue-charge'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Venue Charge</p>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcanany

        @can('banquet-venue-block.manage')
        <li class="nav-item">
            <a href="{{ route('admin.venue_blocks') }}" class="nav-link {{ Request::segment(2)=='venue-blocks'?'active':'' }} {{ Request::segment(2)=='venue-block'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Venue Block
                </p>
            </a>
        </li>
        @endcan

        @can('banquet-cancellation-policy.manage')
        <li class="nav-item">
            <a href="{{ route('admin.cancellation_policies') }}" class="nav-link {{ Request::segment(2)=='cancellation-policies'?'active':'' }} {{ Request::segment(2)=='cancellation-policy'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Cancellation Policy
                </p>
            </a>
        </li>
        @endcan

        @can('banquet-booking.manage')
        <li class="nav-item">
            <a href="{{ route('admin.banquet_bookings') }}" class="nav-link {{ Request::segment(2)=='banquet-bookings'?'active':'' }} {{ Request::segment(2)=='banquet-booking'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Bookings
                </p>
            </a>
        </li>
        @endcan

        @can('banquet-SOP.manage')
        <li class="nav-item">
            <a href="{{ route('admin.banquet_sops') }}" class="nav-link {{ Request::segment(2)=='banquet-sops'?'active':'' }} {{ Request::segment(2)=='banquet-sop'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    SOP
                </p>
            </a>
        </li>
        @endcan

        @php
            $menuOpen = in_array(Request::segment(2), [
                'table-venues',
                'table-venue',
                'table-meals',
                'table-meal',
                'table-times',
                'table-time',
                'tables',
                'table'
            ]);

        @endphp

        @canany(['table-venue.manage', 'table-meal.manage', 'table-time.manage', 'table-master.manage' ])
        <li class="nav-item {{ $menuOpen ? 'menu-is-opening menu-open' : '' }}">
            <a href="#" class="nav-link {{ $menuOpen ? 'active' : '' }}">
                <i class="nav-icon fas fa-table"></i>
                <p>
                    Masters
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @can('table-venue.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.table_venues') }}" class="nav-link {{ Request::segment(2)=='table-venues'?'active':'' }} {{ Request::segment(2)=='table-venue'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Venue</p>
                    </a>
                </li>
                @endcan

                @can('table-meal.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.table_meals') }}" class="nav-link {{ Request::segment(2)=='table-meals'?'active':'' }} {{ Request::segment(2)=='table-meal'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Meal</p>
                    </a>
                </li>
                @endcan

                @can('table-time.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.table_times') }}" class="nav-link {{ Request::segment(2)=='table-times'?'active':'' }} {{ Request::segment(2)=='table-time'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Time</p>
                    </a>
                </li>
                @endcan

                @can('table-master.manage')
                <li class="nav-item">
                    <a href="{{ route('admin.tables') }}" class="nav-link {{ Request::segment(2)=='tables'?'active':'' }} {{ Request::segment(2)=='table'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Table</p>
                    </a>
                </li>
                @endcan

            </ul>

        </li>
        @endcanany

        @can('table-booking.manage')
        <li class="nav-item">
            <a href="{{ route('admin.table_bookings') }}" class="nav-link {{ Request::segment(2)=='table-bookings'?'active':'' }} {{ Request::segment(2)=='table-booking'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Bookings
                </p>
            </a>
        </li>
        @endcan

        @can('mmr-registration-booking.manage')
        <li class="nav-item">
            <a href="{{ route('admin.mmr_registration.list') }}" class="nav-link {{ Request::segment(2)=='mmr-registration-enquery'?'active':'' }} {{ Request::segment(2)=='mmr-registration-enquery'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    MMR Registration List
                </p>
            </a>
        </li>
        @endcan

        @can('mmr-registration-setting.manage')
        <li class="nav-item">
            <a href="{{ route('admin.mmr_registration.setting') }}" class="nav-link {{ Request::segment(2)=='mmr-registration'?'active':'' }} {{ Request::segment(2)=='mmr-registration-setting'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    MMR Registration Setting
                </p>
            </a>
        </li>
        @endcan

        <!-- Activity Menu -->

        @php
            $menuOpen = in_array(Request::segment(2), [
                'facilities',
                'facility',
                'slots',
                'slot',
                'activity-sessions',
                'activity-session',
                'activity-occupant-masters',
                'activity-occupant-master',
            ]);

        @endphp

        @if(auth()->guard('web')->user()->role=='Activity Manager' || auth()->guard('web')->user()->role=='Activity Admin')

        <li class="nav-item {{ $menuOpen ? 'menu-is-opening menu-open' : '' }}">
            <a href="#" class="nav-link {{ $menuOpen ? 'active' : '' }}">
                <i class="nav-icon fas fa-table"></i>
                <p>
                    Masters
                    <i class="fas fa-angle-left right"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
    
                <li class="nav-item">
                    <a href="{{ route('admin.facilities') }}" class="nav-link {{ Request::segment(2)=='facilities'?'active':'' }} {{ Request::segment(2)=='facility'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Facility</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.slots') }}" class="nav-link {{ Request::segment(2)=='slots'?'active':'' }} {{ Request::segment(2)=='slot'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Slot</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.activity_sessions') }}" class="nav-link {{ Request::segment(2)=='activity-sessions'?'active':'' }} {{ Request::segment(2)=='activity-session'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Session</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.activity_occupant_masters') }}" class="nav-link {{ Request::segment(2)=='activity-occupant-masters'?'active':'' }} {{ Request::segment(2)=='activity-occupant-master'?'active':'' }}">
                        <i class="far fa-circle nav-icon"></i>
                        <p>Occupant</p>
                    </a>
                </li>

            </ul>

        </li>

        <li class="nav-item">
            <a href="{{ route('admin.facility_slots') }}" class="nav-link {{ Request::segment(2)=='facility-slots'?'active':'' }} {{ Request::segment(2)=='facility-slot'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Facility Slots
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.block_slots') }}" class="nav-link {{ Request::segment(2)=='block-slots'?'active':'' }} {{ Request::segment(2)=='block-slot'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Block Slots
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.game_types') }}" class="nav-link {{ Request::segment(2)=='game-types'?'active':'' }} {{ Request::segment(2)=='game-type'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Game Types
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.activity_cancellation_policies') }}" class="nav-link {{ Request::segment(2)=='activity-cancellation-policies'?'active':'' }} {{ Request::segment(2)=='activity-cancellation-policy'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Cancellation Policies
                </p>
            </a>
        </li>

        <li class="nav-item">
            <a href="{{ route('admin.activity_bookings') }}" class="nav-link {{ Request::segment(2)=='activity-bookings'?'active':'' }} {{ Request::segment(2)=='activity-booking'?'active':'' }}">
                <i class="nav-icon fas fa-list"></i>
                <p>
                    Bookings
                </p>
            </a>
        </li>

        @endif

        <!-- Activity Menu End -->

        <li class="nav-item">
            <a href="{{ route('admin.admin_settings') }}" class="nav-link {{ Request::segment(2)=='admin-settings'?'active':'' }} {{ Request::segment(2)=='admin-setting'?'active':'' }}">
                <i class="nav-icon fas fa-cog"></i>
                <p>
                    Settings
                </p>
            </a>
        </li>

        <form action="{{ route('logout') }}" method="post" id="logout-form">
            @csrf
        </form>

    </ul>
</nav>
<!-- /.sidebar-menu -->