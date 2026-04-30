<?php
namespace App\Http\Controllers\web;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Models\TeeSessionCategory;
use Illuminate\Http\Request;
use App\Models\TeeSheet;
use App\Models\Member;
use App\Models\SOP;
use App\Models\MemberReceipt;
use App\Models\TeeMyBuddies;
use App\Models\TeeBookingDetails;
use App\Models\TeeHole;
use App\Models\TeeSession;
use App\Models\TeeGroup;
use App\Models\OccupantMaster;
use App\Models\FunctionMaster;
use App\Models\VenueMaster;
use App\Models\RoomBooking;
use App\Models\VenueCharge;
use App\Models\VenuePax;
use App\Models\BanquetBooking;
use App\Models\BanquetBookingCharges;
use App\Models\CancellationPolicy;
use Rap2hpoutre\FastExcel\FastExcel;
use DB;
use Crypt;
use Carbon\Carbon;
use Session;
use Auth;
use URL;
use PDF;
use App\Http\Controllers\web\hmac_sha256;
use App\Models\AdminSetting;
use Illuminate\Support\Facades\Artisan;

class MemberController extends Controller
{
   
 
    public function store_buddy(Request $request)
    {

        // if(auth()->user()->id == $request['buddy_member_id']){
        //     return response()->json(['error' => "Your self can not be add  as a buddy."], 400);
        // }
        $data = [
            "member_id"=>auth()->user()->id,
            'created_by' => auth()->user()->id, 
        ];
        
       $buddyExist =  TeeMyBuddies::where($data)->first();
        if ($buddyExist) {
            return response()->json(['error' => "This Member already added in your buddy list."], 400);
        } else{
            //dd($request);
            TeeMyBuddies::create($data);
        }

        return response()->json(['message' => 'Buddy added successfully']);   
    }

    public function store_group(Request $request)
    {

        $group_name=$request->group_name;
        $groupExists = TeeGroup::where('group_name', $group_name)->exists();

        if ($groupExists) {
            return response()->json(['error' => 'Group name already exists.'], 400);
        }

        
        $validator = $request->validate([
            'player6_id' => 'required|different:player7_id,player8_id,player9_id',
            'player7_id' => 'required|different:player6_id,player8_id,player9_id',
            'player8_id' => 'required|different:player6_id,player7_id,player9_id',
            'player9_id' => 'nullable|different:player6_id,player7_id,player8_id',
        ], [

            'player6_id.required' => 'Player 1 required for Tee Booking.',
            'player7_id.required' => 'Player 2 required for Tee Booking.',
            'player8_id.required' => 'Player 3 required for Tee Booking.',
            'player9_id.required' => 'Player 4 required for Tee Booking.',
            'player6_id.different' => 'Player 1 must be different from other players.',
            'player7_id.different' => 'Player 2 must be different from other players.',
            'player8_id.different' => 'Player 3 must be different from other players.',
            'player9_id.different' => 'Player 4 must be different from other players.',

        ]);

        $playerId1 = $request->input("player6_id");
        $playerId2 = $request->input("player7_id");

        if ($playerId1 == $playerId2) {
            return response()->json(['Player 3 already selected as Player1'], 400);
        }




        $playerIds = array_filter($request->only('player6_id', 'player7_id', 'player8_id', 'player9_id'));

        if (count($playerIds) < 3) {
            return response()->json(['error' => 'At least 3 players are required.'], 400);
        }
       

        	
        $teeGroup = new TeeGroup();
        $teeGroup->group_name=$request->group_name;
        $teeGroup->player1_id= $request->player6_id;
        $teeGroup->player2_id= $request->player7_id;
        $teeGroup->player3_id= $request->player8_id;
        $teeGroup->player4_id= $request->player9_id;
        $teeGroup->created_by= auth()->user()->id;
        $teeGroup->save();

        /*$teeBookingDetailId = $request->tee_booking_detail_id;
        foreach ($playerIds as $index => $playerIdField) {
            if ($request->has($playerIdField)) {
                $playerId = $request->input($playerIdField);
                if ($playerId) {
                    $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())
                        ->whereNot("id", $teeBookingDetailId)
                        ->where("is_cancelled", 0)
                        ->where(function ($query) use ($playerId) {
                            $query->where('player1_id', $playerId)
                                ->orWhere('player2_id', $playerId)
                                ->orWhere('player3_id', $playerId)
                                ->orWhere('player4_id', $playerId);
                        })
                        ->exists();

                    if ($bookingExists) {
                        $playerNumber = $index + 1; // Get the player number (1-indexed)
                        return response()->json(['error' => "Player $playerNumber has already booked a slot on the same day."], 400);
                    }
                }
            }
        }*/
       



        return response()->json(['message' => 'TeeBookingDetails created successfully']);
    }

    
    public function delete($id)
    {
        TeeMyBuddies::where("id",$id)->delete();
        return redirect()->back()->with('success', 'Buddy deleted successfully!');
    }
    public function delete_group($id)
    {
        TeeGroup::where("id",$id)->delete();
        return redirect()->back()->with('success', 'Group deleted successfully!');
    }
 

    public function autocompleteBuddy(Request $request)
    {
        //$query = $request->query('name');
        $userInput = $request->query('userInput');
        //$teeSheetId = $request->query('teeSheetId');
        //$selectedSessionId = TeeSheet::select('session_id')->where("id",$teeSheetId)->first()->session_id;

        //$categoryCodes = TeeSessionCategory::where("tee_session_id",$selectedSessionId)->pluck('category_type_Code')->toArray();

        //$categoryCodes = TeeSessionCategory::where("tee_session_id",1)->pluck('category_type_Code')->toArray();
        $membersObj = Member::where(function ($query) use ($userInput) {
            $query->where('DisplayName', 'like', "%$userInput%")
                ->orWhere('MemberID', '=', $userInput);
        })
        ->select(
            'memberprofile.id',
            'memberprofile.DisplayName as name',
            'memberprofile.MemberID',
            'memberprofile.CategoryCode',
            'memberprofile.Status',

        )
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryCode')
        ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryCode')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
        ->distinct()
        ->get();
    
   
        $StatusArray = ["Direct Out Station","In Station","Out Station"];
      
        $response = [];
        foreach ($membersObj as $member) {
            if(in_array($member->Status,$StatusArray)){
            $newAre = array();
            $newAre['value'] = $member->id;
            $newAre['label'] = trim($member->name) . "/" . $member->MemberID;
            $response[] = $newAre;
            }
        }

        return response()->json($response);

        // return response()->json($members);
    }

