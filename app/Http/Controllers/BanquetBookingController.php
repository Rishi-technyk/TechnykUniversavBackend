<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\SOP;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use App\Models\Transaction;
use App\Models\AdminSetting;
use Illuminate\Http\Request;
use App\Models\FunctionMaster;
use App\Models\BanquetBooking;
use App\Models\BanquetOccupant;
use App\Models\CancellationPolicy;
use Illuminate\Support\Facades\Auth;
use App\Models\BanquetBookingCharges;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class BanquetBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data['session'] = DB::table('sessions')->where('status', 'Active')->get();
        $data['occupant_type'] = BanquetOccupant::where('status', 'Active')->get();

        $q = BanquetBookingCharges::query();
            
            if ($request->fundate)
            {
                $q->whereDate('funDate', $request->fundate);
            }

            if ($request->session)
            {
                $q->where('session_id', $request->session);
            }

            if ($request->card_id)
            {
            $cID = $request->card_id;

            $q->whereHas('banquet', function ($query) use ($cID) {
                $query->where('cardID', $cID);
            });
            }

            if ($request->member_id)
            {
            $MID = $request->member_id;

            $q->whereHas('banquet', function ($query) use ($MID) {
                $query->where('memberID', $MID);
            });
            }

            if ($request->occupant_type)
            {
            $OTy = $request->occupant_type;

            $q->whereHas('banquet', function ($query) use ($OTy) {
                $query->where('occupant_type', $OTy);
            });
            }

            if ($request->status)
            {
                $q->where('status', $request->status);
            }

        $data['datas'] = $q->orderBy('id', 'DESC')->get();

        $data['request'] = $request;
        
        return view('backend.banquet_booking.index', $data);
    }

    // Frontend Route
    public function banquet_booking()
    {
        $occupan = BanquetOccupant::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $function = FunctionMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $member = Auth::guard('student')->user();
        
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

        return view('frontend.banquet_booking.form', compact('occupan', 'vanue', 'session', 'function', 'member', 'from_date', 'to_date', 'SOP'));
    }

    public function check_occupant(Request $request)
    {
        $occupan = BanquetOccupant::where('status', 'Active')->where('id', $request->occ_id)->first();

        return response()->json(['data'=>$occupan]);
    }

    public function append_extra_field(Request $request)
    {
        $vanue = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $session = DB::table('sessions')->where('status', 'Active')->orderBy('id', 'DESC')->get();

        $rand = rand(10,100);

        $html = view('frontend.banquet_booking.append_extra_field',compact('vanue', 'session', 'rand'))->render();

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
        $datas['vanue'] = VenueMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        $datas['request'] = $request;

        $view = view('frontend.banquet_booking.venue_by_session', $datas)->render();

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

            foreach ($request->session_id as $key => $session) {
                
                $input['banquet_booking_id'] = $res->id;
                $input['session_id']    = $session;
                $input['vanue_id']      = $request->vanue_id[$key];
                $input['gst_amount']    = $request->gst_amount[$key];;
                $input['gst_per']       = $request->gst_per[$key];;
                $input['charges']       = $request->charges[$key];
                $input['total']         = $request->total[$key];
                $input['funDate']       = $request->funDate;

                BanquetBookingCharges::create($input);

                $total_amt += $request->total[$key];
            }

            // $this->banquet_payment_checkout($params);
        
            return redirect()->route('banquet.payment.checkout', encrypt($res->id));

        } else {

            return redirect()->back()->with('error', 'Try Again.');
        }

    }

    public function banquet_payment_checkout($banq_id='')
    {
        $banq_id = decrypt($banq_id);

        $banquet = BanquetBooking::find($banq_id);

        $banquet_details_amt = BanquetBookingCharges::where('banquet_booking_id', $banq_id)->sum('total');

        $amount = $banquet_details_amt;

        $member = Auth::guard('student')->user();
   
        $order_id = 'BQB'.date('YmdHis').rand(999,10000);

        $params['order_id']         = $order_id; 

        $params['amount']           = $amount; 

        $params['member_id']        = $member?$member->SC_ID:'';

        $params['transID']          = $banquet->booking_ID;

        $params['type']             = 'Banquet Booking'; 

        $paramst['payment_type']    = 'ICICI';
                
        $paramst['entry_come']      = 'Web';

        $params['banquet_booking_id'] = $banq_id;

        Transaction::create($params);

        return redirect()->route('billdesk.pay', ['order_id' => encrypt($order_id)]);
    }

    function banquet_transactions(Request $request)
    {
        $member = Auth::guard('student')->user();

        if($request && $request->function_date || $request->booking_no){

            $q = BanquetBooking::query();

            if($request->function_date){
                $q->whereDate('funDate', $request->function_date);
            }

            if($request->booking_no){
                $q->where('booking_ID', $request->booking_no);
            }

            $datas = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(25);

        } else {

            $datas = BanquetBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(25);

        }

        $data['datas'] = $datas;

        return view('frontend.transaction.banquet_transactions', $data);
        
    }

    public function banquet_booking_details($id='')
    {
        $id = decrypt($id);

        $datas['datas'] = BanquetBooking::find($id);

        $datas['transaction'] = Transaction::where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        return view('frontend.transaction.banquet_details', $datas);
    }

    public function banquet_details_download($id='')
    {
        $datas['datas'] = BanquetBooking::find($id);

        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['transaction'] = Transaction::where('banquet_booking_id', $id)->first();

        $datas['setting'] = AdminSetting::first();

        $pdf = PDF::loadView('frontend.transaction.banquet_details_download', $datas);

        return $pdf->download('Banquet.pdf');

    }

    public function banquet_booking_cancel($id='')
    {
        $id = decrypt($id);
        
        $datas['datas'] = BanquetBooking::find($id);

        $datas['transaction'] = DB::table('transactions')->where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['prev_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $datas['latest_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();
        
        return view('frontend.transaction.banquet_cancel', $datas);
    }

    public function cancelVenue(Request $request)
    {

        $policys = CancellationPolicy::where('venue_id', $request->venue_id)->get();

        if(count($policys)){

            $booking = BanquetBookingCharges::find($request->bookingID);
           
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

                $data['msg'] = '';
                $data['status'] = true;

                return $data;

            } else {

                $data['msg'] = 'Cancellation policy is not available for this venue';
                $data['status'] = false;

                return $data;

            }
        } else {

            $data['msg'] = 'Cancellation policy is not available for this venue';
            $data['status'] = false;

            return $data;
        }
        
    }

    public function getBookingVenue(Request $request)
    {
        $id = $request->booking_id;

        $datas['transaction'] = DB::table('transactions')->where('banquet_booking_id', $id)->first();
       
        $datas['bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->get();

        $datas['prev_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->sum('cancellation_deducation');

        $datas['latest_bookings'] = BanquetBookingCharges::where('banquet_booking_id', $id)->where('status', 'Cancelled')->orderBy('id', 'DESC')->latest()->first();
        
        $view = view('frontend.transaction.banquet_cancel_venue', $datas)->render();

        return $view;
    }
}
