<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        <!-- <li class="nav-item">

            <a class="nav-link" href="{{ route('admin.dashboard') }}">

                <i class="bi bi-grid"></i>

                <span>Dashboard</span>

            </a>

        </li> -->

       <!--



        <li class="nav-item">

            <a class="nav-link collapsed" data-bs-target="#members-nav" data-bs-toggle="collapse" href="#">

                <i class="bi bi-people"></i><span>Members</span><i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <ul id="members-nav" class="nav-content collapse {{ request()->is('admin/member') ? 'show' : ''}}" data-bs-parent="#sidebar-nav">

                <li>

                    <a href="{{route('admin.members')}}" class={{ (request()->route()->getName()) === 'admin.members' ? 'active' : '' }}>

                        <i class="bi bi-circle"></i><span>List</span>

                    </a>

                </li>

                {{-- <li>

                    <a href="">

                        <i class="bi bi-circle"></i><span>Bookings</span>

                    </a>

                </li> --}}

            </ul>

        </li>

       



        <li class="nav-item">

            <a class="nav-link collapsed" data-bs-target="#rooms-nav" data-bs-toggle="collapse" href="#">

                <i class="bi bi-house"></i><span>Rooms</span><i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <ul id="rooms-nav" class="nav-content collapse {{ request()->is('admin/room*') ? 'show' : ''}}"

                data-bs-parent="#sidebar-nav">

                <li>

                  

                    <a href="#" class="third-level-nav">             
                        <div class="accordion" id="accordionExample">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="headingOne">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">Add</button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">option</div>
                                </div>
                            </div>
                        </div>
                    </a>
                </li>

                <li>

                    <a href="{{ route('admin.roomList') }}">

                        <i class="bi bi-circle"></i><span>View</span>

                    </a>

                </li>

                <li>

                    <a href="{{ route('admin.roomPriceList') }}"

                        class="{{ request()->is('admin/room/price*') ? 'active' : '' }}">

                        <i class="bi bi-circle"></i><span>Manage Rates</span>

                    </a>

                </li>

                <li>

                    <a href="#">

                        <i class="bi bi-circle"></i><span>Bookings</span>

                    </a>

                </li>

            </ul>

        </li>

      



         <li class="nav-item">

            <a class="nav-link collapsed" data-bs-target="#venues-nav" data-bs-toggle="collapse" href="#">

                <i class="bi bi-buildings"></i><span>Venues</span><i class="bi bi-chevron-down ms-auto"></i>

            </a>

            <ul id="venues-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">

                <li>

                    <a href="forms-elements.html">

                        <i class="bi bi-circle"></i><span>Add</span>

                    </a>

                </li>

                <li>

                    <a href="forms-layouts.html">

                        <i class="bi bi-circle"></i><span>Bookings</span>

                    </a>

                </li>

            </ul>

        </li> -->

        <li class="nav-item">

<a class="nav-link collapsed" data-bs-target="#teeBooking-nav" data-bs-toggle="collapse" href="#">

    <i class="bi bi-vignette"></i><span>Tee Booking</span><i class="bi bi-chevron-down ms-auto"></i>
   
</a>

<ul id="teeBooking-nav" class="nav-content collapse {{ request()->is('admin/tee*') ? 'show' : ''}}" data-bs-parent="#sidebar-nav">

    <!--<li >-->
    <!--    <a class="{{ request()->is('admin/tee/session_manage*') ? 'active' : ''}}" href="{{ route('session_manage') }}">-->
    <!--        <i class="bi bi-circle"></i><span>Session Manage</span>-->
    <!--    </a>-->
    <!--</li>-->
    <!--<li>-->
    <!--    <a class="{{ request()->is('admin/tee/tee-session-times*') ? 'active' : ''}}" href="{{ route('tee-session-times.index') }}">-->
    <!--        <i class="bi bi-circle"></i><span>Sessions</span>-->
    <!--    </a>-->
    <!--</li>-->
    <li>
        <a class="{{ request()->is('admin/tee/sessions*') ? 'active' : ''}}" href="{{ route('sessions.index') }}">
            <i class="bi bi-circle"></i><span>Sessions</span>
        </a>
    </li>
    <!-- <li >
        <a class="{{ request()->is('admin/tee/service_manage*') ? 'active' : ''}}" href="{{ route('service_manage') }}">
            <i class="bi bi-circle"></i><span>Services</span>
        </a>
    </li> -->
    
    <li>
        <a class="{{ request()->is('admin/tee/tee_holes*') ? 'active' : ''}}" href="{{ route('tee_holes.index') }}">
            <i class="bi bi-circle"></i><span>Tee Holes</span>
        </a>
    </li>
    <!--<li>-->
    <!--    <a class="{{ request()->is('admin/tee/tee_slot_intervals*') ? 'active' : ''}}" href="{{ route('tee_slot_intervals.index') }}">-->
    <!--        <i class="bi bi-circle"></i><span>Slot Intervals</span>-->
    <!--    </a>-->
    <!--</li>-->
    <!-- <li>
        <a class="{{ request()->is('admin/tee/transportations*') ? 'active' : ''}}" href="{{ route('transportations.index') }}">
            <i class="bi bi-circle"></i><span>Transportation</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/tee/caddies*') ? 'active' : ''}}" href="{{ route('caddies.index') }}">
            <i class="bi bi-circle"></i><span>Caddies</span>
        </a>
    </li> 
    <li>
        <a class="{{ request()->is('admin/tee/rental_clubs*') ? 'active' : ''}}" href="{{ route('rental_clubs.index') }}">
            <i class="bi bi-circle"></i><span>Rental Clubs</span>
        </a>
    </li>-->
    <li>
        <a class="{{ request()->is('admin/tee/tee_bookings*') ? 'active' : ''}}" href="{{ route('tee_bookings.index') }}">
            <i class="bi bi-circle"></i><span>Tee Bookings</span>
        </a>
    </li>
    <li>
        <a class="{{ request()->is('admin/tee/config*') ? 'active' : ''}}" href="{{ route('tee_bookings.config') }}">
            <i class="bi bi-circle"></i><span>Booking Hours</span>
        </a>
    </li>
    <!--<li>-->
    <!--    <a class="{{ request()->is('admin/tee/tee_sheets*') ? 'active' : ''}}" href="{{ route('tee_sheets.index') }}">-->
    <!--        <i class="bi bi-circle"></i><span>Tee Sheets</span>-->
    <!--    </a>-->
    <!--</li>-->


