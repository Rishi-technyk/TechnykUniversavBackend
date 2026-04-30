<?php



use App\Models\Room;

use App\Models\OccupantTypeOption;

use App\Models\OccupantType;

use App\Models\RoomPrice;

use App\Models\RoomBooking;

use App\Models\CardItem;



use App\Models\BlockRoom;



use App\Models\RoomBookingItem;



use App\Models\RoomCategoryMaster;



use App\Models\Card;


use App\Models\Member;



use App\Models\VenueBlock;

use App\Models\BanquetBookingCharges;



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



function con_decrypt($encryptedText,$key)

    {

       $key = $this->hextobin(md5($key));

       $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);

       $encryptedText = hextobin($encryptedText);

       $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);

       return $decryptedText;

    }



    function hextobin($hexString) 

    { 

       $length = strlen($hexString); 

       $binString="";   

       $count=0; 

       while($count<$length) 

       {       

          $subString =substr($hexString,$count,2);           

          $packedString = pack("H*",$subString); 

          if ($count==0)

          {

             $binString=$packedString;

          } else {

             $binString.=$packedString;

          }



     



          $count+=2; 

       } 

       return $binString; 

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

    

    if (!function_exists('getAvaiableRooms')) {

    

        function getAvaiableRooms($room_category_id='')

    

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

    

            return $avai_room;

    

        }

    

    }

    

    if (!function_exists('getBookedRooms')) {

    

        function getBookedRooms_Old($category_id='', $category_type_id='', $room_category_id='', $check_in='', $check_out='')

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

        function getBookedRooms($category_id='', $category_type_id='', $room_category_id='', $check_in='', $check_out='', $main_check_in='')

        {        
            // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where('from_date', '<=', $check_in)->where('to_date', '>=', $check_out)->first();
            // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->whereBetween('to_date', [$check_in, $check_out])->first();
            // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where('from_date', '<=', $check_in.' '.env('CheckOut',null))->where('to_date', '>=', $check_in.' '.env('CheckOut',null))->get();

            $days = (strtotime($check_out) - strtotime($main_check_in)) / (60 * 60 * 24);

            if($days>'1')
            $check_out = date('Y-m-d', strtotime('-1 day', strtotime($check_out)));
            else
            $check_out = $check_out;
            
            $main_check_in = $main_check_in.' '.env('CheckIn',null);
            $main_check_out = $check_out.' '.env('CheckOut',null);

            // Old Query
            // $blockroom = BlockRoom::where('room_category_id', $room_category_id)->where(function ($query) use ($main_check_in, $main_check_out) {
            //                                                             $query->whereBetween('from_date', [$main_check_in, $main_check_out])
            //                                                                   ->orWhereBetween('to_date', [$main_check_in, $main_check_out])
            //                                                                   ->orWhere(function ($q) use ($main_check_in, $main_check_out) {
            //                                                                       $q->where('from_date', '<=', $main_check_in)
            //                                                                         ->where('to_date', '>=', $main_check_out);
            //                                                                   });
            //                                                         })->sum('blocked_room');

            $blockroom = BlockRoom::where('room_category_id', $room_category_id)
                                    ->where(function ($query) use ($main_check_in, $main_check_out) {
                                        $query->where('from_date', '<', $main_check_out)
                                              ->where('to_date', '>', $main_check_in);
                                    })
                                    ->sum('blocked_room');

           
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

            // old code
            // $rooms = RoomBooking::where('status', 'Active')->where(function ($query) use ($main_check_in, $main_check_out) {
            //                                                             $query->whereBetween('checkin', [$main_check_in, $main_check_out])
            //                                                                   ->orWhereBetween('checkout', [$main_check_in, $main_check_out])
            //                                                                   ->orWhere(function ($q) use ($main_check_in, $main_check_out) {
            //                                                                       $q->where('checkin', '<=', $main_check_in)
            //                                                                         ->where('checkout', '>=', $main_check_out);
            //                                                                   });
            //                                                         })->get();

            $rooms = RoomBooking::where('status', 'Active')
                                    ->where(function ($query) use ($main_check_in, $main_check_out) {
                                        $query->where('checkin', '<', $main_check_out)
                                              ->where('checkout', '>', $main_check_in);
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