    public function banquet_form()
    {

        $occupan = OccupantMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $function = FunctionMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        
        Session::forget('session_array');

        $v_s_array = [];

        Session::put('session_array', $v_s_array);

        $setting = AdminSetting::first();

        if($setting && $setting->min_days && $setting->max_days){

            $from_date = Carbon::now()->addDays($setting->min_days)->toDateString();

            $to_date = Carbon::now()->addDays($setting->max_days)->toDateString();

        } else {

            $from_date = '';
            $to_date = '';

        }

        $SOP = SOP::where('type', 'Banquet Booking')->first();

        return view('website.pages.banquet_form',compact('member','occupan', 'session', 'vanue', 'function', 'setting', 'from_date', 'to_date', 'SOP'));
    }

    public function banquet_traction(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        if($request && $request->function_date || $request->booking_no){

            $q = BanquetBooking::query();

            if($request->function_date){
                $q->whereDate('funDate', $request->function_date);
            }

            if($request->booking_no){
                $q->where('booking_ID', $request->booking_no);
            }

            $datas = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

        } else {

            $datas = BanquetBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

        }
        
        return view('website.pages.banquet_traction',compact('member', 'datas', 'request'));
    }

    public function banquet_details($id='')
    {
        $id = decrypt($id);

        $data = BanquetBooking::find($id);

        $datas['datas'] = $data;

        $datas['transaction'] = DB::table('transactions')->where('transID', $data->booking_ID)->where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        return view('website.pages.banquet_details', $datas);
    }

    public function banquet_details_download($id='')
    {
        
        $data = BanquetBooking::find($id);

        $datas['datas'] = $data;

        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['transaction'] = DB::table('transactions')->where('transID', $data->booking_ID)->where('banquet_booking_id', $id)->first();

        $datas['setting'] = AdminSetting::first();

        // return view('website.pages.banquet_details_download', $datas);

        $pdf = PDF::loadView('website.pages.banquet_details_download', $datas);

        return $pdf->download('Banquet.pdf');

    }

    public function banquet_cancel($id='')
    {
        $id = decrypt($id);
        
        $datas['datas'] = BanquetBooking::find($id);

        $datas['transaction'] = DB::table('transactions')->where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['prev_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $datas['latest_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();
        
        return view('website.pages.banquet_cancel', $datas);
    }

    public function cancelVenue($id)
    {
        $id = decrypt($id);
        
        $banq_booking = BanquetBooking::find($id);

        if($banq_booking){

            $bookings = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

            foreach ($bookings as $key => $booking) {

                $policys = CancellationPolicy::where('venue_id', $booking->vanue_id)->get();

                if(count($policys)){

                    $cdate = date('Y-m-d');
                                                      
                    $startTimeStamp = strtotime($booking->funDate);

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

                        $percentage = $policy->GST;
                        $totalWidth = $booking->charges;

                        $balaance_Amt = ($policy->deduction / 100) * $totalWidth;

                        $new_width = ($percentage / 100) * $balaance_Amt;

                        $params['cancellation_per']         = $policy->deduction;
                        $params['cancellation_amt']         = $balaance_Amt;
                        $params['cancellation_GST']         = $percentage;
                        $params['cancellation_GST_amt']     = $new_width;
                        $params['cancellation_deducation']  = $balaance_Amt+$new_width;
                        $params['cancellation_date']        = date('Y-m-d H:i:s');
                        $params['status']                   = 'Cancelled';

                        $res = BanquetBookingCharges::whereId($booking->id)->update($params);

                        $ban_params['status'] = 'Cancelled';

                        BanquetBooking::whereId($id)->update($ban_params);

                    } else {

                        return redirect()->back()->with('error', 'Cancellation policy is not available for this venue');

                    }

                } else {

                    $data['msg'] = 'Cancellation policy is not available for this venue';
                    $data['status'] = false;

                    return redirect()->back()->with('error', 'Cancellation policy is not available for this venue.');
                }
            }

            return redirect()->back()->with('success', 'Venue Cancelled successfully!');

        } else {

            return redirect()->back()->with('error', 'Booking Not Found.');

        }

        
        
    }

