<?php

use App\Models\Room;

use App\Models\OccupantTypeOption;

use App\Models\OccupantType;

use App\Models\RoomPrice;

use App\Models\CardItem;

use App\Models\BlockRoom;

use App\Models\RoomBookingItem;

use App\Models\RoomCategoryMaster;

use App\Models\Card;

use App\Models\Member;

use App\Models\BlockSlot;

use App\Models\GameBookingSlot;

use App\Models\GameBooking;

use App\Models\ActivityCardItem;

use App\Models\Facility;

use App\Models\VenueBlock;

use App\Models\RoomChargesMaster;

use App\Models\BanquetBookingCharges;

use Illuminate\Support\Facades\Auth;

if (!function_exists('format_price')) {
    function format_price($amount)
    {
        return '₹ ' . number_format($amount, 2);
    }
}

if (!function_exists('user_guard')) {
    function user_guard()
    {
        if (auth()->guard('student')->check()) {
            return 'student';
        }
        return 'web';
    }
}
if (!function_exists('get_authenticated_user')) {
    function get_authenticated_user()
    {
        $guard = user_guard();
        return auth()->guard($guard)->user();
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

if (!function_exists('getBookedRooms')) 
{

    function getBookedRooms($category_id='', $category_type_id='', $room_category_id='', $check_in='', $check_out='')
    {
        $blockroom = BlockRoom::where('room_category_id', $room_category_id)->whereDate('from_date', '<=', date('Y-m-d'))->whereDate('to_date', '>=', date('Y-m-d'))->first();

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

        $q = RoomBookingItem::query();

            $q->where('category_id', $category_id)->where('category_type_id', $category_type_id)->where('room_category_id', $room_category_id)->where('status', 'Active');

            $q->whereHas('booking_info', function ($query) use ($check_in) {

                $query->where('checkout', '>=', $check_in);

            });

        $booked_room = $q->sum('no_of_rooms');

        return $avai_room-$booked_room;

    }

}

function getMinCharge($room_category_id)
{
    $minPrice = RoomChargesMaster::where('status', 'Active')
                                ->where('room_category_id', $room_category_id)
                                ->selectRaw('MIN(CAST(charges_nite AS UNSIGNED)) as charges_nite')
                                ->value('charges_nite');

    return $minPrice ? $minPrice : 0;
}

if (!function_exists('checkRoomInCard')) {

    function checkRoomInCard($category_id='', $category_type_id='', $room_category_id='')
    {        
        $member  = Auth::guard('student')->user();

        $cards = Card::where('memberID', $member->MemberID)->first();

        $check = CardItem::where('card_id', $cards?$cards->id:'')->where('category_id', $category_id)->where('category_type_id', $category_type_id)->where('room_category_id', $room_category_id)->exists();

        return $check;

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

if (!function_exists('checkVenueBlock')) {

    function checkVenueBlock($venue_id='')
    {        

        $check = VenueBlock::where('venue_id', $venue_id)->whereDate('from_date', '<=', date('Y-m-d'))->whereDate('to_date', '>=', date('Y-m-d'))->first();

        if(isset($check))

        return false;

        else

        return true;

    }

}

if (!function_exists('checkSlot')) {

        function checkSlot($slot_id='', $date='', $facility_id='')
        {

            $q = BlockSlot::query();

            if ($facility_id){

                $q->where('facility_id', $facility_id);

            }

            if ($slot_id){

                $q->where('slot_id', $slot_id);

            }

            $blockSlot = $q->whereDate('date', $date)->exists();

            if($blockSlot){
                $bslot = '1';
            } else {
                $bslot = '0';
            }

            $facility = Facility::find($facility_id);

            $avl_slots = $facility->inventory - $bslot;

            $gameSlot = GameBookingSlot::where('slot_id', $slot_id)->whereDate('slot_date', $date)->get();

            $book_slot = '0';

            if($slot_id){

                foreach ($gameSlot as $key => $slot) {

                    $game = GameBooking::whereId($slot->game_booking_id)->where('status', 'Active')->where('payment_status', 'Paid')->first();

                    if($game && isset($game)){

                        $book_slot += '1';

                    }
                }

            }            

            return $avl_slots - $book_slot ?? '0';
        }

   }

   if (!function_exists('checkPerticularSlot')) {

        function checkPerticularSlot($slot_id='', $date='', $facility_id='')
        {
            $q = BlockSlot::query();

            if ($facility_id){

                $q->where('facility_id', $facility_id);

            }

            if ($slot_id){

                $q->where('slot_id', $slot_id);

            }

            $block = $q->whereDate('date', $date)->exists();
            
            // $block = BlockSlot::where('facility_id', $facility_id)->where('slot_id', $slot_id)->whereDate('date', $date)->exists();

            if($block){

                return false;

            } else {

                return true;

            }

        }

   }


    if (!function_exists('checkSlotBool')) {

        function checkSlotBool($slot_id='', $date='', $card_id='')
        {
            $data = ActivityCardItem::where('card_id', $card_id)->where('slot_id', $slot_id)->whereDate('slot_date', $date)->exists();

            if($data){
                return true;
            } else {
                return false;
            }
        }
    }