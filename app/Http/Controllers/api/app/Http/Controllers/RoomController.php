<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\Room;
use App\Models\OccupantTypeOption;
use App\Models\OccupantType;
use App\Models\RoomPrice;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Constraint\Count;

class RoomController extends Controller
{
    public function index(Request $request)
    {
        if (session('temp_booking_id')) session()->forget('temp_booking_id');

        //dd($request->all);
        $getRequest = $request->all();
        // default
        if (empty($getRequest)) {
            $checkin = Carbon::now()->format('Y-m-d');
            $checkout = Carbon::now()->addDays(1)->format('Y-m-d');
        } else {
            $checkin = date('Y-m-d', strtotime($request->query('checkin')));
            $checkout = date('Y-m-d', strtotime($request->query('checkout')));

            //if (session('room_cart')) session()->forget('room_cart');
        }

        if (session('checkin') !== $checkin || session('checkout') !== $checkout) {
            session()->forget('room_cart');
        }
        session(['checkin' => $checkin, 'checkout' => $checkout]);

        // SELECT *, GET_AVAIAVLE_ROOMS_BY_CHEKIN_DATE(id, '2023-10-10', '2023-10-10') as available FROM rooms;

        $rooms = Room::selectRaw("*, GET_AVAILABLE_ROOMS_BY_CHECKIN_DATE(id, '$checkin', '$checkout') as available_rooms")->with(['roomPrices', 'roomPrices.occupants', 'roomAmenity'])->get();
        //dd($rooms);
        return view('member.rooms', compact('rooms'));
    }

    /* public function index()
    {
        session(['checkin' => date('Y-m-d'), 'checkout' => date('Y-m-d', strtotime("+1 days"))]);

        $rooms = Room::select("*")->with(['roomPrices', 'roomPrices.ocuppents'])->get();
        return view('member.rooms', compact('rooms'));
    } */

    public function roomDetails($id)
    {
        //dd(session());
        //$getSessionData = session();
        $checkin = session('checkin');
        $checkout = session('checkout');

        //$room = Room::find(decrypt($id));

        $room = Room::selectRaw("*, GET_AVAILABLE_ROOMS_BY_CHECKIN_DATE(id, '$checkin', '$checkout') as available_rooms")->with(['roomPrices', 'roomPrices.occupants'])->where('id', decrypt($id))->first();
        return view('member.room-details', compact('room'));
    }


    public function validateRoomBooking(Request $request)
    {
        //if (session('temp_booking_id')) session()->forget('temp_booking_id');

        $category_id = Auth::user()->Category;
        $room_id = $request->query('room_id');
        $occupant_id = $request->query('occupant_id');
        // $roomCount = $request->query('roomCount'); --> will get from cart session

        // price, max room , max nights
        // SELECT a.*, b.* FROM `room_prices` AS a INNER JOIN occupant_types AS b ON a.occupant_type_id = b.id WHERE a.room_type_id = 1 AND a.occupant_type_id = 1;
        $price_details = RoomPrice::select('room_prices.*', 'occupant_types.*')->with(['room' => function ($q) {
            $q->select('id', 'max_guest');
        }])
            ->join('occupant_types', 'room_prices.occupant_type_id', '=', 'occupant_types.id')
            ->where(['room_prices.room_type_id' => $room_id, 'room_prices.occupant_type_id' => $occupant_id, 'room_prices.member_category_id' => $category_id])
            ->first();

        //        dd($price_details);
        // check for max_room & max_night (optional for now)
        /*
        if ($roomCount > $price_details->max_rooms) {
            return response()->json(['status' => 0, 'message' => 'Max limit reached. Booking not allowed']);
        }
        */

        // options
        // SELECT * FROM `room_prices` WHERE room_type_id = 1 AND occupant_type_id = 1;
        $occupantOptions = OccupantTypeOption::where('occupant_type_id', $occupant_id)->get();

        //dd($price_details);
        return response()->json(['status' => 1, 'options' => $occupantOptions, 'price_details' => $price_details]);
    }

