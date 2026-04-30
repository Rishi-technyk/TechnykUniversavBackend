<?php



use App\Models\Room;

use App\Models\Member;

use App\Models\OccupantTypeOption;

use App\Models\OccupantType;

use App\Models\BanquetBookingCharges;

use App\Models\RoomPrice;

use App\Models\CardItem;

use App\Models\BlockRoom;

use App\Models\RoomBookingItem;

use App\Models\RoomBooking;

use App\Models\RoomCategoryMaster;

use App\Models\Card;

use App\Models\VenueBlock;

use App\Models\RoomChargesMaster;

use Session;



/**

 * Write code on Method

 *

 * @return response()

 */
 

if (!function_exists('get_room_details')) {

    function get_room_details($room_id, $occupant_id, $category_id)

    {

        $details = RoomPrice::select('room_prices.*', 'occupant_types.*')->with(['room' => function ($q) {

            $q->select('id', 'title', 'max_guest');

        }])

            ->join('occupant_types', 'room_prices.occupant_type_id', '=', 'occupant_types.id')

            ->where(['room_prices.room_type_id' => $room_id, 'room_prices.occupant_type_id' => $occupant_id, 'room_prices.member_category_id' => $category_id])

            ->first();



        return $details;

    }

}

if (!function_exists('get_occupant_type_option_name')) {

    function get_occupant_type_option_name($id)

    {

        return OccupantTypeOption::select('option')->where(['id' => $id])->first()->option;

    }

}

if (!function_exists('store_venue_charge_in_session')) {

    function store_venue_charge_in_session($id)

    {        

        if(in_array($id, Session::get('session_array'))){

            return 'Already';

        } else {

            $v_s_array = Session::get('session_array');

            array_push($v_s_array, $id);

            Session::put('session_array', $v_s_array);

            return 'Insert';

        }

    }

}

function getVenueTotal($id='')
{
   return BanquetBookingCharges::where('banquet_booking_id', $id)->sum('total');
}

if (!function_exists('checkRoomInCard')) {

    function checkRoomInCard($category_id='', $category_type_id='', $room_category_id='')

    {        

        $member = Member::where("memberprofile.id",auth()->user()->id)->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        $cards = Card::where('memberID', $member->MemberID)->first();

        $check = CardItem::where('card_id', $cards?$cards->id:'')->where('category_id', $category_id)->where('category_type_id', $category_type_id)->where('room_category_id', $room_category_id)->exists();

        return $check;

    }

}

function getBookingTotal($id='')
{
   $data = RoomBookingItem::where('booking_id', $id)->get();

   $total = '0';

   foreach ($data as $key => $value) {
       $total += $value->room_charge_total;
   }

   return $total;
}

if (!function_exists('checkNites')) {

    function checkNites($room_category_id='', $daysDifference='')
    {
        $room_cate = RoomChargesMaster::where('room_category_id',$room_category_id)->first();

        return $room_cate->max_no_of_nites ?? '0';
    }

}

if (!function_exists('getAvaiableRooms')) {

    function getAvaiableRooms($room_category_id='', $check_in='')

    {        

        $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where('from_date', '<=', $check_in)->where('to_date', '>=', $check_in)->first();
        // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->whereDate('from_date', '<=', date('Y-m-d'))->whereDate('to_date', '>=', date('Y-m-d'))->first();

        $room_cate = RoomCategoryMaster::find($room_category_id);

        if($room_cate && $blockroom && $room_cate->no_of_rooms >= $blockroom->blocked_room ){

            $avai_room = $room_cate->no_of_rooms - $blockroom->blocked_room;

        } else {

            if($room_cate && $room_cate->no_of_rooms){

                $avai_room = $room_cate->no_of_rooms;

            } else {
                $avai_room = '0';
            }

        }

        return $avai_room;

    }

}