    public function getBookingVenue(Request $request)
    {
        $id = $request->booking_id;

        $datas['transaction'] = DB::table('transactions')->where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['prev_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $datas['latest_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();
        
        $view = view('website.pages.banquet_cancel_venue', $datas)->render();

        return $view;
    }

    public function check_occupant(Request $request)
    {
        $occupan = OccupantMaster::where('status', 'Active')->where('id', $request->occ_id)->first();

        return response()->json(['data'=>$occupan]);
    }

    public function append_extra_field(Request $request)
    {
        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $rand = rand(10,100);

        $html = view('website.pages.append_extra_field',compact('vanue', 'session', 'rand'))->render();

        return response()->json(['html'=>$html, 'rand'=>$rand]);
    }

    public function remove_extra_field(Request $request)
    {
        $charges = VenueCharge::where('venue_id', $request->venue)->where('session_id', $request->session)->where('occupant_id', $request->occupant)->first();

        if($charges){

            $v_s_array = [];

            foreach (Session::get('session_array') as $key => $id) {
                if($id != $charges->id){
                    array_push($v_s_array, $id);
                }
            }

            Session::put('session_array', $v_s_array);
        }       

    }

    public function get_venue_by_session(Request $request)
    {
        if($request->group_id)
        $datas['vanue'] = VenueMaster::where('status', 'Active')->where('group_id', $request->group_id)->orderBy('id', 'DESC')->get();
        else
        $datas['vanue'] = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();


        $datas['request'] = $request;

        $view = view('website.pages.venue_by_session', $datas)->render();

        return $view;
    }

    public function get_charges(Request $request)
    {
        $charges = VenueCharge::where('venue_id', $request->venue)->where('session_id', $request->session)->where('occupant_id', $request->occupant)->first();

        $venue = VenueMaster::find($request->venue);

        if($request->function_date){

            $checkBooking = BanquetBooking::whereDate('funDate', '=', $request->function_date)->first();

            if($checkBooking){

                $checkVenue = BanquetBookingCharges::where('status', 'Active')->where('banquet_booking_id', $checkBooking->id)->where('vanue_id', $request->venue)->where('session_id', $request->session)->count();

                if($checkVenue){

                    return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'Booking', 'checkVenue'=>'']);

                } else {

                    $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

                    return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert]);

                }

            } else {

                $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

                return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert]);

            }

        } else {

            $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

            return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert]);

        }

        
    }

    public function get_venue_pax(Request $request)
    {

        $pax = VenuePax::get();

        $pax_info_id = ''; 

        foreach ($pax as $key => $px) {
            
            if($px->min_pax<=$request->noofPerson && $px->max_pax>=$request->noofPerson){

                $pax_info_id = $px->id;
                break;

            }
        }

        if(isset($pax_info_id)){

            $paxx = VenuePax::where('id', $pax_info_id)->first();

            if($paxx){

                $response['pax'] = $paxx;
                $response['status'] = 'true';

            } else {

                $response['status'] = 'true';

            }

        } else {

            $response['status'] = 'true';

        }

        return $response;
    }

    public function banquet_store(Request $request)
    {
        $bookingID = date('dmY').'-'.rand(9999,100000);
     
        $params['occupant_type']    = $request->occupant_type;
        $params['memberID']         = $request->memberID;
        $params['cardID']           = $request->SC_ID;
        $params['memberName']       = $request->memberName;
        $params['memberMobile']     = $request->memberMobile;
        $params['memberEmail']      = $request->memberEmail;
        $params['address']          = $request->address;
        $params['funDate']          = $request->funDate;
        $params['functionType']     = $request->functionType;
        $params['noofPerson']       = $request->noofPerson;
        $params['remark']           = $request->remark;
        $params['booking_ID']       = $bookingID;

        $res = BanquetBooking::create($params);

        if($res){

            $total_amt = '0';
            
            if($request->session_id && $request->vanue_id){

                foreach ($request->session_id as $key => $session) {

                    if($session && isset($request->vanue_id[$key])){
                    
                        $input['banquet_booking_id'] = $res->id;
                        $input['session_id']    = $session;
                        $input['vanue_id']      = $request->vanue_id[$key];
                        $input['gst_amount']    = $request->gst_amount[$key];;
                        $input['gst_per']       = $request->gst_per[$key];;
                        $input['charges']       = $request->charges[$key];
                        $input['security_deposit'] = $request->security_deposit[$key];
                        $input['total']         = $request->total[$key];
                        $input['funDate']       = $request->funDate;

                        BanquetBookingCharges::create($input);

                        $total_amt += $request->total[$key];

                    }
                }
            }

            // $this->banquet_payment_checkout($params);

            return redirect()->route('banquet.payment.checout', encrypt($res->id));

        } else {

            return redirect()->bach()->with('error', 'Try Again.');
        }


    }

    public function banquet_ability(Request $request)
    {
        $data['session'] = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        if($request->date || $request->session){

            if($request->session){
                $data['charges'] = VenueCharge::select('venue_id', 'session_id')->groupBy(['venue_id','session_id'])->where('session_id', $request->session)->get();
            } else {
                $data['charges'] = VenueCharge::select('venue_id', 'session_id')->groupBy(['venue_id','session_id'])->get();
            }

            $startDate = new \DateTime($request->date);

            $first_date = [];

            // Loop through the next 15 days
            for ($i = 0; $i < 15; $i++) {
                // Print the current date in 'Y-m-d' format
                $fdt = $startDate->format('Y-m-d'); 
                array_push($first_date, $fdt);
                // Move to the next day
                $startDate->modify('+1 day');
            }

            $data['first_date'] = $first_date;

            $data['today_date'] = $request->date;
            
        } else {

            $data['today_date'] = date('Y-m-d');

            $data['charges'] = [];

            $data['first_date'] = [];

            $data['venue_check'] = [];

        }

        $data['request'] = $request;

        return view('website.pages.banquet_ability', $data);
    }

    public function member_profile(){
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        return view('website.pages.member_profile',compact('member'));
    }
    public function member_edit(){
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        return view('website.pages.member_edit',compact('member'));
    }
    public function update(Request $request)
{
    // $request->validate([
    //     'DOB' => 'required|date',
    //     'SpouseName' => 'required|string',
    //     'SpouseDOB' => 'required|date',
    //     'AnniversaryDate' => 'required|date',
    //     'Email' => 'required|email',
    //     'Mobile' => 'required|numeric',
    //     'state' => 'required|string',
    //     'city' => 'required|string',
    //     'pin' => 'required|numeric',
    //     'Address' => 'required|string',
    // ]);

    $member = Member::findOrFail(auth()->user()->id);

    // Update member details individually
    $member->DOB = $request->input('DOB');
    $member->SpouseName = $request->input('SpouseName');
    $member->SpouseDOB = $request->input('SpouseDOB');
    $member->AnniversaryDate = $request->input('AnniversaryDate');
    $member->Email = $request->input('Email');
    $member->Mobile = $request->input('Mobile');
    //$member->state = $request->input('state');
    //$member->city = $request->input('city');
    $member->pin = $request->input('pin');
    $member->Address = $request->input('Address');

    // Save the changes
    $member->save();

    return redirect()->back()->with('success', 'Member details updated successfully!');
}
    

    public function member_subscription(){
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        
        $dataReaderBill = DB::table('MemberReceipts')->where('Mem_Id',  $member->SC_ID)->first();

        if ($dataReaderBill) {
            $BillAmt = $dataReaderBill->BillAmt;
            $PaymentReceived = $dataReaderBill->PaymentReceived;
            $BillMonthYear = $dataReaderBill->BillMonthYear;
            $PayStatus = $dataReaderBill->PayStatus;
            $ReceivingDate = Carbon::parse($dataReaderBill->ReceivingDate)->format('d/m/Y');
            
            $AmountPayable = $BillAmt - $PaymentReceived;
            Session::put('amt', $AmountPayable);

            // Generate random transaction id
            $txnid = 'MBP' . substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            Session::put('refno', $txnid);

            // Update MemberReceipts with the generated transaction id
            // MemberReceipt::where('Mem_Id', $member->SC_ID)
            //     ->where('PayStatus', 'pending')
            //     ->update(['TransactionID' => $txnid]);
        } else {
            // Handle case when MemberReceipts data is not found
        }

        $trans = DB::table('transactions')->where('order_id',  $dataReaderBill->TransactionID)->where('payment_status', 'Paid')->first();
        
        return view('website.pages.subscription',compact('member','AmountPayable','dataReaderBill','trans'));
    }

    public function member_otp(){
        $sc_id = auth()->user()->id;
        $otp = rand(100000,999999);

        DB::delete("DELETE FROM OTP WHERE MemberId = ? AND Verified = 0", [$sc_id]);

    // Insert new OTP record
     DB::insert("INSERT INTO OTP (MemberId, OTP) VALUES (?, ?)", [$sc_id, $otp]);

    
        return view('website.pages.otp',compact('otp'));
    }
    public function member_card_recharge(){

        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $card_recharge = DB::table('CardClosingBalance')->where('MemberID', $member?$member->SC_ID:'')->first();
        
        return view('website.pages.card_recharge',compact('member', 'card_recharge'));
    }

    public function confirm(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        // Retrieve user data from the database
        $dataReader = auth()->user();

        $PayStatus ="Pending";
       

        // Extract user data
        $MemID = $dataReader->MemberID;
      
        $SC_ID = $dataReader->SC_ID;
        $MemberName = $dataReader->DisplayName;
        $MobileNo = $dataReader->Mobile;
        $Email = $dataReader->Email;
        $Category = $dataReader->CategoryTypeCode;
        $Status = $dataReader->Status;
 

        // Get recharge amount from the form
        $RechargeAmount = $request->input('txtAmountCharged');
        
        // Generate a unique transaction ID
        $txnid = 'SCR' . substr(hash('sha256', mt_rand() . microtime()), 0, 20);

        // Set default values for MobileNo and Email if not provided
        if ($MobileNo == "0" || $MobileNo == "") {
            $MobileNo = "9999999999";
        }

        if ($Email == "") {
            $Email = "contact@aepta.in";
        }

        // Delete pending recharge records for the user
        DB::table('CardRecharge')->where('Card_ID', $SC_ID)->where('PayStatus', 'pending')->delete();

        // Insert new recharge record
        DB::table('CardRecharge')->insert([
            'Card_ID' => $SC_ID,
            'RechargeAmt' => $RechargeAmount,
            'RechargeDate' => now(),
            'PayStatus' => $PayStatus,
            'TxnRefrenceNo' => $txnid,
            'BankRefrenceNo' => '',
            'TransactionID' => $txnid,
            'ImportStatus' => 0,
            'PaymentResponse' => '',
            'TransactionType'=>'SCR',
            'PayMode'=>'web',
            'WebhookResponse'=>''
        ]);
  
        
        return view('website.pages.card_recharge_confirm',compact('member','txnid','RechargeAmount'));
    }
    public function subscription_confirm(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        // Retrieve user data from the database
        $dataReader = auth()->user();

        $PayStatus ="Pending";
       

        // Extract user data
        $MemID = $dataReader->MemberID;
      
        $SC_ID = $dataReader->SC_ID;
        $MemberName = $dataReader->DisplayName;
        $MobileNo = $dataReader->Mobile;
        $Email = $dataReader->Email;
        $Category = $dataReader->CategoryTypeCode;
        $Status = $dataReader->Status;
 

        // Get recharge amount from the form
        $RechargeAmount = $request->input('txtAmountCharged');
        
        // Generate a unique transaction ID
        $txnid = 'SCR' . substr(hash('sha256', mt_rand() . microtime()), 0, 20);

        // Set default values for MobileNo and Email if not provided
        if ($MobileNo == "0" || $MobileNo == "") {
            $MobileNo = "9999999999";
        }

        if ($Email == "") {
            $Email = "contact@aepta.in";
        }

        DB::table('MemberReceipts')->where('Mem_Id',   $SC_ID)->where('PayStatus', 'pending')->update(['TransactionID' => $txnid]);
        
        return view('website.pages.subscription_confirm',compact('member','txnid','RechargeAmount'));
    }
    public function member_change_password(){
        return view('website.pages.change_password');
    }
    public function ccavRequestHandler(Request $request){


        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

          // Your merchant data, working key, and access code
          $merchant_data = '2';
          $working_key = 'D7E32D391ACF0365D8CB9B5593D1BB01'; // Shared by CCAVENUES
          $access_code = 'AVPT37KL59AJ56TPJA'; // Shared by CCAVENUES
  
          // Build merchant data
          foreach ($request->all() as $key => $value) {
              $merchant_data .= $key . '=' . $value . '&';
          }
          $dataReader = auth()->user();
         
      
          $SC_ID = $dataReader->SC_ID;

          DB::table('CardClosingBalance')
                    ->where('MemberID', $SC_ID )
                    ->update([
                       
                        'is_updated' => false,
                    ]);

          
  
          // Encrypt the data
          $encrypted_data =  Helpers::encrypt($merchant_data, $working_key);
          
          //dd( $encrypted_data );
          // Update the database with the payment response
          DB::table('MemberReceipts')->update(['PaymentResponse' => $encrypted_data]);
          
          // Add any additional logic or redirection based on your requirements

          return view('website.pages.card_recharge_request',compact('member','access_code','encrypted_data'));
  

    }

    public function subscriptionCcavRequestHandler(Request $request){


        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

          // Your merchant data, working key, and access code
          $merchant_data = '2';
          $working_key = 'D7E32D391ACF0365D8CB9B5593D1BB01'; // Shared by CCAVENUES
          $access_code = 'AVPT37KL59AJ56TPJA'; // Shared by CCAVENUES
  
          // Build merchant data
          foreach ($request->all() as $key => $value) {
              $merchant_data .= $key . '=' . $value . '&';
          }
          $dataReader = auth()->user();
         
      
          $SC_ID = $dataReader->SC_ID;

        //   DB::table('CardClosingBalance')
        //             ->where('MemberID', $SC_ID )
        //             ->update([
                       
        //                 'is_updated' => false,
        //             ]);

          
  
          // Encrypt the data
          $encrypted_data =  Helpers::encrypt($merchant_data, $working_key);
          
          //dd( $encrypted_data );
          // Update the database with the payment response
          DB::table('MemberReceipts')->update(['PaymentResponse' => $encrypted_data]);
          
          // Add any additional logic or redirection based on your requirements

          return view('website.pages.subscription_request',compact('member','access_code','encrypted_data'));
  

    }
    public function CardChargeResponse(Request $request){

        //dd($request->all());
      
        $workingKey = 'D7E32D391ACF0365D8CB9B5593D1BB01'; 
        $encResponse = $request->encResp;
        $rcvdString = Helpers::decrypt($encResponse, $workingKey); // Crypto Decryption used as per the specified working key.

       
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);

        $order_id = $tracking_id = $bank_ref_no = $ReceivedAmount = $Mem_No = $DisplayName = $trans_date = "";

        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);

            switch ($i) {
                case 0:
                    $order_id = $information[1];
                    break;
                case 1:
                    $tracking_id = $information[1];
                    break;
                case 2:
                    $bank_ref_no = $information[1];
                    break;
                case 3:
                    $order_status = $information[1];
                    break;
                case 10:
                    $ReceivedAmount = $information[1];
                    break;
                case 26:
                    $Mem_No = $information[1];
                    break;
                case 27:
                    $DisplayName = $information[1];
                    break;
                case 40:
                    $trans_date = $information[1];
                    break;
            }
        }

        $merchantRefNo = $order_id;

        // Check the CardRecharge record
        $cardRecharge = DB::table('CardRecharge')
            ->where('TransactionID', $merchantRefNo)
            ->first();

        if ($cardRecharge) {
            $SentTransactionID = $cardRecharge->TransactionID;
            $BillAmt = $cardRecharge->RechargeAmt;
            $C_ID = $cardRecharge->Card_ID;
        }

        if ($order_status === "Success") {
            $PaymentStatus = "Success";
        } elseif ($order_status === "Aborted" || $order_status === "Failure") {
            $PaymentStatus = "Failure";
            $ReceivedAmount = 0;
        } else {
            $PaymentStatus = "Security Error. Illegal access detected";
            $ReceivedAmount = 0;
        }

        if ($SentTransactionID == $merchantRefNo && $BillAmt == $ReceivedAmount && $order_status === "Success") {
            $PaymentStatus = 'SUCCESS';
        } else {
            $PaymentStatus = 'FAILURE';
        }

        // Retrieve C_ID from the session or redirect to index.php
        $dataReader = auth()->user();

        $PayStatus ="Pending";
       

        // Extract user data
        $MemID = $dataReader->MemberID;
      
        $SC_ID = $dataReader->SC_ID;
        $C_ID =$dataReader->SC_ID;

        if ($order_status === "Failure") {
            DB::table('CardRecharge')
                ->where('TransactionID', $merchantRefNo)
                ->where('Card_ID', $C_ID)
                ->where('PayStatus', 'pending')
                ->update([
                    'PayStatus' => $PaymentStatus,
                    'TxnRefrenceNo' => $tracking_id,
                    'BankRefrenceNo' => $bank_ref_no,
                    'PaymentResponse' => $request->input('encResp'),
                    //'RechargeDate' => $trans_date,
                ]);
        } else {
            DB::table('CardRecharge')
                ->where('TransactionID', $merchantRefNo)
                ->where('Card_ID', $C_ID)
                ->where('RechargeAmt', $ReceivedAmount)
                ->where('PayStatus', 'pending')
                ->update([
                    'PayStatus' => $PaymentStatus,
                    'TxnRefrenceNo' => $tracking_id,
                    'BankRefrenceNo' => $bank_ref_no,
                    'PaymentResponse' => $request->input('encResp'),
                    //'RechargeDate' => $trans_date,
                ]);

            $cardClosingBalance = DB::table('CardClosingBalance')
                ->where('MemberID', $C_ID)
                ->first();

            $ReceiptDate = now();

            if (!$cardClosingBalance) {
                DB::table('CardClosingBalance')
                    ->insert([
                        'MemberID' => $C_ID,
                        'CardBalance' => $ReceivedAmount,
                        'ClosingDate' => $ReceiptDate,
                        'is_updated' => true,
                    ]);
            } elseif (!$cardClosingBalance->is_updated) {
             
                DB::table('CardClosingBalance')
                    ->where('MemberID', $C_ID)
                    ->update([
                        'CardBalance' => DB::raw('CardBalance + ' . $ReceivedAmount),
                        'ClosingDate' => $ReceiptDate,
                        'is_updated' => true,
                    ]);
            }
        }
    $member = Member::where("memberprofile.id",auth()->user()->id)
    ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
 
    // Display the message and decrypted values in a view
    return view('website.pages.card_recharge_response', [
        "member"=> $member ,
        'message' => $PaymentStatus,
        'order_id' => $order_id,
        'tracking_id' => $tracking_id,
        'bank_ref_no' => $bank_ref_no,
        'order_status' => $order_status,
        'ReceivedAmount' => $ReceivedAmount,
    ]);

   

    }
    public function subscriptionResponse(Request $request){

        //dd($request->all());
      
        $workingKey = 'D7E32D391ACF0365D8CB9B5593D1BB01'; 
        $encResponse = $request->encResp;
        $rcvdString = Helpers::decrypt($encResponse, $workingKey); // Crypto Decryption used as per the specified working key.

       
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);

        $order_id = $tracking_id = $bank_ref_no = $ReceivedAmount = $Mem_No = $DisplayName = $trans_date = "";

        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);

            switch ($i) {
                case 0:
                    $order_id = $information[1];
                    break;
                case 1:
                    $tracking_id = $information[1];
                    break;
                case 2:
                    $bank_ref_no = $information[1];
                    break;
                case 3:
                    $order_status = $information[1];
                    break;
                case 10:
                    $ReceivedAmount = $information[1];
                    break;
                case 26:
                    $Mem_No = $information[1];
                    break;
                case 27:
                    $DisplayName = $information[1];
                    break;
                case 40:
                    $trans_date = $information[1];
                    break;
            }
        }

        $merchantRefNo = $order_id;

        // Check the CardRecharge record
        $cardRecharge = DB::table('MemberReceipts')
            ->where('TransactionID', $merchantRefNo)
            ->first();

        if ($cardRecharge) {
            $SentTransactionID = $cardRecharge->TransactionID;
            $BillAmt = $cardRecharge->RechargeAmt;
            $C_ID = $cardRecharge->Card_ID;
        }

        if ($order_status === "Success") {
            $PaymentStatus = "Success";
        } elseif ($order_status === "Aborted" || $order_status === "Failure") {
            $PaymentStatus = "Failure";
            $ReceivedAmount = 0;
        } else {
            $PaymentStatus = "Security Error. Illegal access detected";
            $ReceivedAmount = 0;
        }

        if ($SentTransactionID == $merchantRefNo && $BillAmt == $ReceivedAmount && $order_status === "Success") {
            $PaymentStatus = 'SUCCESS';
        } else {
            $PaymentStatus = 'FAILURE';
        }

        // Retrieve C_ID from the session or redirect to index.php
        $dataReader = auth()->user();

        $PayStatus ="Pending";
       

        // Extract user data
        $MemID = $dataReader->MemberID;
      
        $SC_ID = $dataReader->SC_ID;
        $C_ID =$dataReader->SC_ID;



  

     DB::table('MemberReceipts')
    ->where('TransactionID', $merchantRefNo)
    ->where('Mem_Id', $C_ID)
    ->where('BalanceAmt', $ReceivedAmount)
    ->where('PayStatus', 'pending')
    ->update([
        'PayStatus' => $PaymentStatus,
        'TxnRefrenceNo' => $tracking_id,
        'BankRefrenceNo' => $bank_ref_no,
        'PaymentResponse' => $rcvdString,
        'ReceivingDate' => now()
    ]);
          

        
    $member = Member::where("memberprofile.id",auth()->user()->id)
    ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
 
    // Display the message and decrypted values in a view
    return view('website.pages.subscription_response', [
        "member"=> $member ,
        'message' => $PaymentStatus,
        'order_id' => $order_id,
        'tracking_id' => $tracking_id,
        'bank_ref_no' => $bank_ref_no,
        'order_status' => $order_status,
        'ReceivedAmount' => $ReceivedAmount,
    ]);

   

    }

    public function get_otp(){
        $sc_id = auth()->user()->id;
        $otp = rand(100000,999999);

        DB::delete("DELETE FROM OTP WHERE MemberId = ? AND Verified = 0", [$sc_id]);

    // Insert new OTP record
     DB::insert("INSERT INTO OTP (MemberId, OTP) VALUES (?, ?)", [$sc_id, $otp]);

    echo $otp;
    }

    public function banquet_payment_checkout($banq_id='')
    {
        $banq_id = decrypt($banq_id);

        $banquet = BanquetBooking::find($banq_id);

        $banquet_details_amt = BanquetBookingCharges::where('banquet_booking_id', $banq_id)->sum('total');

        $amount = $banquet_details_amt;

        $order_Amt = $amount*100;
        
        $curl = curl_init();

        $resp_data = [
            "amount" => $order_Amt,
            "currency" => "INR",
            "receipt" => "123",
            "notes" => [
                "key1" => "value1",
                "key2" => "value2",
                "key3" => "value3"
            ]
        ];

        curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode($resp_data), // encode data to JSON
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic cnpwX2xpdmVfYlRiektXaW9ISkZWb1A6T1FoYnJIUWxhY2gxSGJlWDN5YWJGNVh1'
              ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response, true);
    
        if ($data && isset($data['error'])) {

          return $data['error']['description'] ?? 'Try Again.';

        } else {
          
            if($data && isset($data['id'])){
                $order_id = $data['id'];
            } else {
                $order_id = uniqid();
            }

        }

        $params['order_id'] = $order_id; 

        // $params['bank_refrance_no'] = $order_id; 

        $params['amount'] = $amount; 

        $params['type'] = 'Banquet Booking'; 

        $params['member_id'] = $banquet?$banquet->cardID:'';

        $params['banquet_booking_id'] = $banq_id;

        $member = Member::where("memberprofile.id",auth()->user()->id)->first();

        $bookingID = date('dmY').'-'.rand(9999,100000);

        $params['transID'] = $banquet->booking_ID;

        DB::table('transactions')->insert($params);

        $params['title'] = 'Checkout payment';  

        $type = 'Banquet Booking';

        
        $params['return_url']       = URL::to('razorpay/callback');
        $params['surl']             = URL::to('razorpay/success');
        $params['furl']             = URL::to('razorpay/failed');
        $params['currency_code']    = 'INR';
        $params['reference_no']     = $order_id;
        $params['amount']           = $amount;
        $params['student']          = $member;
        $params['order_id']         = $order_id;
        $params['type']             = $type;
        $params['mobile']           = $banquet->memberMobile;
     
        return view('payment.checkout_new',$params);
    }
    
    public function payment_checkout(Request $request)
    {
        if(empty(Auth::user())){
            return redirect('login');
        }
        
        $member = Member::where("memberprofile.id",auth()->user()->id)->first();
        
        $amount = $request->txtAmountCharged;

        $order_Amt = $amount*100;
        
        $curl = curl_init();

        $resp_data = [
            "amount" => $order_Amt,
            "currency" => "INR",
            "receipt" => "123",
            "notes" => [
                "key1" => "value1",
                "key2" => "value2",
                "key3" => "value3"
            ]
        ];

        curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.razorpay.com/v1/orders',
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'POST',
              CURLOPT_POSTFIELDS => json_encode($resp_data), // encode data to JSON
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: Basic cnpwX2xpdmVfYlRiektXaW9ISkZWb1A6T1FoYnJIUWxhY2gxSGJlWDN5YWJGNVh1'
              ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $data = json_decode($response, true);
       
        if ($data && isset($data['error'])) {

          return $data['error']['description'] ?? 'Try Again.';

        } else {
          
            if($data && isset($data['id'])){
                $order_id = $data['id'];
            } else {
                $order_id = uniqid();
            }

        }

        // $payy = DB::table('PaymentKey')->where('payment_name','SBI')->first();

        // $key=$payy?$payy->payment_key:'';

        $params['order_id'] = $order_id; 

        $params['razorpay_order_id'] = $order_id; 

        $params['amount'] = $amount; 

        $params['type'] = $request->type; 

        $params['member_id'] = $member?$member->SC_ID:'';

        $bookingID = date('dmY').'-'.rand(9999,100000);

        $params['transID'] = $bookingID;
        
        $res = DB::table('transactions')->insert($params);
        
        if($request->type=='Subscription'){

            $type = 'Subscription';

            DB::table('MemberReceipts')->where('Mem_Id', $request->memberReceiptsId)->update(['TransactionID' => $order_id, 'ReceivingDate' => date('Y-m-d H:i:s')]);

        } else {

            $type = 'Card Recharge';

            $recharge['Card_ID']        = $member?$member->SC_ID:'';
            $recharge['RechargeAmt']    = $amount; 
            $recharge['TransactionType']= 'Card Recharge'; 
            $recharge['PayMode']        = 'Web'; 
            $recharge['TxnRefrenceNo']  = $order_id; 
            $recharge['BankRefrenceNo'] = $order_id; 
            $recharge['TransactionID']  = $order_id; 
            $recharge['PaymentResponse']= $order_id;
            $recharge['RechargeDate']   = date('Y-m-d H:i:s');
            $recharge['WebhookResponse']= '';

            DB::table('CardRecharge')->insert($recharge);

        }

         $data['member'] = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorymaster', 'categorymaster.Code', '=', 'memberprofile.CategoryCode')->first();
    
        $params['title'] = 'Checkout payment';  
        
        $params['return_url']       = URL::to('razorpay/callback');
        $params['surl']             = URL::to('razorpay/success');
        $params['furl']             = URL::to('razorpay/failed');
        $params['currency_code']    = 'INR';
        $params['reference_no']     = $order_id;
        $params['amount']           = $amount;
        $params['student']          = $member;
        $params['order_id']         = $order_id;
        $params['type']             = $type;
        $params['mobile']           = $request->mobile;

        return view('payment.checkout_new',$params);
       
    }

    public function razorpay_callback(Request $request)
    {

        // Test Key
        // $key                    = 'rzp_test_TfccAiRNRpb2hA';
        // $secret                 = 'AsRRyEzgvLnTRfY7tSSAim03';

        // Live Key
        $key                    = 'rzp_live_bTbzKWioHJFVoP';
        $secret                 = 'OQhbrHQlach1HbeX3yabF5Xu';
       
        if(isset($request->error)){

            if(isset($request->error['metadata'])){

                $metadata = json_decode($request->error['metadata'], true);
                
                if($metadata && isset($metadata) && isset($metadata['payment_id'])){
                    $razorpay_payment_id    = $metadata['payment_id'];
                } else {
                    $razorpay_payment_id    = '';
                }
                
                if($metadata && isset($metadata) && isset($metadata['order_id'])){
                    $order_id    = $metadata['order_id'];
                } else {
                    $order_id    = '';
                }
                
            } else {

                $order_id = '';
                $razorpay_payment_id = '';

            }
             
            $status  = 'Not Paid';

            $razorpay_signature = '';

        } else {

            $order_id               = $request->razorpay_order_id;
            $razorpay_payment_id    = $request->razorpay_payment_id;
            $razorpay_signature     = $request->razorpay_signature;

            // Generate the expected signature
            $generated_signature = $this->verifyRazorpaySignature($order_id, $razorpay_payment_id, $secret);

            // Compare the generated signature with Razorpay's signature
            if ($generated_signature && $razorpay_signature && hash_equals($generated_signature, $razorpay_signature)) {

                // Signature is valid; proceed with payment success handling
                $status  = 'Paid';

            } else {

                // Signature is invalid; handle payment failure
                $status  = 'Not Paid';

            }

        }
        
        $params['transaction_date']     = date('Y-m-d H:i:s');
        $params['razorpay_order_id']    = $order_id;
        $params['razorpay_payment_id']  = $razorpay_payment_id;
        $params['razorpay_signature']   = $razorpay_signature;
        $params['payment_status']       = $status;
        
        DB::table('transactions')->where('order_id', $order_id)->update($params);

        if($status=='Paid'){

            $trans = DB::table('transactions')->where('order_id', $order_id)->latest()->first();

            if($trans && $trans->type=='Banquet Booking'){

                $banq_params['payment_status'] = $status;
                $banq_params['payment_status'] = $status;
                BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->update($banq_params);
                BanquetBooking::where('id', $trans->banquet_booking_id)->update($banq_params);

                $trans = BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->get();

                foreach ($trans as $key => $value) {

                    $check = BanquetBookingCharges::where('id', '!=' , $value->id)->where('status', 'Active')->where('payment_status', 'Paid')->whereDate('funDate', $value->funDate)->where('session_id', $value->session_id)->where('vanue_id', $value->vanue_id)->first();

                    if($check){

                        $check_params['status'] = 'Pending';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                        break;
                        
                    } else {

                        $check_params['status'] = 'Active';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                    }                    
                    
                }

            } elseif ($trans && $trans->type=='Room Booking'){

                $r_params['status'] = 'Active';
                RoomBooking::where('id', $trans->room_booking_id)->update($r_params);

            }
            
        }

        $ca_params['TransactionID']        = $order_id;
        $ca_params['BankRefrenceNo']       = $razorpay_payment_id;
        $ca_params['PayStatus']            = $status;

        DB::table('CardRecharge')->where('TxnRefrenceNo', $order_id)->update($ca_params);

        DB::table('MemberReceipts')->where('TxnRefrenceNo', $order_id)->update($ca_params);

        if($order_id && isset($order_id)){

            return redirect()->route('e-transaction',encrypt($order_id));

        } else {

            return redirect()->route('member_transactions');
        }
        

    }

    public function verifyRazorpaySignature($order_id, $razorpay_payment_id, $secret)
    {
        // Concatenate order_id and razorpay_payment_id with '|'
        $payload = $order_id . '|' . $razorpay_payment_id;

        // Generate the signature using hash_hmac with sha256 algorithm
        $generated_signature = hash_hmac('sha256', $payload, $secret);

        return $generated_signature;
    }

    public function razorpay_failed(Request $request)
    {
        return $request;
    }

    public function etransaction($ref='')
    {
        if(Auth::check()){

            $ref = decrypt($ref);
            
            $data['tt'] = DB::table('transactions')->where('order_id', $ref)->orderBy('id', 'DESC')->first();

            $data['member'] = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorymaster', 'categorymaster.Code', '=', 'memberprofile.CategoryCode')->first();

            return view('payment.payment_confirm',$data);

        } else {

            return redirect('login');
        }
    }

    public function member_transactions()
    {
        if(Auth::check()){

            $member = Member::where("memberprofile.id",auth()->user()->id)->first();

            $data['tt'] = DB::table('transactions')->where('member_id', $member?$member->SC_ID:'')->orderBy('id', 'DESC')->paginate(15);

            $data['member'] = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorymaster', 'categorymaster.Code', '=', 'memberprofile.CategoryCode')->first();

            return view('website.pages.transactions',$data);

        } else {

            return redirect('login');
        }
       
    }

    function encrypt($plainText, $workingKey)
    {
        $secretKey = hextobin(md5($workingKey));
        $initVector = pack("C*", 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
        $encryptedText = openssl_encrypt($plainText, 'aes-128-cbc', $secretKey, OPENSSL_RAW_DATA, $initVector);
        return bin2hex($encryptedText);
    }

    // Convert hexadecimal to binary
    function hextobin($hexString)
    {
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            $packedString = pack("H*", $subString);
            if ($count == 0) {
                $binString = $packedString;
            } else {
                $binString .= $packedString;
            }
            $count += 2;
        }
        return $binString;
    }

    public function webhook_payload()
    {

        $tras = DB::table('transactions')->where('payment_status','Not Paid')->where( 'created_at', '>=', Carbon::now()->subDays(15))->get();

        $this->clearCache();

        foreach ($tras as $key => $val) {

            $response = $this->webhooks_API($val->order_id);

            if($response && isset($response['count'])){

                    $item = $response['items'];
                    
                    $status = 'Not Paid';

                    foreach ($item as $key => $bnk_res) {

                        $status = $bnk_res['status'];

                    }

                    echo "MemberID : "; print_r($val->member_id); echo " | Order ID : "; print_r($val->order_id); echo " | Status : "; print_r($status); echo "<br>";
                  
                    if($status=='captured'){    

                        $status                         = 'Paid';

                        $params['transaction_date']     = date('Y-m-d H:i:s');
                        $params['payment_status']       = $status;

                        DB::table('transactions')->where('order_id', $val->order_id)->update($params);

                        $ca_params['PayStatus']            = $status;

                        DB::table('CardRecharge')->where('TxnRefrenceNo', $val->order_id)->update($ca_params);

                        DB::table('MemberReceipts')->where('TxnRefrenceNo', $val->order_id)->update($ca_params);

                        $order_id = $val->order_id;

                        $trans = DB::table('transactions')->where('order_id', $order_id)->latest()->first();

                        if($trans && $trans->type=='Banquet Booking'){

                            $banq_params['payment_status'] = $status;
                            $banq_params['payment_status'] = $status;
                            BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->update($banq_params);
                            BanquetBooking::where('id', $trans->banquet_booking_id)->update($banq_params);

                            $trans = BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->get();

                            foreach ($trans as $key => $value) {

                                $check = BanquetBookingCharges::where('id', '!=' , $value->id)->where('status', 'Active')->where('payment_status', 'Paid')->whereDate('funDate', $value->funDate)->where('session_id', $value->session_id)->where('vanue_id', $value->vanue_id)->first();

                                if($check){

                                    $check_params['status'] = 'Pending';

                                    $check_params['payment_status'] = 'Paid';

                                    BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                                    BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                                    break;
                                    
                                } else {

                                    $check_params['status'] = 'Active';

                                    $check_params['payment_status'] = 'Paid';

                                    BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                                    BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                                }                    
                                
                            }

                        }

                    }

            }

        }
    }

    public function sbi_payment_sucess(Request $request)
    {   

        $AESobj=new AESEncDec();

        $payy = DB::table('PaymentKey')->where('payment_name','SBI')->first();

        $key=$payy?$payy->payment_key:'';

        $plaintext = $AESobj->decrypt($request->encData,$key);
        
        $decryptValues=explode('&', $plaintext);               

        $dataSize=sizeof($decryptValues);
        
        $order_id = '';

        $status = '';

        $bank_refrance_no = '';

        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('|',$decryptValues[$i]);
            
            $order_id=$information[0]; 
            // $subString = isset($information[6]) ? $information[6] : null;
            // if ($subString) {
            //     $caretSplit = explode('^', $subString);
                
            //     // Step 4: Trim and get the last part, which is "Card Recharge"
            //     $paymentDescription = isset($caretSplit[2]) ? trim($caretSplit[2]) : null;

            //     echo $paymentDescription; // Output: "Card Recharge"
            // }
            // dd($subString);

            // $bank_refrance_no =$information[1];

            // $status=$information[2]; 

        }

        $tt = DB::table('transactions')->where('order_id', $order_id)->first();

        $merchant_order_no=$tt->order_id; // merchant order no
        $merchantid="1002864";  //merchant id
        $amount=$tt->amount;

        $url="https://www.sbiepay.sbi/payagg/statusQuery/getStatusQuery"; // double verification test env url

        $queryRequest="|$merchantid|$merchant_order_no|$amount";
        $queryRequest33=http_build_query(array('queryRequest' => $queryRequest,"aggregatorId"=>"SBIEPAY","merchantId"=>$merchantid));
       
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_SSLVERSION, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch,CURLOPT_POSTFIELDS, $queryRequest33);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec ($ch);

        $decryptValues=explode('&', $response);

        $dataSize=sizeof($decryptValues);
        
        $status = '';

        $bank_refrance_no = '';

        for($i = 0; $i < $dataSize; $i++) 
        {
            $information=explode('|',$decryptValues[$i]);
            
            $bank_refrance_no =$information[1];

            $status=$information[2]; 

        }

        // $member = Member::where("MemberID",$tt->member_id)->first();

        $member = Member::where("memberprofile.SC_ID",$tt->member_id)->first();

        $randomNumber = $request->session()->get('randomNumber');
        $pwd=$member->Password ?? '';
        $sha256Hash=$pwd.$randomNumber;
        $sha256Hash = hash('sha256', $sha256Hash);        
           
        $sessionToken = Str::random(60);    
        session(['session_token' => $sessionToken]);    
        $member->session_token = $sessionToken;
        $member->save();

        Auth::login($member);

        if($status == 'SUCCESS'){

            $paramss['payment_status']  = 'Paid';
            $paramssm['PayStatus']      = 'Paid';
            $recharge['PayStatus']      = 'Paid';
            $status = 'Paid';

        } else if ($status == 'FAIL'){

            $paramss['payment_status']  = 'Failed';
            $paramssm['PayStatus']      = 'Failed';
            $recharge['PayStatus']      = 'Failed';
            $status = 'Failed';

        } else {

            $paramss['payment_status']  = 'Not Paid';
            $paramssm['PayStatus']      = 'Not Paid';
            $recharge['PayStatus']      = 'Not Paid';
            $status = 'Not Paid';

        }

        $notification = [
            'title' => 'MBClub - Payment',
            'short_descriptions' => $status == 'SUCCESS' ? 'Your payment is paid.' : 'Your payment is failed.',
        ];    
        $this->sendFCMMessage($notification, $member->device_id, $member->device_type);

        $paramss['bank_refrance_no']    = $bank_refrance_no;
        $paramss['bank_response']       = $decryptValues;

        $paramssm['BankRefrenceNo']     = $bank_refrance_no;
        $paramssm['PaymentResponse']    = $decryptValues;

        $recharge['BankRefrenceNo']     = $bank_refrance_no;
        $recharge['OrderResponse']      = $decryptValues;

        DB::table('transactions')->where('order_id', $order_id)->update($paramss);

        DB::table('MemberReceipts')->where('TransactionID', $order_id)->update($paramssm);

        DB::table('CardRecharge')->where('TransactionID', $order_id)->update($recharge);

        $data_re = DB::table('CardRecharge')->where('TransactionID', $order_id)->where('PayStatus','Paid')->first();
      
        if($data_re){

            $data_close = DB::table('CardClosingBalance')->where('MemberID', $data_re->Card_ID)->first();

            $close['CardBalance'] = $data_re->RechargeAmt + $data_close->CardBalance;

            DB::table('CardClosingBalance')->where('MemberID', $data_re->Card_ID)->update($close);

        }

        $trans = DB::table('transactions')->where('order_id', $order_id)->latest()->first();

        if($trans && $trans->type=='Banquet Booking'){

            $banq_params['payment_status'] = $status;

            BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->update($banq_params);

            BanquetBooking::where('id', $trans->banquet_booking_id)->update($banq_params);

            if($status=='Paid'){

                $trans = BanquetBookingCharges::where('banquet_booking_id', $trans->banquet_booking_id)->get();

                foreach ($trans as $key => $value) {

                    $check = BanquetBookingCharges::where('id', '!=' , $value->id)->where('status', 'Active')->where('payment_status', 'Paid')->whereDate('funDate', $value->funDate)->where('session_id', $value->session_id)->where('vanue_id', $value->vanue_id)->first();

                    if($check){

                        $check_params['status'] = 'Pending';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                        break;
                        
                    } else {

                        $check_params['status'] = 'Active';

                        BanquetBookingCharges::where('banquet_booking_id', $value->banquet_booking_id)->update($check_params);

                        BanquetBooking::where('id', $value->banquet_booking_id)->update($check_params);

                    }                    
                    
                }

            }
            

        } elseif ($trans && $trans->type=='Room Booking' && $status=='Paid'){

            $r_params['status'] = 'Active';

            RoomBooking::where('id', $trans->room_booking_id)->update($r_params);

        }
        
        return redirect('payment-confirm/'.$order_id);

    }

    public function sbi_payment_fail(Request $request)
    {
        return redirect()->route('transactions');
    }

    public function webhooks_API($reference_no='')
    {
        $curl = curl_init();

        // $reference_no = 'order_OvnAj9AXegaHt9';
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.razorpay.com/v1/orders/'.$reference_no.'/payments',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'Authorization: Basic cnpwX2xpdmVfYlRiektXaW9ISkZWb1A6T1FoYnJIUWxhY2gxSGJlWDN5YWJGNVh1'
          ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);

        $response = json_decode($response, true);
        return $response;
        echo "<pre>"; print_r($response); 
    }

    public function check_email()
    {       

        $array['view']      = 'emails.invoice';
        $array['subject']   = 'Test Email';
        $array['data']      = '';
        return \Mail::to('raj.vish81@gmail.com')->send(new \App\Mail\InvoiceMail($array));
    }

    public function php_check_email()
    {
        $msg = "First line of text\nSecond line of text";

        // use wordwrap() if lines are longer than 70 characters
        $msg = wordwrap($msg,70);

        // send email
        mail("raj.vish81@gmail.com","My subject",$msg);
    }

    public function clearCache()
    {
        // Clear application cache
        Artisan::call('cache:clear');

        // Clear route cache
        Artisan::call('route:clear');

        // Clear config cache
        Artisan::call('config:clear');

        // Clear view cache
        Artisan::call('view:clear');
    }
    
}

?>