</ul>

</li>

<li class="nav-item">

<a class="nav-link collapsed" data-bs-target="#notification-nav" data-bs-toggle="collapse" href="#">

    <i class="bi bi-circle"></i><span>Notification</span><i class="bi bi-chevron-down ms-auto"></i>

</a>

<ul id="notification-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">

    <!-- <li>

        <a href="forms-elements.html">

            <i class="bi bi-circle"></i><span>Add</span>

        </a>

    </li> -->

    <li>

        <a 
         class="{{ request()->is('admin/notification*') ? 'active' : ''}}" 
          href="{{route('notifications')}}">

            <i class="bi bi-circle"></i><span>Notifications</span>

        </a>

    </li>
    <!-- <li>-->

    <!--    <a -->
    <!--     class="{{ request()->is('admin/notification/reminders*') ? 'active' : ''}}" -->
    <!--      href="{{route('reminders')}}">-->

    <!--        <i class="bi bi-circle"></i><span>Bill Reminders</span>-->

    <!--    </a>-->

    <!--</li>-->

</ul>

</li>
       





        <!-- <li class="nav-heading">Demo Category</li>



        <li class="nav-item">

            <a class="nav-link collapsed" href="#">

                <i class="bi bi-person"></i>

                <span>Profile</span>

            </a>

        </li>

       



        <li class="nav-item">

            <a class="nav-link collapsed" href="#">

                <i class="bi bi-question-circle"></i>

                <span>F.A.Q</span>

            </a>

        </li>

       



        <li class="nav-item">

            <a class="nav-link collapsed" href="#">

                <i class="bi bi-envelope"></i>

                <span>Contact</span>

            </a>

        </li> -->

<li class="nav-item">
    <a class="nav-link {{ request()->is('admin/events*') ? '' : 'collapsed' }}"
       data-bs-target="#events-nav"
       data-bs-toggle="collapse"
       href="#">
        <i class="bi bi-circle"></i><span>Events</span>
        <i class="bi bi-chevron-down ms-auto"></i>
    </a>

    <ul id="events-nav"
        class="nav-content collapse {{ request()->is('admin/events*') ? 'show' : '' }}"
        data-bs-parent="#sidebar-nav">

        <li>
            <a class="{{ request()->routeIs('events') ? 'active' : '' }}"
               href="{{ route('events') }}">
                <i class="bi bi-circle"></i>
                <span>Event Info</span>
            </a>
        </li>

        <li>
            <a href="{{ route('admin.events.passes.landing') }}"
               class="{{ request()->routeIs('admin.events.passes*') ? 'active' : '' }}">
                <i class="bi bi-circle"></i>
                <span>Passes</span>
            </a>
        </li>
         <li>
         <a href="{{ route('admin.events.waiters.landing') }}"
       class="{{ request()->routeIs('admin.events.waiters*') ? 'active' : '' }}">
        <i class="bi bi-circle"></i>
        <span>Waiters</span>
    </a>
    </li>
      <li>
         <a href="{{ route('admin.events.banners') }}"
       class="{{ request()->routeIs('admin.events.banners*') ? 'active' : '' }}"> 
        <i class="bi bi-circle"></i>
        <span>Banners</span>
    </a>
    </li>
       <li>
    <a href="{{ route('admin.events.bookings.redirect') }}"
       class="{{ request()->routeIs('admin.events.tickets') ? 'active' : '' }}">
        <i class="bi bi-circle"></i>
        <span>Event Bookings</span>
    </a>
</li>

<li>
    <a href="{{ route('admin.events.seating.redirect') }}"
       class="{{ request()->routeIs('admin.events.seating.*') ? 'active' : '' }}">
        <i class="bi bi-layout-text-window"></i>
        <span>Event Seating</span>
    </a>
</li>

<li>
    <a href="{{ route('admin.events.staff') }}"
       class="{{ request()->routeIs('admin.events.staff*') ? 'active' : '' }}">
        <i class="bi bi-layout-text-window"></i>
        <span>Admin / Staff</span>
    </a>
</li>
    </ul>
</li>


    </ul>

</aside>