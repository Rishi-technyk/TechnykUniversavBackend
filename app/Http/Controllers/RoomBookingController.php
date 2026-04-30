<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\SOP;
use App\Models\Card;
use App\Models\CardItem;
use App\Models\Transaction;
use App\Models\RoomBooking;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use App\Models\MemberProfile;
use App\Models\OccupantMaster;
use App\Models\RoomBookingItem;
use App\Models\RoomChargesMaster;
use App\Models\RoomCategoryMaster;
use Illuminate\Support\Facades\Auth;
use App\Models\RoomCancellationPolicy;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class RoomBookingController extends Controller
{
    function room_booking(Request $request)
    {
        $startDate = Carbon::parse($request->check_in);

        $endDate = Carbon::parse($request->check_out);

        $member  = Auth::guard('student')->user();

        $daysDifference = $startDate->diffInDays($endDate);
       
        if($request && $request->member_name || $request->check_in ||  $request->check_out){

            if($request->check_out<=$request->check_in){

                Session::flash('message', 'Check-out date should be greater than the Check-in date.'); 
                return redirect()->route('room-booking.check.availability'); 

            } else {
                // Old Query 12/02/2026
                // $q = RoomChargesMaster::query();

                // $S = 'Active';

                // $q->whereHas('room_category', function ($query) use ($S) {
                //     $query->where('status', $S);
                // });

                // $q->where('status', $S);

                // if($member && $member->CategoryTypeCode)
                // $q->where('category_type_id', $member->CategoryTypeCode);

                // if($member && $member->CategoryCode)
                // $q->where('category_id', $member->CategoryCode);

                // $q->selectRaw('category_id, room_category_id, category_type_id, MAX(id) as id');

                // $roomss = $q->groupBy(['category_id','room_category_id','category_type_id'])->with('room_category')->get();
                // $rooms = $q->groupBy(['category_id','category_type_id','room_category_id'])->with('room_category')->get();

                $ids = RoomChargesMaster::whereHas('room_category', function ($query) {
                            $query->where('status', 'Active');
                        })
                        ->where('status', 'Active')
                        ->when($member && $member->CategoryTypeCode, function ($q) use ($member) {
                            $q->where('category_type_id', $member->CategoryTypeCode);
                        })
                        ->when($member && $member->CategoryCode, function ($q) use ($member) {
                            $q->where('category_id', $member->CategoryCode);
                        })
                        ->selectRaw('MAX(id) as id')
                        ->groupBy('category_id', 'room_category_id')
                        ->pluck('id');

                $roomss = RoomChargesMaster::with('room_category')
                            ->whereIn('id', $ids)
                            ->get();
                
                $rooms = [];
                
                $check_in_date_last = date('Y-m-d', strtotime('+1 day', strtotime($request->check_in)));

                $max_nitie = RoomChargesMaster::where('category_type_id', $member->CategoryTypeCode)->max('max_no_of_nites');

                foreach ($roomss as $key => $room) {

                    // return getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in);
                   
                    if(getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in)>'0'){
                        if($max_nitie && $max_nitie>=$daysDifference){
                            array_push($rooms, $room); 
                        } else {
                            array_push($rooms, $room); 
                        }
                    }
                }

                $status = true;
            }
           
        } else {

            $rooms = [];

            $status = false;

            $check_in_date_last = '';

            $max_nitie = '';
        }        

        $SOP = SOP::where('type', 'Room Booking')->first();

        $setting = AdminSetting::first();

        if($setting && $setting->min_days && $setting->max_days){

            $from_date = Carbon::now()->addDays($setting->min_days)->toDateString();

            $to_date = Carbon::now()->addDays($setting->max_days)->toDateString();

        } else {

            $from_date = '';
            $to_date = '';

        }

        $datas = Card::where('memberID', $member->MemberID)->first();

        $occupant = OccupantMaster::where('status', 'Active')->get();

        // CardItem::where('card_id', $datas?$datas->id:'')->delete();

        // Card::where('memberID', $member->MemberID)->delete();       
     
        return view('frontend.rooms.room_booking', compact('member', 'request', 'rooms', 'SOP', 'setting', 'from_date', 'to_date', 'status', 'check_in_date_last', 'daysDifference', 'max_nitie', 'occupant'));
    }

    function room_details(Request $request ,$id)
    {
        $id = decrypt($id);

        $member  = Auth::guard('student')->user();

        $room = RoomChargesMaster::with('room_category')->where('id', $id)->first();

        $occupant = OccupantMaster::where('status', 'Active')->get();
        
        return view('frontend.rooms.room_details', compact('room','request','occupant','member'));
        
    }

    public function check_occupant_room(Request $request)
    {
        $occupan = OccupantMaster::where('status', 'Active')->where('id', $request->occup_val)->first();

        $room_charge = RoomChargesMaster::where('category_id', $request->cat_val)->where('category_type_id', $request->cat_typ_val)->where('occupant_type_id', $request->occup_val)->where('room_category_id', $request->rm_cat_val)->first();

        return response()->json(['data'=>$occupan, 'room_charge'=>$room_charge]);
    }

    public function store_card(Request $request)
    {
        $member  = Auth::guard('student')->user();
        
        if(checkRoomInCard($request->category_id, $request->category_type_id, $request->room_category_id, $member->MemberID)){
            return response()->json([
                'status' => false,
                'message' => 'This room is already booked in your cart.'
            ]);
        }

        $checkCard = Card::where('memberID', $member->MemberID)->exists();

        if($checkCard){

            $card = Card::where('memberID', $member->MemberID)->first();

        } else {

            $bookingID = date('dmY').'-'.rand(9999,100000);

            $params['booking_number']   = $bookingID;
            $params['memberID']         = $member->MemberID;
            $params['chartID']          = $member->SC_ID;
            $params['checkin']          = $request->check_in.' '.env('CheckIn',null);
            $params['checkout']         = $request->check_out.' '.env('CheckOut',null);

            $card = Card::create($params);
        }

        if($card){

            $date1=date_create($request->check_in);
            $date2=date_create($request->check_out);
            $diff=date_diff($date1,$date2);
            $in_days = str_replace('+','',$diff->format("%R%a"));

            $paramss['card_id']             = $card->id;
            $paramss['category_id']         = $request->category_id;
            $paramss['category_type_id']    = $request->category_type_id;
            $paramss['room_category_id']    = $request->room_category_id;
            $paramss['occupant_id']         = $request->occupant_type;
            $paramss['no_of_rooms']         = $request->booked_room_no;
            $paramss['no_of_days']          = $in_days;
            $paramss['room_charges']        = $request->room_charge;
            $paramss['adult']               = $request->adult;
            $paramss['child']               = $request->child;
            $paramss['guest_name']          = $request->guest_name;
            $paramss['guest_email']         = $request->guest_email;
            $paramss['guest_mobile']        = $request->guest_mobile;

            $room_charges_total = ($request->room_charge*$request->booked_room_no)*$in_days;

            $GST_a = ($request->gst / 100) * $room_charges_total; 

            $paramss['gst_per']             = $request->gst;
            $paramss['gst_amount']          = $GST_a;
            $paramss['room_charge_total']   = $room_charges_total+$GST_a;

            $res = CardItem::create($paramss);

            if($res){
                return response()->json([
                    'status' => true,
                    'message' => 'Room booked successfully'
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Room booking failed'
                ]);
            }

        }

        return response()->json([
            'status' => false,
            'message' => 'Room booking failed'
        ]);
    }

    function room_booking_card()
    {
        $member  = Auth::guard('student')->user();

        $card = Card::where('memberID', $member->MemberID)->first();

        $card_items = CardItem::where('card_id', $card?$card->id:'')->get();

        $card_total = '0';

        return view('frontend.rooms.room_booking_card', compact('card', 'card_items', 'card_total'))->render();;
    }

    function remove_room_from_card(Request $request)
    {
        $card_item = CardItem::where('id', $request->card_item_id)->first();

        if($card_item){
            $card_item->delete();

            $remaining_items = CardItem::where('card_id', $card_item->card_id)->count();
            if($remaining_items == 0){
                Card::where('id', $card_item->card_id)->delete();
            }

            return response()->json([
                'status' => true,
                'message' => 'Room removed from card successfully'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Room removal failed'
        ]);
    }

    function room_booking_summary()
    {
        $member  = Auth::guard('student')->user();

        $datas = Card::where('memberID', $member->MemberID)->first();
       
        $data_items = CardItem::where('card_id', $datas?$datas->id:'')->get();
        
        return view('frontend.rooms.booking_summary',compact('datas', 'data_items', 'member'));
    }

    function cancel_room_item(Request $request)
    {
        $card = CardItem::where('id', $request->bookingID)->first();

        CardItem::where('id', $request->bookingID)->delete();

        $cards = CardItem::where('card_id', $card->card_id)->count();

        if($cards == 0){

            Card::where('id', $card->card_id)->delete();

        }

        return true;    
    }

    public function cancel_room_item_booking(Request $request)
    {
        $member  = Auth::guard('student')->user();

        $cards = Card::where('memberID', $member->MemberID)->first();

        CardItem::where('card_id', $cards->id)->where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('room_category_id', $request->room_category_id)->delete();

        $card = CardItem::where('card_id', $cards->id)->where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('room_category_id', $request->room_category_id)->get();

        if(empty($card)){

            Card::where('id', $cards->id)->delete();

        }

        return true;
    }

    public function empty_card($card_id='')
    {
        $card = Card::where('booking_number', $card_id)->first();

        CardItem::where('card_id', $card->id)->delete();

        Card::where('booking_number', $card_id)->delete();

        return redirect()->back()->with('message', 'Card emptied successfully');
    }

    public function checkout_card($card_id)
    {

        $card = Card::where('booking_number', $card_id)->first();

        $member  = Auth::guard('student')->user();

        if($card){

            $params['booking_number']   = $card->booking_number;
            $params['memberID']         = $card->memberID;
            $params['memberName']       = $member->DisplayName;
            $params['chartID']          = $card->chartID;
            $params['checkin']          = $card->checkin;
            $params['checkout']         = $card->checkout;

            $booking = RoomBooking::create($params);

            if($booking){

                $amount = '0';

                foreach (CardItem::where('card_id', $card->id)->get() as $key => $value) {
                    
                    $paramss['booking_id']          = $booking->id;
                    $paramss['category_id']         = $value->category_id;
                    $paramss['category_type_id']    = $value->category_type_id;
                    $paramss['room_category_id']    = $value->room_category_id;
                    $paramss['occupant_id']         = $value->occupant_id;
                    $paramss['no_of_rooms']         = $value->no_of_rooms;
                    $paramss['no_of_days']          = $value->no_of_days;
                    $paramss['room_charges']        = $value->room_charges;
                    $paramss['room_charge_total']   = $value->room_charge_total;
                    $paramss['adult']               = $value->adult;
                    $paramss['child']               = $value->child;
                    $paramss['guest_name']          = $value->guest_name;
                    $paramss['guest_email']         = $value->guest_email;
                    $paramss['guest_mobile']        = $value->guest_mobile;
                    $paramss['gst_per']             = $value->gst_per;
                    $paramss['gst_amount']          = $value->gst_amount;

                    $res = RoomBookingItem::create($paramss);

                    $amount += $value->room_charge_total;

                }
                $order_id = 'RB'.date('YmdHis').rand(999,10000);

                $paramst['order_id']        = $order_id; 

                $paramst['amount']          = $amount; 

                $paramst['member_id']       = $member?$member->SC_ID:'';

                $paramst['transID']         = $card->booking_number;

                $paramst['type']            = 'Room Booking'; 

                $paramst['payment_type']    = 'ICICI';
                
                $paramst['entry_come']      = 'Web';

                $paramst['room_booking_id'] = $booking->id;
                
                Transaction::create($paramst);

                $data['member'] = $member;

                CardItem::where('card_id', $card->id)->delete();

                Card::where('booking_number', $card_id)->delete();
                
                return redirect()->route('billdesk.pay', ['order_id' => encrypt($order_id)]);
            }

        } else {

            return back();

        }
    }

    public function get_room_item_front(Request $request)
    {
        $id = $request->booking_id;

        $member  = Auth::guard('student')->user();
        
        $datas = Card::where('id', $id)->first();

        $data_items = CardItem::where('card_id', $id)->get();

        $view = view('frontend.rooms.booking_summary_table',compact('member', 'datas', 'data_items'))->render();

        return $view;
    }

    public function room_transactions(Request $request)
    {
        $member = Auth::guard('student')->user();

        if($request && $request->memberID || $request->memberName || $request->booking_no || $request->checkIn || $request->checkOut){

            $q = RoomBooking::query();            

            if($request->memberID){
                $q->where('memberID', $request->memberID);
            }

            if($request->memberName){
                $q->where('memberName', $request->memberName);
            }

            if($request->booking_no){
                $q->where('booking_number', $request->booking_no);
            }

            if($request->checkIn){
                $q->where('checkin', '>=', $request->checkIn.' '.env('CheckIn',null));
            }

            if($request->checkOut){
                $q->where('checkout', '<=', $request->checkOut.' '.env('CheckOut',null));
            }

            $datas = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(20);

        } else {

            $datas = RoomBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(20);

        }

        return view('frontend.transaction.room_traction',compact('member', 'datas', 'request'));
    }

    public function room_booking_details($id='')
    {
        $member = Auth::guard('student')->user();
        
        $id = decrypt($id);

        $datas = RoomBooking::where('id', $id)->first();

        $data_items = RoomBookingItem::where('booking_id', $id)->get();

        $transaction = Transaction::where('transID', $datas->booking_number)->first();
        
        return view('frontend.rooms.room_booking_details',compact('member', 'datas', 'data_items', 'transaction'));
    }

    public function room_details_download($id='')
    {
        $datas['member'] = Auth::guard('student')->user();
        
        $datas['datas'] = RoomBooking::where('id', $id)->first();

        $datas['data_items'] = RoomBookingItem::where('booking_id', $id)->get();

        $datas['transaction'] = Transaction::where('transID', $datas['datas']->booking_number)->first();

        $datas['setting'] = AdminSetting::first();
        
        // return view('website.pages.room_booking.room_details_download',$datas);

        $pdf = PDF::loadView('frontend.rooms.room_details_download', $datas);

        return $pdf->download('Room-Booking.pdf');
    }

    public function room_booking_cancel($id='')
    {
        $member = Auth::guard('student')->user();   

        $id = decrypt($id);

        $datas = RoomBooking::where('id', $id)->first();

        $data_items = RoomBookingItem::where('booking_id', $id)->get();

        $transaction = Transaction::where('transID', $datas->booking_number)->first();

        $prev_bookings = RoomBookingItem::where('booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $latest_bookings = RoomBookingItem::where('booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();

        return view('frontend.rooms.room_booking_cancel',compact('member', 'datas', 'data_items', 'transaction', 'prev_bookings', 'latest_bookings'));
    }

    public function cancelRoom(Request $request)
    {
        $item = RoomBookingItem::find($request->bookingID);

        $booking = RoomBooking::where('id', $item->booking_id)->first();

        $in_days = $item->no_of_days;

        $startDate = new \DateTime($booking->checkin);

        $first_date = [];

        // Loop through the next 15 days
        for ($i = 0; $i < $in_days; $i++) {
            // Print the current date in 'Y-m-d' format
            $fdt = $startDate->format('Y-m-d'); 
            array_push($first_date, $fdt);
            // Move to the next day
            $startDate->modify('+1 day');
        }

        $cdate = date('Y-m-d');

        $deduction = [];

        $deduction_Amt = '0';

        $new_width = '0';

        foreach ($first_date as $key => $t_date) {
            
            $startTimeStamp = strtotime($t_date);

            $endTimeStamp = strtotime($cdate);

            $timeDiff = abs($endTimeStamp - $startTimeStamp);

            $numberDays = $timeDiff/86400; 
              
            $numberDays = intval($numberDays);

            $policys = RoomCancellationPolicy::get();

            $policy = '';

            if($policys){

                foreach ($policys as $key => $ploy) {
                    
                    if($numberDays >= $ploy->from_days && $numberDays <= $ploy->to_days){

                        $policy = $ploy;

                    }
                }
            }

            if(isset($policy)){

                $percentage = $policy->GST;

                $totalWidth = $item->room_charges;

                $balaance_Amt = ($policy->deduction / 100) * $totalWidth;

                $new_width += ($percentage / 100) * $balaance_Amt; 

                $p_deduction = $policy->deduction;

                $deduction_Amt += $balaance_Amt;

            } else {

                $p_deduction = '0';

                $percentage = '0';

            }

            array_push($deduction, $p_deduction);

        }

        $params['cancellation_per']         = $deduction;
        $params['cancellation_amt']         = $deduction_Amt;
        $params['cancellation_GST']         = $percentage;
        $params['cancellation_GST_amt']     = $new_width;
        $params['cancellation_deducation']  = $deduction_Amt+$new_width;
        $params['cancellation_date']        = date('Y-m-d H:i:s');
        $params['status']                   = 'Cancelled';

        $res = RoomBookingItem::whereId($item->id)->update($params);

        $checkIi = RoomBookingItem::where('booking_id', $item->booking_id)->where('status', 'Active')->get();

        if(count($checkIi)=='0'){

            $rparams['status'] = 'Cancelled';

            RoomBooking::where('id', $item->booking_id)->update($rparams);

        }

        return true;
        
    }

    public function get_room_item(Request $request)
    {
        $id = $request->booking_id;

        $member = Auth::guard('student')->user();

        $datas = RoomBooking::where('id', $id)->first();

        $datas['transaction'] = Transaction::where('transID', $datas->booking_number)->first();

        $datas['datas'] = $datas;

        $datas['data_items'] = RoomBookingItem::where('booking_id', $id)->get();

        $datas['transaction'] = Transaction::where('transID', $datas->booking_number)->first();

        $datas['prev_bookings'] = RoomBookingItem::where('booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $datas['latest_bookings'] = RoomBookingItem::where('booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();
        
        $view = view('frontend.rooms.room_cancel_table', $datas)->render();

        return $view;
    }

    // Backend Functions

    function index(Request $request)
    {
        $q = RoomBooking::query();            

        if($request->member_id){
            $q->where('memberID', $request->member_id);
        }
        
        if($request->status){
            $q->where('status', $request->status);
        }

        if($request->booking_no){
            $q->where('booking_number', $request->booking_no);
        }

        if($request->checkIn){
            $q->where('checkin', '>=', $request->checkIn.' '.env('CheckIn',null));
        }

        if($request->checkOut){
            $q->where('checkout', '<=', $request->checkOut.' '.env('CheckOut',null));
        }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('backend.room_booking.index', $data);
    }

    function details($id)
    {
        $id = decrypt($id);

        $room = RoomBooking::where('id', $id)->first();

        $data['data'] = $room;

        $data['member'] = MemberProfile::where("MemberID",$room->memberID)->first();
        
        $data['items'] = RoomBookingItem::where('booking_id', $id)->get();

        $data['transaction'] = Transaction::where('transID', $room->booking_number)->first();

        return view('backend.room_booking.details', $data);
        
    }
}
