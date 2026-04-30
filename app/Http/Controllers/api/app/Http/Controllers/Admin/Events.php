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
class Events extends Controller
{
public function index(Request $request)
{
    $query = Event::query();

    if ($request->search) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $events = $query->latest()->paginate(10);
    return view('admin.events.index', compact('events'));
}
public function getBanners(Request $request)
{
    $query = Event::query()->select(['name','banner','id']);

    if ($request->search) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    $events = $query->latest()->paginate(10);
    return view('admin.events.banners', compact('events'));
}

public function updateBanner(Request $request, $id)
{
    $request->validate([
        'banner' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
    ]);

    $event = Event::findOrFail($id);

    if ($request->hasFile('banner')) {

        $file = $request->file('banner');
    
        $imageName = time().'_'.$event->id.'.'.$file->getClientOriginalExtension();
        $file->move(public_path('banners'), $imageName);

        $event->banner = $imageName;
        $event->save();
    }

    return redirect()->back()->with('success','Banner updated successfully');
}

public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'event_date' => 'required',
        'booking_start_at' => 'required',
        'booking_end_at' => 'required',
        'max_tickets' => 'required|integer',
        'max_per_member_tickets' => 'required|integer',
        'image' => 'required|image',
        'complimentary_age'=>'required|integer'
    ]);

    if ($request->hasFile('image')) {
        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('event'), $imageName);
    }

    Event::create([
        'name' => $request->name,
        'location' => $request->location,
        'event_date' => $request->event_date,
        'booking_start_at' => $request->booking_start_at,
        'booking_end_at' => $request->booking_end_at,
        'max_tickets' => $request->max_tickets,
        'max_per_member_tickets' => $request->max_per_member_tickets,
        'complimentary_age'=>$request->complimentary_age ?? 0,
        'gst' => $request->gst ?? 0,
        'service_charge' => $request->service_charge ?? 0,
        'image' => $imageName,
        'status' => 'active'
    ]);

    return back()->with('success', 'Event Created Successfully');
}