    public function deleteOccupantsFromList($id)
    {
        $id_arr = explode('.', decrypt($id));

        if (count($id_arr) === 2) {
            $room_cart = session('room_cart');

            if (!empty($room_cart['rooms'][$id_arr['0']]['occupants'])) {
                unset($room_cart['rooms'][$id_arr['0']]['occupants'][$id_arr['1']]);

                if (count($room_cart['rooms'][$id_arr['0']]['occupants']) === 0) {
                    unset($room_cart['rooms'][$id_arr['0']]);

                    if (count($room_cart['rooms']) === 0) {
                        $room_cart = [];
                        // session()->forget('room_cart');
                    }
                }
            }

            if (!empty($room_cart) && count($room_cart)) {
                if (!empty(session('room_cart'))) {
                    $rooms = session('room_cart')['rooms'];
                } else {
                    $rooms = [];
                }

                $total_price = array_sum(array_map(fn ($item) => $item['price'], $rooms));
                $room_cart['total_price'] = $total_price;
                session(['room_cart' => $room_cart]);
            } else {
                session()->forget('room_cart');
            }

            $message = ['success' => 'Guest Removed Successfully'];
        } else {
            $message = ['error' => 'Invalid Url'];
        }

        return back()->with($message);
    }

    public function addOccupantsToList(Request $request)
    {
        $room_cart_key = decrypt($request->room_cart_key);
        $occupant_type = $request->occupant_type;
        $occupant_name = $request->occupant_name;
        $occupant_mobile = $request->occupant_mobile;

        $room_cart = session('room_cart');
        if (!empty($room_cart)) {
            $max_guest = $room_cart['rooms'][$room_cart_key]['max_guest'];
            if (count($room_cart['rooms'][$room_cart_key]['occupants']) < $max_guest) {
                $occupants_arr = array(
                    'type_id' => $occupant_type,
                    'type_name' => get_occupant_type_option_name($occupant_type),
                    'name' => $occupant_name,
                    'mobile' => $occupant_mobile
                );

                array_push($room_cart['rooms'][$room_cart_key]['occupants'], $occupants_arr);

                session(['room_cart' => $room_cart]);

                $message = ['success' => 'Guest Added Successfully'];
            } else {
                $message = ['error' => 'Maximum ' . $max_guest . ' Guests are allowed.'];
            }
        } else {
            $message = ['error' => 'Empty room cart.'];
        }
        return back()->with($message);
    }

    public function addToList(Request $request)
    {
        //if (session('room_cart')) session()->forget('room_cart');
        //dd($request->all());

        // validation pending

        $category_id = Auth::user()->Category;
        $room_id = $request->room_id;
        $occupant_type_id = $request->occupant_type_id;

        $details = get_room_details($room_id, $occupant_type_id, $category_id);
        //dd($details);

        if (!empty($request->occupant_type)) {
            $occupants = array();
            foreach ($request->occupant_type as $key => $value) {
                //$occupants[$key] = array_merge($request->occupant_type[$key], $request->occupant_name[$key], $request->occupant_mobile[$key]);
                $occupants[$key] = array(
                    'type_id' => $request->occupant_type[$key],
                    'type_name' => get_occupant_type_option_name($request->occupant_type[$key]),
                    'name' => $request->occupant_name[$key],
                    'mobile' => $request->occupant_mobile[$key]
                );
            }
        } else {
            return back()->with(['error' => 'The guest list is empty']);
        }

        //dd($occupants);
        $price_pre_day = $price = $details->price;
        $days = $this->get_days(session('checkin'), session('checkout'));
        if ($days > 1) {
            $price = $price_pre_day * $this->get_days(session('checkin'), session('checkout'));
        }

        $room_details = array(
            'room_id' => $room_id,
            'room_title' => $details->room->title,
            'max_guest' => $details->room->max_guest,
            'occupant_type_id' => $occupant_type_id,
            'occupant_type' => $details->name,
            'price' => $price,
            'gst' => $details->gst,
            'occupants' => $occupants
        );

        if (!empty(session('room_cart'))) {
            $rooms = session('room_cart')['rooms'];
        } else {
            $rooms = [];
        }

        array_push($rooms, $room_details);

        $total_price = array_sum(array_map(fn ($item) => $item['price'], $rooms));

        session([
            'room_cart' => [
                'checkin' => date('Y-m-d', strtotime(session('checkin'))),
                'checkout' => date('Y-m-d', strtotime(session('checkout'))),
                'days' => $days,
                'total_price' => $total_price,
                'rooms' => $rooms
            ]
        ]);
        /* echo "<pre>";
        print_r(session('room_cart'));
        die; */
        return redirect()->back();
    }

    public function get_days($startDate, $endDate)
    {
        $start = date_create(date('Y-m-d', strtotime($startDate)));
        $end = date_create(date('Y-m-d', strtotime($endDate)));
        $days = date_diff($start, $end);
        return (int)$days->format("%a");
    }

    /* public function checkout()
    {
        return view('member.checkout');
    } */
}
