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
class AdminTicketController extends Controller
{
public function index($id)
{
    $event = Event::with(['ticketTypes','waiter'])->findOrFail($id);

    $members = Member::select('id','MemberID','DisplayName')
        ->orderBy('MemberID')
        ->get();

$payment_type = [

            [
                'id' => 1,
                'name' => 'UPI',
                'type_code' => 'upi',
                'icon' => 'qrcode-scan', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay instantly using UPI (GPay, PhonePe, Paytm).',
                'ref_no'=>true
            ],

            [
                'id' => 2,
                'name' => 'Cash',
                'type_code' => 'cash',
                'icon' => 'cash-multiple', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using physical cash.',
                 'ref_no'=>false
            ],

            [
                'id' => 3,
                'name' => 'Credit Card',
                'type_code' => 'credit_card',
                'icon' => 'credit-card', // MaterialIcons
                'icon_type' => 'MaterialIcons',
                'description' => 'Pay using your credit card.',
                 'ref_no'=>true
            ],

            [
                'id' => 4,
                'name' => 'Debit Card',
                'type_code' => 'debit_card',
                'icon' => 'credit-card-outline', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using debit card.',
                 'ref_no'=>true
            ],

            [
                'id' => 5,
                'name' => 'Net Banking',
                'type_code' => 'netbanking',
                'icon' => 'bank-transfer', // MaterialCommunityIcons
                'icon_type' => 'MaterialCommunityIcons',
                'description' => 'Pay using your bank net banking.',
                 'ref_no'=>true
            ],

        ];
    return view('admin.events.createbooking', compact('event','members','payment_type'));
}
}