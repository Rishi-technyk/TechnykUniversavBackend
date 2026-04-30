<div class="card mb-1 h-100">
    <div class="card-body text-left ">
        
        @if(Auth::check() && Auth::user()->role=='Super Admin')
        <a href="{{ route('main.superadmin.dashboard') }}"><button class="btn-button {{ request()->segment(2)=='staff'?'active-btn':'' }}">Users</button></a>
        @endif

        @if(Auth::check() && Auth::user()->role=='Banquet Manager')
        <a href="{{ route('superadmin.dashboard') }}"><button class="btn-button {{ request()->segment(2)=='dashboard'?'active-btn':'' }}">Dashboard</button></a>
        <a href="{{ route('venue.master') }}"><button class="btn-button {{ request()->segment(2)=='venue-master'?'active-btn':'' }}">Venue Master</button></a>
        <a href="{{ route('venue.group') }}"><button class="btn-button {{ request()->segment(2)=='venue-group'?'active-btn':'' }}">Venue Groups</button></a>
        <a href="{{ route('venue.pax') }}"><button class="btn-button {{ request()->segment(2)=='venue-pax'?'active-btn':'' }}">Venue Pax</button></a>
        <a href="{{ route('session.master') }}"><button class="btn-button {{ request()->segment(2)=='session-master'?'active-btn':'' }}">Session Master</button></a>
        <a href="{{ route('occupant.master') }}"><button class="btn-button {{ request()->segment(2)=='occupant-master'?'active-btn':'' }}">Occupant Master</button></a>
        <a href="{{ route('function.master') }}"><button class="btn-button {{ request()->segment(2)=='function-master'?'active-btn':'' }}">Function Master</button></a>
        <a href="{{ route('venue.charge') }}"><button class="btn-button {{ request()->segment(2)=='venue-charges'?'active-btn':'' }}">Venue Charges</button></a>
        <a href="{{ route('venue.block') }}"><button class="btn-button {{ request()->segment(2)=='venue-block'?'active-btn':'' }}">Venue Block</button></a>
        <a href="{{ route('cancellation.policy') }}"><button class="btn-button {{ request()->segment(2)=='cancellation-policy'?'active-btn':'' }}">Cancellation Policy</button></a>
        <a href="{{ route('bookings') }}"><button class="btn-button {{ request()->segment(2)=='bookings'?'active-btn':'' }}">Bookings</button></a>
        <a href="{{ route('cancel.bookings') }}"><button class="btn-button {{ request()->segment(2)=='cancel-bookings'?'active-btn':'' }}">Cancel Bookings</button></a>
        <a href="{{ route('SOP') }}"><button class="btn-button {{ request()->segment(2)=='SOP'?'active-btn':'' }}">SOP</button></a>
        <a href="{{ route('admin.setting') }}"><button class="btn-button {{ request()->segment(2)=='admin-setting'?'active-btn':'' }}">Setting</button></a>
        @endif

        @if(Auth::check() && Auth::user()->role=='Room Manager')
        <a href="{{ route('superadmin.dashboard') }}"><button class="btn-button {{ request()->segment(2)=='dashboard'?'active-btn':'' }}">Dashboard</button></a>
        <a href="{{ route('category.master') }}"><button class="btn-button {{ request()->segment(2)=='category-master'?'active-btn':'' }}">Category Master</button></a>
        <a href="{{ route('category.type') }}"><button class="btn-button {{ request()->segment(2)=='category-type'?'active-btn':'' }}">Category Type</button></a>
        <a href="{{ route('occupant.master') }}"><button class="btn-button {{ request()->segment(2)=='occupant-master'?'active-btn':'' }}">Occupant Master</button></a>
        <a href="{{ route('room.category') }}"><button class="btn-button {{ request()->segment(2)=='room-category'?'active-btn':'' }}">Room Category</button></a>
        <a href="{{ route('room.charges.master') }}"><button class="btn-button {{ request()->segment(2)=='room-charges-master'?'active-btn':'' }}">Room Charges Master</button></a>
        <a href="{{ route('room.block') }}"><button class="btn-button {{ request()->segment(2)=='room-block'?'active-btn':'' }}">Room Block</button></a>
        <a href="{{ route('room.cancellation.policy') }}"><button class="btn-button {{ request()->segment(2)=='room-cancellation-policy'?'active-btn':'' }}">Room Cancellation Policy</button></a>
        <a href="{{ route('room.bookings') }}"><button class="btn-button {{ request()->segment(2)=='room-bookings'?'active-btn':'' }}">Room Bookings</button></a>
        <a href="{{ route('cancel.room.bookings') }}"><button class="btn-button {{ request()->segment(2)=='cancel-room-bookings'?'active-btn':'' }}">Cancel Bookings</button></a>
        <a href="{{ route('SOP') }}"><button class="btn-button {{ request()->segment(2)=='SOP'?'active-btn':'' }}">SOP</button></a>
        <a href="{{ route('admin.setting') }}"><button class="btn-button {{ request()->segment(2)=='admin-setting'?'active-btn':'' }}">Setting</button></a>
        @endif
        <a href="{{ url('webhook/payload') }}" target="_blank"><button class="btn-button">Sync Payment</button></a>
        
    </div>
</div>