public function updateStatus(Request $request)
{
    $event = Event::find($request->id);

    if (!$event) {
        return response()->json([
            'success' => false,
            'message' => 'Event not found'
        ]);
    }

    // Update event status
    $event->status = $request->status;
    $event->save();

    // Increment dashboard version
    DB::table('app_config')
        ->where('config_key', 'dashboard_version')
        ->increment('config_value');

    return response()->json([
        'success' => true,
        'message' => 'Status updated successfully'
    ]);
}
public function updatePass(Request $request, $id)
{
    $pass = TicketType::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'amount' => 'required|numeric',
        'max_per_member' => 'required|integer',
        
    ]);

    $data = [
        'name' => $request->name,
        'amount' => $request->amount,
        'max_per_member' => $request->max_per_member,
    ];

    if ($request->hasFile('image_background')) {

        // Delete old image
        if ($pass->image_background &&
            file_exists(public_path('passes/'.$pass->image_background))) {
            unlink(public_path('passes/'.$pass->image_background));
        }

        $file = $request->file('image_background');

        // Create custom clean filename
        $customName = Str::slug($request->name) . '_' . time() . '.jpg';

        $destinationPath = public_path('passes/' . $customName);

        // Compress + Resize
        $image = Image::make($file);

        // Optional resize (max width 1200px)
        $image->resize(1200, null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        // Save compressed image (75 quality)
        $image->save($destinationPath, 75);

        $data['image_background'] = $customName;
    }

    $pass->update($data);

    return back()->with('success', 'Pass Updated Successfully');
}
 public function edit($id)
{
    $event = Event::findOrFail($id);
    return view('admin.events.edit', compact('event'));
}

   public function update(Request $request, $id)
{
    $event = Event::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'event_date' => 'required',
        'booking_start_at' => 'required',
        'booking_end_at' => 'required',
        'max_tickets' => 'required|integer',
        'complimentary_age'=>'required|integer',
        'max_per_member_tickets' => 'required|integer'
    ]);

    if ($request->hasFile('image')) {

        // Delete old image
        if ($event->image && file_exists(public_path('event/'.$event->image))) {
            unlink(public_path('event/'.$event->image));
        }

        $imageName = time().'_'.$request->image->getClientOriginalName();
        $request->image->move(public_path('event'), $imageName);

        $event->image = $imageName;
    }
    $event->update([
        'name' => $request->name,
        'location' => $request->location,
        'event_date' => $request->event_date,
        'booking_start_at' => $request->booking_start_at,
        'booking_end_at' => $request->booking_end_at,
        'max_tickets' => $request->max_tickets,
        'complimentary_age'=>$request->complimentary_age ?? 0,
        'max_per_member_tickets' => $request->max_per_member_tickets,
        'gst' => $request->gst ?? 0,
        'service_charge' => $request->service_charge ?? 0,
    ]);

    return redirect()->route('admin.events')->with('success','Event Updated Successfully');
}

    public function status(Request $request)
    {
        $id = $request->id;
        $status = $request->status; // Change active_status to status

        $notification = Notifications::find($id);
        if ($notification) {
            $notification->active_status = $status;
            $notification->save();
        }else{
            return redirect()->back()->with('message','notification not found');
        }
    }

    public function delete($id)
    {
        $notification = Notifications::findOrFail($id);
        $notification->delete();
        return redirect()->route('notifications')->with('success','Notification delete successfully!');
    }
    
     public function tickets(Request $request, $id)
{
    $event = Event::with('ticketTypes')->findOrFail($id);

    // ðŸ”½ ADD THIS LINE
    $events = Event::orderBy('event_date','desc')->get();

    $query = TicketBooking::with(['member','participants.ticketType','waiterBooking','seats.seat'])
        ->where('event_id', $id);

    // ðŸ”Ž Search
    // 🔎 Search
    if ($request->search) {

        $search = trim($request->search);

        $query->where(function ($q) use ($search, $id) {

            // 1️⃣ Booking Number
            $q->where('booking_no', 'like', "%{$search}%");

            // 2️⃣ Name / Mobile
            $q->orWhere(function ($sub) use ($search) {
                $sub->where('Name', 'like', "%{$search}%")
                    ->orWhere('Mobile', 'like', "%{$search}%");
            });

            // 3️⃣ Member Table Search
            $q->orWhereHas('member', function ($m) use ($search) {
                $m->where('DisplayName','like',"%{$search}%")
                  ->orWhere('MemberID','like',"%{$search}%")
                  ->orWhere('SC_ID','like',"%{$search}%");
            });

        });
    }



    // ðŸ’³ Payment Filter
    if ($request->payment_status) {
        $query->where('payment_status', $request->payment_status);
    }

    // ðŸ“… Date Filter
    if ($request->from_date && $request->to_date) {
        $query->whereBetween('created_at', [
            $request->from_date,
            $request->to_date
        ]);
    }

    $bookings = $query->latest()->paginate(15);
  $bookings->getCollection()->transform(function ($booking) {

    $booking->seat_codes = $booking->seats
        ->pluck('seat.seat_code')
        ->filter()
        ->values()
        ->toArray();

    return $booking;

});
$totalSold = Participant::whereHas('booking', function ($q) use ($id) {
    $q->where('event_id', $id)
      ->whereIn('payment_status', ['paid','admin']);
})->count();


$totalComp = Participant::whereHas('booking', function ($q) use ($id) {
    $q->where('event_id', $id)
      ->where('is_complimentary', 1)
      ->whereIn('payment_status', ['paid','admin']);
})->count();

$ticketTypeCounts = Participant::whereHas('booking', function ($q) use ($id) {
    $q->where('event_id', $id)
      ->whereIn('payment_status', ['paid','admin']);
})
->select('ticket_type', DB::raw('count(*) as total'))
->groupBy('ticket_type')
->pluck('total', 'ticket_type')
->toArray();
$ticketBreakdown = [
    'member' => $ticketTypeCounts['member'] ?? 0,
    'spouse' => $ticketTypeCounts['spouse'] ?? 0,
    'vip' => $ticketTypeCounts['vip'] ?? 0,
    'guest' => $ticketTypeCounts['guest'] ?? 0,
    'dependent'=>$ticketTypeCounts['dependent'] ?? 0,
];
$totalRevenue = TicketBooking::where('event_id',$id)
    ->whereIn('payment_status', ['paid','admin'])
    ->sum('total_amount');
    return view('admin.events.tickets',
        compact('event','events','bookings','totalSold','totalRevenue','totalComp','ticketBreakdown'));
}

   public function passes($id)
{
    $event = Event::findOrFail($id);

    $passes = TicketType::where('event_id',$id)
        ->latest()
        ->paginate(10);
  $types = [
        'member',
        'spouse',
        'dependent',
        'guest',
        'vip',
    ];
    return view('admin.events.passes',
        compact('event','passes','types'));
}
public function redirectToLatestEventBookings()
{
    $event = Event::orderBy('event_date','desc')->first();

    if (!$event) {
        return back()->with('error','No event found');
    }

    return redirect()->route('admin.events.tickets', $event->id);
}
public function getWaiters()
{
    $event = Event::orderBy('event_date','desc')->first();

    if (!$event) {
        return back()->with('error','No event found');
    }

    return redirect()->route('admin.events.waiters', $event->id);
}
public function waiters($id)
{
    $event = Event::findOrFail($id);

    $waiter = EventWaiter::firstOrCreate(
        ['event_id' => $id],
        [
            'max_waiters' => 0,
            'max_waiters_per_member' => 0,
            'waiter_cost' => 0,
            'status' => 'inactive'
        ]
    );

    $events = Event::orderBy('event_date','desc')->get();

    return view('admin.events.waiters',
        compact('event','events','waiter'));
}
public function updateWaiters(Request $request, $id)
{
    $request->validate([
        'max_waiters' => 'required|integer|min:0',
        'max_waiters_per_member' => 'required|integer|min:0',
        'waiter_cost' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive'
    ]);

    EventWaiter::updateOrCreate(
        ['event_id' => $id],
        $request->only([
            'max_waiters',
            'max_waiters_per_member',
            'waiter_cost',
            'status'
        ])
    );

    return back()->with('success','Waiter settings updated successfully');
}
public function storePass(Request $request, $id)
{
    $request->validate([
        'name' => 'required',
        'type' => 'required',
        'amount' => 'required|numeric',
        'max_per_member' => 'required|integer'
    ]);

    $imageName = null;

    if($request->hasFile('image_background')){
        $imageName = time().'_'.$request->image_background->getClientOriginalName();
        $request->image_background->move(public_path('passes'),$imageName);
    }

    TicketType::create([
        'event_id' => $id,
        'name' => $request->name,
        'type' => $request->type,
        'amount' => $request->amount,
        'max_per_member' => $request->max_per_member,
        'image_background' => $imageName,
        'status' => 'active'
    ]);

    return back()->with('success','Pass Created');
}
public function passesLanding()
{
    $events = Event::orderBy('event_date','desc')->get();
    return view('admin.events.passes-landing', compact('events'));
}


