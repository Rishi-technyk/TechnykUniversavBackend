<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\{
        FacilityGameType,
        GameType, 
        Facility, 
        ActivityBooking, 
        ActivityCard, 
        ActivityCardGuestInfo, 
        ActivityCardItem, 
        ActivitySession, 
        AdminSetting,
        FacilitySlot,
        MemberProfile,
        GuestInfo,
        OccupantMaster,
        GameBooking,
        GameBookingSlot,
        GameBookingGuest,
        Session,
        ActivityCancellationPolicy        
    };
use Carbon\Carbon;
use DB;

class ActivityBookingController extends Controller
{
    function index(Request $request)
    {
        $member = Auth::guard('student')->user();

        $facility = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = ActivitySession::where('status', 'Active')->get();

        $gametypes = GameType::where('status', 'Active')->get();

        $card = ActivityCard::where('memberID', $member->MemberID)->get();

        foreach ($card as $key => $value) {
            
            ActivityCardItem::where('card_id', $value->id)->delete();
            ActivityCardGuestInfo::where('card_id', $value->id)->delete();
            $value->delete();
        }

        $setting = AdminSetting::first();

        if($setting && $setting->max_days){

            $max_days = Carbon::now()->addDays($setting->max_days)->toDateString();

        } else {

            $max_days = '';

        }

        return view('frontend.activity_booking.index',compact('member', 'facility', 'request', 'session', 'gametypes', 'max_days'));
    }

