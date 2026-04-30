<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RoomCancellationPolicy;
use App\Models\RoomChargesMaster;
use App\Models\RoomBookingItem;
use App\Models\OccupantMaster;
use App\Models\AdminSetting;
use App\Models\RoomBooking;
use App\Models\CardItem;
use App\Models\Member;
use App\Models\Card;
use App\Models\SOP;
use Carbon\Carbon;
use AESEncDec;
use DB;

class RoomBookingController extends Controller
{
    public function room_traction(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

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

            $datas = $q->where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

        } else {

            $datas = RoomBooking::where('memberID', $member->MemberID)->orderBy('id', 'DESC')->paginate(15);

        }

        foreach ($datas as $key => $value) {
            $value->payment = DB::table('transactions')->where('transID', $value->booking_number)->select('payment_status')->first();
        }

        $return_data['data'] = $datas;

        $return_data['message'] = '';

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function room_traction_details($id='')
    {
        $datas = RoomBooking::where('id', $id)->first();

        $datas->paid_payment = getBookingTotal($id);

        $data_items = RoomBookingItem::where('booking_id', $id)->get();

        foreach ($data_items as $key => $item) {

            $item->room_name = $item->room->name;
            $item->occupant_name = $item->occupant->name;
            
        }

        $datas->payment_info = DB::table('transactions')->where('transID', $datas->booking_number)->select('transID', 'payment_status', 'bank_refrance_no')->first();

        $datas->room_details = $data_items;

        $return_data['data'] = $datas;

        $return_data['message'] = '';

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_rooms(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $startDate = Carbon::parse($request->check_in);
        $endDate = Carbon::parse($request->check_out);

        $daysDifference = $startDate->diffInDays($endDate);

        $rooms = [];

        $max_nitie = '';

        if($request && $request->check_in ||  $request->check_out){
          
            if($request->check_out<=$request->check_in){

                $return_data['data'] = '';

                $return_data['message'] = 'Check-out date should be greater than the Check-in date.';

                $return_data['status'] = true; 

            } else {
                
                $q = RoomChargesMaster::query();

                $S = 'Active';

                $q->whereHas('room_category', function ($query) use ($S) {
                    $query->where('status', $S);
                });

                $q->where('status', $S);

                if($member && $member->CategoryTypeCode)
                $q->where('category_type_id', $member->CategoryTypeCode);

                if($member && $member->CategoryCode)
                $q->where('category_id', $member->CategoryCode);

                $roomss = $q->groupBy(['category_id','room_category_id'])->with('room_category')->get();
              
                $rooms = [];
                
                $check_in_date_last = date('Y-m-d', strtotime('+1 day', strtotime($request->check_in)));

                $max_nitie = RoomChargesMaster::where('category_type_id', $member->CategoryTypeCode)->max('max_no_of_nites');

                foreach ($roomss as $key => $room) {

                    if(getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in)>'0'){

                        $room->available_room = getBookedRooms($room->category_id, $room->category_type_id, $room->room_category_id, $check_in_date_last, $request->check_out, $request->check_in);

                        if($max_nitie && $max_nitie>=$daysDifference){
                            array_push($rooms, $room); 
                        } else {
                            array_push($rooms, $room); 
                        }
                       
                        
                    }
                }

            }

            $datas['rooms'] = $rooms;

            $datas['member'] = $member;            

            $datas['max_nitie'] = $max_nitie;

            $datas['daysDifference'] = $daysDifference;
           
        } else {

            $rooms = [];

            $max_nitie = '';
        }


        $return_data['data'] = $datas;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_occupant(Request $request)
    {
        $occupant = OccupantMaster::where('status', 'Active')->get();

        $return_data['data'] = $occupant;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_setting(Request $request)
    {
        $setting = AdminSetting::first();

        $return_data['data'] = $setting;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_SOP(Request $request)
    {
        $SOP = SOP::where('type', 'Room Booking')->first();

        $return_data['data'] = $SOP;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function get_room_charge(Request $request)
    {
        $data['occupan'] = OccupantMaster::where('status', 'Active')->where('id', $request->occupant_id)->first();

        $data['room_charge'] = RoomChargesMaster::where('category_id', $request->category_id)->where('category_type_id', $request->category_type_id)->where('occupant_type_id', $request->occupant_type_id)->where('room_category_id', $request->room_category_id)->first();

        $return_data['data'] = $data;

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function store_in_summary(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

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
            $paramss['occupant_id']         = $request->occupant_id;
            $paramss['no_of_rooms']         = $request->booked_room_no;
            $paramss['no_of_days']          = $in_days;
            $paramss['room_charges']        = $request->room_charges;
            $paramss['adult']               = $request->adult;
            $paramss['child']               = $request->child;
            $paramss['guest_name']          = $request->guest_name;
            $paramss['guest_email']         = $request->guest_email;
            $paramss['guest_mobile']        = $request->guest_mobile;

            $room_charges_total = ($request->room_charges*$request->booked_room_no)*$in_days;

            $GST_a = ($request->gst / 100) * $room_charges_total; 

            $paramss['gst_per']             = $request->gst;
            $paramss['gst_amount']          = $GST_a;
            $paramss['room_charge_total']   = $room_charges_total+$GST_a;

            $res = CardItem::create($paramss);

            $return_data['message'] = 'Booking insert in session';

            $return_data['status'] = true;

        } else {

            $return_data['message'] = 'Try Again';

            $return_data['status'] = false;

        }

        return response()->json($return_data);
    }

    public function get_summary(Request $request)
    {
        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $datas = Card::where('memberID', $member->MemberID)->first();

        if($datas){

            $data_items = CardItem::where('card_id', $datas?$datas->id:'')->get();

            foreach ($data_items as $key => $item) {

                $item->room_name = $item->room->name;
                $item->occupant_name = $item->occupant->name;
                
            }

            $datas->room_details = $data_items;
        }
        

        $return_data['data'] = $datas;

        $return_data['status'] = true;
        
        return response()->json($return_data);
    }

    public function booking_checkout($card_id)
    {
        $card = Card::where('id', $card_id)->first();

        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        if($card){

            $params['booking_number']   = $card->booking_number;
            $params['memberID']         = $card->memberID;
            $params['memberName']       = $member->DisplayName;
            $params['chartID']          = $card->chartID;
            $params['checkin']          = $card->checkin;
            $params['checkout']         = $card->checkout;
            $params['booking_from']     = 'App';

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


                $member = Member::where("memberprofile.id",auth()->user()->id)->first();
           
                $order_id = "SBIePay".mt_rand().'_RMB';

                $paramst['order_id'] = $order_id; 

                $paramst['amount'] = $amount; 

                $paramst['member_id'] = $member?$member->SC_ID:'';

                $paramst['transID'] = $card->booking_number;

                $paramst['type'] = 'Room Booking'; 

                $paramst['room_booking_id'] = $booking->id;
                
                DB::table('transactions')->insert($paramst);

                CardItem::where('card_id', $card->id)->delete();
                Card::where('booking_number', $card_id)->delete();

                $AESobj=new AESEncDec();

                $requestParameter  ="1002864|DOM|IN|INR|".$amount."|".$member->MemberID.'^'.$member->DisplayName. '^Card Recharge'."|https://mbclublucknow.org/mbclublogin/app-sbi-sucess|https://mbclublucknow.org/mbclublogin/app/sbi/payment/fail|SBIEPAY|".$order_id."|2|NB|ONLINE|ONLINE";

                $payy = DB::table('PaymentKey')->where('payment_name','SBI')->first();

                $key=$payy?$payy->payment_key:'';

                $cipherText = $AESobj->encrypt($requestParameter,$key); 

                $plaintext = $AESobj->decrypt($cipherText,$key);

                $data['cipherText'] = $cipherText;
               
                $return_data['message'] = 'Booking inserted successfully.';

                $return_data['status'] = true; 

                $return_data['id'] = $booking->id;

                $return_data['order_id'] = $order_id;

                $return_data['data'] = ['url' => url('/redirectToSBI') . '?cipherText=' . urlencode($cipherText) . '&merchIdVal=1002864&type=Room_Booking'];
            }

        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 

        }

        return response()->json($return_data);
    }

    public function update_payment(Request $request)
    {
        if($request->status == 'SUCCESS'){

            $status = 'Paid';

        } else if ($request->status == 'FAIL'){

            $status = 'Failed';

        } else {

            $status = 'Not Paid';

        }

        $trans = DB::table('transactions')->where('order_id', $request->order_id)->latest()->first();

        if($trans){

            if($status=='Paid'){

                $r_params['status'] = 'Active';

                RoomBooking::where('id', $trans->room_booking_id)->update($r_params);
            }

            $paramss['payment_status']      = $status;
            $paramss['bank_refrance_no']    = $request->bank_refrance_no;
            $paramss['bank_response']       = $request->decryptValues;
            DB::table('transactions')->where('order_id', $request->order_id)->update($paramss);

            $return_data['message'] = 'Payment Updated.';

            $return_data['status'] = true; 

        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 

        }
        
        return response()->json($return_data);
        
    }

    public function room_cancel($id)
    {
        $item = RoomBookingItem::find($id);

        if($item){

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

            $return_data['message'] = 'Room Cancelled';

            $return_data['status'] = true; 

        } else {

            $return_data['message'] = 'Data not found.';

            $return_data['status'] = false; 

        }

        return response()->json($return_data);
    }

    public function empty_card($card_id='')
    {
        $card = Card::where('id', $card_id)->first();

        CardItem::where('card_id', $card->id)->delete();

        Card::where('booking_number', $card_id)->delete();

        $return_data['message'] = 'Card Empty';

        $return_data['status'] = true;

        return response()->json($return_data);
    }

    public function cancel_room_item($ciid='')
    {
        $card = CardItem::where('id', $ciid)->first();

        CardItem::where('id', $ciid)->delete();

        $cards = CardItem::where('id', $card->card_id)->get();

        if(empty($cards)){

            Card::where('id', $card->card_id)->delete();

        }

        $return_data['message'] = 'Item Remove';

        $return_data['status'] = true;

        return response()->json($return_data);
        
    }
}