public function updatePassStatus(Request $request)
{
    $pass = TicketType::findOrFail($request->id);

    $pass->status = $request->status; // 1 or 0
    $pass->save();

    return response()->json(['success' => true]);
}

public function deletePass($id)
{
    $pass = TicketType::findOrFail($id);

    if($pass->image_background && file_exists(public_path('passes/'.$pass->image_background))){
        unlink(public_path('passes/'.$pass->image_background));
    }

    $pass->delete();

    return back()->with('success','Pass Deleted');
}


public function exportTickets(Request $request, $eventId)
{
    $reportDate  = $request->query('date');
    $ticketType  = $request->query('ticket_type');
    $entryStatus = $request->query('entry_status');
    $foodStatus  = $request->query('food_status');

    $bookings = TicketBooking::with([
        'participants.ticketType',
        'member'
    ])
    ->where('payment_status', '!=', 'failed')
    ->where('event_id', $eventId)
    ->where('total_amount', '>', 0)
    ->when($reportDate, function ($q) use ($reportDate) {
        $q->whereDate('created_at', $reportDate);
    })
    ->orderBy('created_at','asc')
    ->get();

    $rows = [];

    foreach ($bookings as $booking) {

        foreach ($booking->participants as $p) {

            // Skip complimentary or zero amount
            if ($p->is_complimentary || $p->amount <= 0) {
                continue;
            }

            // Apply filters
            if ($ticketType && $p->ticket_id != $ticketType) {
                continue;
            }

            if ($entryStatus !== null && $p->entry_status != $entryStatus) {
                continue;
            }

            if ($foodStatus !== null && $p->food_status != $foodStatus) {
                continue;
            }
\Log::info($p);
            $rows[] = [
                $booking->booking_no,
                $booking->created_at->format('d-m-Y'),
                $booking->created_at->format('h:i A'),
                $booking->member->DisplayName ?? '-',
                $p->ticketType->name ?? 'N/A',
                $p->name,
                $p->entry_status ? 'Yes' : 'No',
                $p->food_status ? 'Yes' : 'No',
                number_format($p->amount,2),
                ucfirst($booking->payment_status),
                $booking->payment_type,
                $booking->razorpay_payment_id,
                $booking->razorpay_order_id,
            ];
        }
    }

    return Excel::download(
        new EventTicketExport($rows),
        'event_summary_'.$eventId.'.xlsx'
    );
}
}
