<?php

namespace App\Http\Controllers\Admin;
use App\Models\Event;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\SendFCMNotification;
use App\Models\Notifications;
use App\Models\Member;
use App\Models\NotificationUser; 
use Carbon\Carbon;
use App\Services\FCMService;
use App\Models\MemberReceipt;
use App\Models\EventTicket;
use App\Models\TicketBooking;
use App\Models\Participant;

use Log;
class TicketBookingController extends Controller
{
    public function create($id)
{
    $event = Event::with('ticketTypes')->findOrFail($id);

    return view('admin.events.create-ticket', compact('event'));
}

}