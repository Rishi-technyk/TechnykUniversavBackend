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

class BanquetController extends Controller
{
	public function booking_form()
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

        $maxVenue = VenuePax::max('venue_count');

        return view('website.pages.banquet_booking.booking_form',compact('member','occupan', 'session', 'vanue', 'function', 'setting', 'from_date', 'to_date', 'SOP', 'maxVenue'));
    
    }

    public function append_extra_field(Request $request)
    {
        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $rand = rand(10,100);

        $html = view('website.pages.banquet_booking.append_extra_field',compact('vanue', 'session', 'rand'))->render();

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

        return Session::get('session_array');      

    }

    public function get_venue_by_session(Request $request)
    {
        if(isset($request->group_id))
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

        $venue_charge = 0;

        $venue_max_pax = '';
        
        if($request->function_date){

            $checkBooking = BanquetBooking::whereDate('funDate', '=', $request->function_date)->first();

            $pax = $request->pax;
           
            if ($charges) {
                // Normalize data (decode only if string)
                $minPaxArray = is_string($charges->min_pax) ? json_decode($charges->min_pax, true) : $charges->min_pax;
                $maxPaxArray = is_string($charges->max_pax) ? json_decode($charges->max_pax, true) : $charges->max_pax;
                $rateArray   = is_string($charges->rate) ? json_decode($charges->rate, true) : $charges->rate;

                if (is_array($minPaxArray) && count($minPaxArray)) {
                    foreach ($minPaxArray as $key => $min_pax) {
                        if ($pax >= $min_pax && $pax <= ($maxPaxArray[$key] ?? 0)) {
                            $venue_charge = $rateArray[$key] ?? '0';
                            break;
                        } else {
                            $venue_charge = $rateArray[$key] ?? '0';
                        }
                    }

                    $venue_max_pax = $maxPaxArray[$key] ?? 0;
                }
            }

            if($checkBooking){

                $checkVenue = BanquetBookingCharges::where('status', 'Active')->where('banquet_booking_id', $checkBooking->id)->where('vanue_id', $request->venue)->where('session_id', $request->session)->count();

                if($checkVenue){

                    return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'Booking', 'checkVenue'=>'', 'venue_charge'=> $venue_charge, 'venue_max_pax'=>$venue_max_pax]);

                } else {

                    $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

                    return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert, 'venue_charge'=> $venue_charge, 'venue_max_pax'=>$venue_max_pax]);

                }

            } else {

                $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

                return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert, 'venue_charge'=> $venue_charge, 'venue_max_pax'=>$venue_max_pax]);

            }

        } else {

            $checkInsert = store_venue_charge_in_session($charges?$charges->id:'');

            return response()->json(['charges'=>$charges, 'venue'=>$venue, 'booking'=>'No Booking', 'checkVenue'=> $checkInsert, 'venue_charge'=> $venue_charge, 'venue_max_pax'=>$venue_max_pax]);

        }

    }

    public function get_venue_pax_new(Request $request)
    {

        $paxx = VenuePax::where('venue_count', $request->venue_count)->first();

        if(isset($paxx)){            

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
        $params['venue_total_amount_active'] = $request->pax_changes;

        $res = BanquetBooking::create($params);

        if($res){

            $total_amt = '0';
            
            if($request->session_id && $request->vanue_id){

                $pax_charge = $request->total_value_amount;   // string ko integer me convert karo
                
                $sessionCount = is_array($request->session_id) 
                                    ? count($request->session_id) 
                                    : (int) $request->session_id;  // agar array nahi hai to number cast karo
                
                foreach ($request->session_id as $key => $session) {

                    if($session && isset($request->vanue_id[$key])){
                    
                        $input['banquet_booking_id'] = $res->id;
                        $input['session_id']    = $session;
                        $input['vanue_id']      = $request->vanue_id[$key];

                        if($request->pax_changes=='Yes'){

                            $input['gst_amount']    = 0;
                            $input['gst_per']       = 0;
                            $input['charges']       = $pax_charge / $sessionCount;
                            $input['security_deposit'] = 0;
                            $input['total']         = $pax_charge / $sessionCount;                            

                        } else {

                            $input['gst_amount']    = $request->gst_amount[$key];;
                            $input['gst_per']       = $request->gst_per[$key];;
                            $input['charges']       = $request->charges[$key];
                            $input['security_deposit'] = $request->security_deposit[$key];
                            $input['total']         = $request->total[$key];

                        }
                        
                        $input['funDate']       = $request->funDate;

                        BanquetBookingCharges::create($input);

                    }
                }
            }

            // $this->banquet_payment_checkout($params);

            return redirect()->route('banquet.payment.checout', encrypt($res->id));

        } else {

            return redirect()->bach()->with('error', 'Try Again.');
        }


    }

}