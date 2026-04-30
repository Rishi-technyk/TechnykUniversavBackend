<?php

namespace App\Http\Controllers\Admin;
use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendFCMNotification;
use App\Models\Notifications;
use App\Models\Member;
use DB;
use App\Models\NotificationUser; 
use Carbon\Carbon;
use App\Services\FCMService;
use App\Models\MemberReceipt;
use App\Models\EventTicket;
use App\Models\TicketBooking;
use App\Models\TicketType;
use App\Models\EventWaiter;
use App\Models\Participant;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EventTicketExport;

use Log;
class AdminUsersController extends Controller
{
public function getStaff()
{
    $roles = ['Event Admin','EventEntry','EventDrinks','EventFood'];

    $users = Member::whereIn('role',$roles)
        ->select('id','MemberID','DisplayName','SC_ID','Mobile','role','status')
        ->get();

    return view('admin.events.staff',compact('users'));
}

public function storeStaff(Request $request)
{
    $request->validate([
        'MemberID' => 'required|string|max:50|unique:memberprofile,MemberID',
        'DisplayName' => 'required|string|max:100',
        'Mobile' => 'required|digits:10|unique:memberprofile,Mobile',
        'role' => 'required|string'
    ]);

    Member::create([
        'MemberID'    => $request->MemberID,
        'SC_ID'       => $request->MemberID,
        'state'       => 1,
        'city'        => 1,
        'Password'    => 'Temp@1234',
        'DisplayName' => $request->DisplayName,
        'Mobile'      => $request->Mobile,
        'role'        => $request->role
    ]);

    return back()->with('success','Staff Created Successfully');
}
public function updateStaff(Request $request,$id)
{
    $request->validate([
        'DisplayName' => 'required|string|max:100',
        'Mobile' => 'required|digits:10|unique:memberprofile,Mobile,'.$id,
        'role' => 'required|string'
    ]);

    $user = Member::findOrFail($id);

    $user->update([
        'DisplayName' => $request->DisplayName,
        'Mobile'      => $request->Mobile,
        'role'        => $request->role
    ]);

    return back()->with('success','Staff Updated Successfully');
}
}