    function get_facility(Request $request)
    {
        $data['game_type'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $data['request'] = $request;

        $view = view('frontend.activity_booking.append.get_facility', $data)->render();

        return $view;
        
    }

    function select_facility(Request $request)
    {
        $member = Auth::guard('student')->user();

        foreach (ActivityCard::where('memberID', $member->MemberID)->get() as $key => $card) {
            ActivityCardGuestInfo::where('card_id', $card->id)->delete();
            ActivityCardItem::where('card_id', $card->id)->delete();
            ActivityCard::whereId($card->id)->delete();
        }

        $data['facility'] = Facility::where('id', $request->facility_id)->first();

        $f_g_t = FacilityGameType::where('facility_id', $request->facility_id)->get();

        $typeIds = [];

        foreach ($f_g_t as $key => $fgt) {
            
            array_push($typeIds, $fgt->game_type_id);

        }

        $datas['gametypes'] = GameType::where('status', 'Active')->whereIn('id', $typeIds)->get();

        $return['game_type'] = view('frontend.activity_booking.append.select_facility_game_type', $datas)->render();

        $return['view'] = view('frontend.activity_booking.append.select_facility', $data)->render();

        return $return;
    }

    public function get_slots(Request $request)
    {
        if($request->session=='All'){
            $data['facility_slots'] = FacilitySlot::where('facility_id', $request->facility_id)->get();
        } else {
            $data['facility_slots'] = FacilitySlot::where('session_id', $request->session)->where('facility_id', $request->facility_id)->get();
        }

        $setting = AdminSetting::first();

        if($setting && $setting->max_days){

            $max_date = Carbon::now()->addDays($setting->max_days)->toDateString();

        } else {

            $max_date = '';

        }

        $startDate = new \DateTime($request->slot_date);

        $first_date = [];

        $last_date = '';

        // Loop through the next 15 days
        for ($i = 0; $i < 4; $i++) {
            // Print the current date in 'Y-m-d' format
            $fdt = $startDate->format('Y-m-d'); 
            $last_date = $fdt;

            if($setting && $setting->max_days){

                if($max_date >= $fdt){

                    array_push($first_date, $fdt);

                }

            } else {
                array_push($first_date, $fdt);
            }
            
            // Move to the next day
            $startDate->modify('+1 day');
        }

        $data['first_date']     = $first_date;

        $current_date           = $first_date[0];

        $data['current_date']   = $current_date;

        $data['prev_date']      = date('Y-m-d', strtotime('-4 day', strtotime($current_date)));

        $data['last_date']      = date('Y-m-d', strtotime('+1 day', strtotime($last_date)));

        $data['facility_id']    = $request->facility_id;

        $data['card_id']        = $request->card_id;

        $view = view('frontend.activity_booking.append.slots_list', $data)->render();

        return $view;
    }

    public function add_game_in_session(Request $request)
    {
        
        $member = Auth::guard('student')->user();
        
        $facility = Facility::find($request->facility_id);

        $bookingID = date('dmY').'-'.rand(9999,100000);

        $params['booking_number']   = $bookingID;
        $params['memberID']         = $member->MemberID;
        $params['chartID']          = $member->SC_ID;
        $params['facility_id']      = $request->facility_id;
        $params['game_type_id']     = $request->game_type_id;
        $params['session_id']       = $request->session_id;
        
        if(ActivityCard::where('memberID', $member->MemberID)->where('facility_id', $request->facility_id)->exists()){

            ActivityCard::where('memberID', $member->MemberID)->where('facility_id', $request->facility_id)->update($params);

            $card = ActivityCard::where('memberID', $member->MemberID)->where('facility_id', $request->facility_id)->first();

        } else {

            $card = ActivityCard::create($params);

        }        

        if($card){

            $pparams['card_id']     = $card->id;
            $pparams['slot_id']     = $request->slot_id;
            $pparams['slot_date']   = $request->slot_date;

            if(ActivityCardItem::where('card_id', $card->id)->where('slot_id', $request->slot_id)->where('slot_date', $request->slot_date)->exists()){
                ActivityCardItem::where('card_id', $card->id)->where('slot_id', $request->slot_id)->where('slot_date', $request->slot_date)->delete();
            } else {
                ActivityCardItem::create($pparams);
            }

            $card_item = ActivityCardItem::where('card_id', $card->id)->count();

            $card_guest = ActivityCardGuestInfo::where('card_id', $card->id)->sum('occupant_charge');

            $facility_amt = $card_item*$facility->charge;

            $gst_amt = ($facility->GSTper / 100) * $facility_amt;

            // $guest_amt = $card_guest*$card_item;
            $guest_amt = $card_guest;
            
            $card_update['guest_total_amount'] = $guest_amt;
            $card_update['facility_amount']  = $facility->charge;
            $card_update['facility_gst_per'] = $facility->GSTper;
            $card_update['facility_gst_amt'] = $gst_amt;
            $card_update['facility_total']   = ($gst_amt+$facility_amt+$guest_amt);
            ActivityCard::whereId($card->id)->update($card_update);

            $data['status'] = true;
            $data['card_id'] = $card->id;

        } else {

            $data['status'] = false;
        }

        return $data;
    }

    public function get_summary_card(Request $request)
    {
        $data['game_type']          = GameType::whereId($request->game_type_id)->first();

        $data['card']               = ActivityCard::find($request->booking_id);

        $data['card_guest']         = ActivityCardGuestInfo::where('card_id', $request->booking_id)->whereNotNull('occupant_charge')->count();

        $slot_count                 = ActivityCardItem::where('card_id', $request->booking_id)->count();

        $data['card_items']         = ActivityCardItem::where('card_id', $request->booking_id)->get();

        $data['info_guest']         = ActivityCardGuestInfo::whereIn('id', function ($query) use ($request) {
                                                        $query->selectRaw('MAX(id)')
                                                            ->from('activity_card_guest_infos')
                                                            ->where('card_id', $request->booking_id)
                                                            ->groupBy('slot_date');
                                                    })->get();

        $data['check_guest']        = count($data['info_guest']);

        $data['slot_count']         = $slot_count;
        $data['slot_details']       = view('frontend.activity_booking.append.slot_info_summary', $data)->render();
        $data['amount_summary']     = view('frontend.activity_booking.append.amount_summary', $data)->render();
        $data['checkout_btn']       = view('frontend.activity_booking.append.checkout_btn', $data)->render();
        $data['guest_list']         = view('frontend.activity_booking.append.game_type', $data)->render();

        return $data;
    }

    public function get_game_type(Request $request)
    {

        $member = Auth::guard('student')->user();

        $card = ActivityCard::where('memberID', $member->MemberID)->where('facility_id', $request->facility_id)->first();

        if($card){

            $facility = Facility::find($request->facility_id);

            ActivityCardGuestInfo::where('card_id', $card->id)->delete();

            $card_item = ActivityCardItem::where('card_id', $card->id)->count();
            
            $card_guest = ActivityCardGuestInfo::where('card_id', $card->id)->sum('occupant_charge');

            $facility_amt = $card_item*$facility->charge;

            $gst_amt = ($facility->GSTper / 100) * $facility_amt;

            // $guest_amt = $card_guest*$card_item;

            $guest_amt = $card_guest;
            
            $card_update['guest_total_amount'] = $guest_amt;
            $card_update['facility_amount']  = $facility->charge;
            $card_update['facility_gst_per'] = $facility->GSTper;
            $card_update['facility_gst_amt'] = $gst_amt;
            $card_update['facility_total']   = ($gst_amt+$facility_amt+$guest_amt);
            ActivityCard::whereId($card->id)->update($card_update);
        }

        // $data['member'] = $member;

        $datas['game_type'] = GameType::where('id', $request->game_type)->first();

        $datas['card_items'] = ActivityCardItem::where('card_id', $card->id)->get();

        $data['view'] = view('frontend.activity_booking.append.game_type', $datas)->render();

        $data['card_id'] = $card?$card->id:'';

        return $data;
    }

    public function get_occupant(Request $request)
    {
        $data['game_type'] = GameType::where('id', $request->game_type_id)->first();

        $data['occupants'] = OccupantMaster::where('id', $request->occupant)->first();

        return $data;
    }

    public function guest_list(Request $request)
    {
        $member = Auth::guard('student')->user();

        $data['guests'] = GuestInfo::where('member_id', $member->id)->orderBy('id', 'DESC')->get();

        $data['members'] = MemberProfile::where('role','Student')->orderBy('id', 'DESC')->get();

        $data['guests_fev'] = GuestInfo::where('member_id', $member->id)->where('is_favorite','1')->orderBy('id', 'DESC')->get();

        $data['members_fev'] = MemberProfile::where('role','Student')->where('is_favorite','1')->orderBy('id', 'DESC')->get();

        $return['guest_list'] = view('frontend.activity_booking.append.guest_list', $data)->render();

        $return['member_list'] = view('frontend.activity_booking.append.member_list', $data)->render();

        $return['favorite_list'] = view('frontend.activity_booking.append.favorite_list', $data)->render();

        return $return;
    }

    public function guest_info(Request $request)
    {
        if($request->tab=='Member'){

            $data['player'] = MemberProfile::whereId($request->guest_id)->first();
            $data['from'] = 'member';

        } else {

            $data['player'] = GuestInfo::whereId($request->guest_id)->first();
            $data['from'] = 'guest';

        }

        return $data;
    }

    public function favorite_active(Request $request)
    {
        if(MemberProfile::whereId($request->id)->exists()){

            $data = MemberProfile::whereId($request->id)->first();

            if($data){

                if($data->is_favorite=='1'){
                    $is_favorite = '0';
                } else {
                    $is_favorite = '1';
                }
                $params['is_favorite'] = $is_favorite;

                $ress = MemberProfile::whereId($request->id)->update($params);

                if($ress){
                    $res['status'] = true;
                    $res['msg'] = '';
                } else {
                    $res['status'] = false;
                    $res['msg'] = 'Try Again.';
                }

            } else {
                $res['status'] = false;
                $res['msg'] = 'Data not found.';
            }

        } elseif (GuestInfo::whereId($request->id)->exists()) {
            
            $data = GuestInfo::whereId($request->id)->first();

            if($data){

                if($data->is_favorite=='1'){
                    $is_favorite = '0';
                } else {
                    $is_favorite = '1';
                }
                $params['is_favorite'] = $is_favorite;

                $ress = GuestInfo::whereId($request->id)->update($params);

                if($ress){
                    $res['status'] = true;
                    $res['msg'] = '';
                } else {
                    $res['status'] = false;
                    $res['msg'] = 'Try Again.';
                }

            } else {
                $res['status'] = false;
                $res['msg'] = 'Data not found.';
            }

        }

        return $res;
    }

    public function remove_player(Request $request)
    {
        return GuestInfo::whereId($request->id)->delete();
    }

    public function store_guest_in_table(Request $request)
    {
        $member = Auth::guard('student')->user();

        $card = ActivityCard::where('memberID', $member->MemberID)->where('facility_id', $request->facility_id)->first();

        if($card){

            if($request->slot_ids){

                // CardGuestInfo::where('card_id', $card->id)->delete();

                foreach ($request->slot_ids as $key => $slot_ids) {

                    $card_item = ActivityCardItem::where('card_id', $card->id)->where('slot_id', $slot_ids)->whereDate('slot_date', $request->slot_dates[$key])->first();

                    if(isset($request->occupant_ids[$key])){

                        $occupant = OccupantMaster::whereId($request->occupant_ids[$key])->first();

                        $ppparams['occupant_id']    = $request->occupant_ids[$key];
                        $ppparams['occupant_charge']= $occupant?$occupant->charge:null;

                    } else {

                        $ppparams['occupant_id']    = null;
                        $ppparams['occupant_charge']= null;

                    }
                    
                    $ppparams['card_id']        = $card->id;
                    $ppparams['card_item_id']   = $card_item->id;
                    $ppparams['slot_id']        = $slot_ids;
                    $ppparams['slot_date']      = $request->slot_dates[$key];
                    $ppparams['player_name']    = $request->player_names[$key];
                    $ppparams['player_email']   = $request->player_emails[$key];
                    $ppparams['player_mobile']  = $request->player_mobiles[$key];

                    if(ActivityCardGuestInfo::where('card_id', $card->id)->where('slot_id', $slot_ids)->whereDate('slot_date', $request->slot_dates[$key])->where('player_name',$request->player_names[$key])->exists()){

                        ActivityCardGuestInfo::where('card_id', $card->id)->where('slot_id', $slot_ids)->whereDate('slot_date', $request->slot_dates[$key])->where('player_name',$request->player_names[$key])->update($ppparams);

                    } else {

                        ActivityCardGuestInfo::create($ppparams);

                    }
                    
                }
            }

            $facility = Facility::find($request->facility_id);

            $card_item = ActivityCardItem::where('card_id', $card->id)->count();

            $card_guest = ActivityCardGuestInfo::where('card_id', $card->id)->sum('occupant_charge');

            $facility_amt = $card_item*$facility->charge;

            $gst_amt = ($facility->GSTper / 100) * $facility_amt;

            // Old calculation 01/07/2025 
            // $guest_amt = $card_guest*$card_item;

            $guest_amt = $card_guest;
            
            $card_update['guest_total_amount'] = $guest_amt;
            $card_update['facility_amount']  = $facility->charge;
            $card_update['facility_gst_per'] = $facility->GSTper;
            $card_update['facility_gst_amt'] = $gst_amt;
            $card_update['facility_total']   = ($gst_amt+$facility_amt+$guest_amt);
            $card_update['game_type_id']     = $request->game_type_id;

            ActivityCard::whereId($card->id)->update($card_update);

            $data['status'] = true;
            $data['card_id'] = $card->id;

        } else {
            $data['status'] = false;
        }

        return $data;
    }

    public function checkout_booking($id='')
    {
        $id = decrypt($id);

        $card = ActivityCard::find($id);

        $card_slot = ActivityCardItem::where('card_id', $id)->get();

        $card_guest = ActivityCardGuestInfo::where('card_id', $id)->get();

        $member = Auth::guard('student')->user();

        $params['booking_number']       = $card->booking_number;
        $params['memberID']             = $card->memberID;
        $params['chartID']              = $card->chartID;
        $params['facility_id']          = $card->facility_id;
        $params['game_type_id']         = $card->game_type_id;
        $params['session_id']           = $card->session_id;
        $params['facility_amount']      = $card->facility_amount;
        $params['guest_total_amount']   = $card->guest_total_amount;
        $params['facility_gst_per']     = $card->facility_gst_per;
        $params['facility_gst_amt']     = $card->facility_gst_amt;
        $params['facility_total']       = $card->facility_total;
        $params['status']               = 'Pending';
        $params['payment_status']       = 'Not Paid';

        $req = GameBooking::create($params);

        if($req){

            foreach ($card_slot as $key => $slot) {
                
                $pparams['game_booking_id']     = $req->id;
                $pparams['slot_id']             = $slot->slot_id;
                $pparams['slot_date']           = $slot->slot_date;

                GameBookingSlot::create($pparams);
            }

            foreach ($card_guest as $key => $guest) {

                $slot = GameBookingSlot::where('game_booking_id', $req->id)->where('slot_id', $guest->slot_id)->whereDate('slot_date', $guest->slot_date)->first();
                
                $ppparams['game_booking_id']    = $req->id;
                $ppparams['game_booking_slot_id']    = $slot->id;
                $ppparams['slot_id']            = $guest->slot_id;
                $ppparams['slot_date']          = $guest->slot_date;
                $ppparams['occupant_id']        = $guest->occupant_id;
                $ppparams['occupant_charge']    = $guest->occupant_charge;
                $ppparams['player_name']        = $guest->player_name;
                $ppparams['player_email']       = $guest->player_email;
                $ppparams['player_mobile']      = $guest->player_mobile;

                GameBookingGuest::create($ppparams);
            }
        
            $tparams['order_id']     = $card->booking_number; 

            $tparams['amount']       = $card->facility_total; 

            $tparams['type']         = 'Activity Booking'; 

            $tparams['member_id']    = $member?$member->SC_ID:'';

            $tparams['game_booking_id'] = $req->id; 

            DB::table('transactions')->insert($tparams);

            return redirect()->route('billdesk.pay', ['order_id' => encrypt($card->booking_number)]);

        } else {

            return back();
        }

    }

    public function modify_guest_list(Request $request)
    {
        $guest_info = ActivityCardGuestInfo::where('card_id', $request->cc_id)->where('id', $request->guest_info_id)->first();

        $member = Auth::guard('student')->user();

        $data['guests'] = GuestInfo::where('member_id', $member->id)->orderBy('id', 'DESC')->get();

        $data['members'] = MemberProfile::where('role','Student')->orderBy('id', 'DESC')->get();

        $data['guests_fev'] = GuestInfo::where('member_id', $member->id)->where('is_favorite','1')->orderBy('id', 'DESC')->get();

        $data['members_fev'] = MemberProfile::where('role','Student')->where('is_favorite','1')->orderBy('id', 'DESC')->get();

        $guest = ActivityCardGuestInfo::where('card_id', $request->cc_id)->where('id', '!=' , $request->guest_info_id)->where('card_item_id', $guest_info->card_item_id)->select('player_name')->get();

        $guest_names = [];

        foreach ($guest as $key => $value) {
            array_push($guest_names, $value->player_name);
        }

        $data['guest_names']        = $guest_names;

        $data['guest_info_id']      = $request->guest_info_id;

        $data['guest_info']         = ActivityCardGuestInfo::whereId($request->guest_info_id)->first();

        $return['guest_list']       = view('frontend.activity_booking.append.modify_guest_list', $data)->render();

        $return['member_list']      = view('frontend.activity_booking.append.modify_member_list', $data)->render();

        $return['favorite_list']    = view('frontend.activity_booking.append.modify_favorite_list', $data)->render();

        return $return;
    }

    public function update_modify_player(Request $request)
    {
        if( MemberProfile::whereId($request->guest_id)->exists()){

            $member = MemberProfile::whereId($request->guest_id)->first();

            $guest_name = $member->DisplayName;

        } else {

            $guest = GuestInfo::where('id', $request->guest_id)->first();

            $guest_name = $guest->name;

        }
        

        $card_guest_check = ActivityCardGuestInfo::where('card_id', $request->cc_id)->where('card_item_id', $request->card_guest_id)->where('player_name', $guest_name)->exists();

        if($card_guest_check){

            $return['status'] = false;
            $return['msg'] = 'This player is already exists. Please select another player.';

        } else {

            $facility = Facility::find($request->facility_id);
            
            if(MemberProfile::whereId($request->guest_id)->exists()){

                $member = MemberProfile::whereId($request->guest_id)->first();
                
                $params['occupant_id']          = null;
                $params['player_name']          = $member->DisplayName;
                $params['player_email']         = $member->Email;
                $params['player_mobile']        = $member->Mobile;
                $params['occupant_charge']      = null;

                ActivityCardGuestInfo::where('id', $request->card_guest_id)->update($params);

            } else {

                $guest = GuestInfo::where('id', $request->guest_id)->first();
                
                $occu = OccupantMaster::find($guest->occupant_id);

                $params['occupant_id']          = $guest->occupant_id;
                $params['player_name']          = $guest->name;
                $params['player_email']         = $guest->email;
                $params['player_mobile']        = $guest->mobile;
                $params['occupant_charge']      = $occu->charge ?? null;

                ActivityCardGuestInfo::where('id', $request->card_guest_id)->update($params);

            }

            $card_item = CardItem::where('card_id', $request->cc_id)->count();

            $card_guest = ActivityCardGuestInfo::where('card_id', $request->cc_id)->sum('occupant_charge');

            $facility_amt = $card_item*$facility->charge;

            $gst_amt = ($facility->GSTper / 100) * $facility_amt;

            $guest_amt = $card_guest;
            
            $card_update['guest_total_amount']  = $guest_amt;
            $card_update['facility_amount']     = $facility->charge;
            $card_update['facility_gst_per']    = $facility->GSTper;
            $card_update['facility_gst_amt']    = $gst_amt;
            $card_update['facility_total']      = ($gst_amt+$facility_amt+$guest_amt);
            ActivityCard::whereId($request->cc_id)->update($card_update);

            $return['status'] = true;

        }

        return $return;
    }

    public function booking_transactions(Request $request)
    {
        $member = Auth::guard('student')->user();

        if($request && $request->activity || $request->session || $request->slot_date){

            $q = GameBooking::query();            

            if($request->activity){
                $q->where('facility_id', $request->activity);
            }

            if($request->session){
                $q->where('session_id', $request->session);
            }

            $slot_date = $request->slot_date;
            if($slot_date){
                $q->whereHas('game_item', function ($query) use ($slot_date) {
                    $query->whereDate('slot_date', $slot_date);
                });
            }
            

            $data['datas'] = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(12);

        } else {

            $data['datas'] = GameBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(12);

        }
        
        $data['facility'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $data['session'] = ActivitySession::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $data['request'] = $request;

        return view('frontend.activity_booking.transactions', $data);
    }

    public function booking_transaction(Request $request)
    {
        $data['member']         = Auth::guard('student')->user();

        $data['data']           = GameBooking::where('id', $request->id)->first();

        $data['items']          = GameBookingSlot::where('game_booking_id', $request->id)->get();

        $data['cancel_sum']     = GameBookingSlot::where('game_booking_id', $request->id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $data['guests']         = GameBookingGuest::whereIn('id', function ($query) use ($request) {
                                                        $query->selectRaw('MAX(id)')
                                                            ->from('game_booking_guests')
                                                            ->where('game_booking_id', $request->id)
                                                            ->groupBy('slot_date');
                                                    })->get();

        $view = view('frontend.activity_booking.append.details', $data)->render();

        return $view;
    }

    public function cancel_slot(Request $request)
    {
        $policys = ActivityCancellationPolicy::where('facility_id', $request->fid)->get();

        if($policys){

            $item = GameBookingSlot::where('id', $request->id)->first();

            $booking = GameBooking::where('id', $item->game_booking_id)->first();

            $occupant_charge = GameBookingGuest::where('game_booking_slot_id', $item->id)->sum('occupant_charge');

            $cdate = date('Y-m-d');

            $startTimeStamp = strtotime($item->slot_date);

            $endTimeStamp = strtotime($cdate);

            $timeDiff = abs($endTimeStamp - $startTimeStamp);

            $numberDays = $timeDiff/86400; 
              
            $numberDays = intval($numberDays);

            $policy = '';

            if($policys){

                foreach ($policys as $key => $ploy) {
                    
                    if($numberDays >= $ploy->from_days && $numberDays <= $ploy->to_days){

                        $policy = $ploy;

                    }
                }
            }

            if(isset($policy)){

                $percentage = $policy->GST ?? '0';

                $totalWidth = $booking->facility_amount+$occupant_charge;

                $balaance_Amt = ($policy->deduction / 100) * $totalWidth;

                $new_width = ($percentage / 100) * $balaance_Amt; 

                $p_deduction = $policy->deduction ?? '0';

                $deduction_Amt = $balaance_Amt;

            } else {

                $p_deduction = '0';

                $percentage = '0';

                $new_width = '0';

                $deduction_Amt = '0';

            }

            $params['cancellation_per']         = $p_deduction;
            $params['cancellation_amt']         = $deduction_Amt;
            $params['cancellation_GST']         = $percentage;
            $params['cancellation_GST_amt']     = $new_width;
            $params['cancellation_deducation']  = ($deduction_Amt)+($new_width);
            $params['cancellation_date']        = date('Y-m-d H:i:s');
            $params['status']                   = 'Cancelled';

            $res = GameBookingSlot::whereId($request->id)->update($params);

            $paramss['status']  = 'Cancelled';

            GameBookingGuest::where('game_booking_slot_id', $request->id)->update($paramss);

            if($res){

                $checkSlot = GameBookingSlot::where('game_booking_id', $item->game_booking_id)->where('status', '!=', 'Cancelled')->count();

                if($checkSlot=='0'){

                    $rparams['status'] = 'Cancelled';

                    GameBooking::where('id', $item->game_booking_id)->update($rparams);
                    
                }

                $data['status'] = true;
                $data['booking_id'] = $item->game_booking_id;

            } else {
                $data['status'] = false;
                $data['msg'] = 'Try Again.';
            }

        } else {
            $data['status'] = false;
            $data['msg'] = 'Cancellation Policy Not Found.';
        }

        return $data;
    }

    public function guest_store(Request $request)
    {
        $occupant = OccupantMaster::where('name','Guest')->orWhere('name', 'GUEST')->first();

        $params['member_id']    = Auth::guard('student')->user()->id;
        $params['occupant_id']  = $occupant?$occupant->id:'';
        $params['name']         = $request->name;
        $params['email']        = $request->email;
        $params['mobile']       = $request->mobile;

        $res = GuestInfo::create($params);

        if($res){
            $status = true;
        } else {
            $status = false;
        }

        return $status;
    }

    // Backend Functions
    function admin_bookings(Request $request)
    {
        $q = GameBooking::query();            

        if($request->facility_id){
            $q->where('facility_id', $request->facility_id);
        }

        if($request->session){
            $q->where('session_id', $request->session);
        }

        if($request->status){
            $q->where('status', $request->status);
        }

        if($request->slot_date){
            $slot_date = $request->slot_date;
            $q->whereHas('game_item', function ($query) use ($slot_date) {
                $query->whereDate('slot_date', $slot_date);
            });
        }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['facility'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $data['session'] = Session::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('backend.activity.booking.index', $data);
    }

    public function booking_details($id='')
    {
        $data['datas']  = GameBooking::where('id', $id)->first();

        $data['items']  = GameBookingSlot::where('game_booking_id', $id)->get();

        $data['guests'] = GameBookingGuest::where('game_booking_id', $id)->get();

        $data['member'] = MemberProfile::where("MemberID",$data['datas']->memberID)->first();

        return view('backend.activity.booking.details', $data);
    }

}