if (!function_exists('getBookedRooms')) {

    function getBookedRooms($category_id='', $category_type_id='', $room_category_id='', $check_in='', $check_out='', $main_check_in='')

    {        
        // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where('from_date', '<=', $check_in)->where('to_date', '>=', $check_out)->first();
        // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->whereBetween('to_date', [$check_in, $check_out])->first();
        // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where('from_date', '<=', $check_in.' '.env('CheckOut',null))->where('to_date', '>=', $check_in.' '.env('CheckOut',null))->get();
      
        $main_check_in = $main_check_in.' '.env('CheckIn',null);
        $main_check_out = $check_out.' '.env('CheckOut',null);

        $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where(function ($query) use ($main_check_in, $main_check_out) {
                                                                    $query->whereBetween('from_date', [$main_check_in, $main_check_out])
                                                                          ->orWhereBetween('to_date', [$main_check_in, $main_check_out])
                                                                          ->orWhere(function ($q) use ($main_check_in, $main_check_out) {
                                                                              $q->where('from_date', '<=', $main_check_in)
                                                                                ->where('to_date', '>=', $main_check_out);
                                                                          });
                                                                })->sum('blocked_room');
        
        $room_cate = RoomCategoryMaster::find($room_category_id);
        
        if($room_cate && $blockroom){

            if($room_cate->no_of_rooms >= $blockroom){

                $avai_room = $room_cate->no_of_rooms - $blockroom;

            } else {

                $avai_room = '0';

            }            

        } else {

            if($room_cate && $room_cate->no_of_rooms){

                $avai_room = $room_cate->no_of_rooms;

            } else {
                $avai_room = '0';
            }

        }
      
        $main_check_in = $main_check_in.' '.env('CheckIn',null);
        $main_check_out = $check_out.' '.env('CheckOut',null);

        $rooms = RoomBooking::where('status', 'Active')->where(function ($query) use ($main_check_in, $main_check_out) {
                                                                    $query->whereBetween('checkin', [$main_check_in, $main_check_out])
                                                                          ->orWhereBetween('checkout', [$main_check_in, $main_check_out])
                                                                          ->orWhere(function ($q) use ($main_check_in, $main_check_out) {
                                                                              $q->where('checkin', '<=', $main_check_in)
                                                                                ->where('checkout', '>=', $main_check_out);
                                                                          });
                                                                })->get();
         
        $bookings_ids = [];

        foreach ($rooms as $key => $value) {
            array_push($bookings_ids, $value->id);
        }

        $q = RoomBookingItem::query();

            
            $q->where('category_id', $category_id)->where('category_type_id', $category_type_id)->where('room_category_id', $room_category_id)->where('status', 'Active')->whereIn('booking_id', $bookings_ids);

            // $q->whereHas('booking_info', function ($query) use ($main_check_in) {
            //     $query->where('checkout', '>=', $main_check_in);
            // });

            // $q->whereHas('booking_info', function ($query) use ($check_in, $check_out) {
            //     $query->whereBetween('checkout', [$check_in, $check_out]);
            // });

        $booked_room = $q->sum('no_of_rooms');
        
        if($booked_room>=$avai_room){
            return '0';
        } else {
            return $avai_room-$booked_room;
        }

        

    }

}
if (!function_exists('getValidVenueCombinations')) {
 function getValidVenueCombinations($venues, $requiredCount)
{
    $paxRules = \App\Models\VenuePax::all();
    $validCombinations = [];

    $venueArray = $venues->values()->all();

    $combinations = collect($venueArray)
        ->crossJoin(...array_fill(0, $requiredCount - 1, $venueArray))
        ->filter(function ($combo) {
            $ids = collect($combo)->pluck('id');
            return $ids->unique()->count() === count($combo); // no duplicates
        });

    return $combinations;
}
}



if (!function_exists('checkVenueBlock')) {

    function checkVenueBlock($venue_id='', $session_id='', $function_date='')

    {        

        $check = VenueBlock::where('venue_id', $venue_id)->where('session_id', $session_id)->whereDate('from_date', '<=', $function_date)->whereDate('to_date', '>=', $function_date)->first();

        if(isset($check))
        return false;
        else
        return true;

    }

}

