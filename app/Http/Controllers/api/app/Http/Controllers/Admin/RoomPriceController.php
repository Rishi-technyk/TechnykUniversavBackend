<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\CategoryMaster;
use App\Models\OccupantType;
use App\Models\RoomPrice;

class RoomPriceController extends Controller
{
    public function add()
    {
        $rooms = Room::all();
        $master_categories = CategoryMaster::all();
        $occupant_types = OccupantType::all();
        return view('admin.room-price-add', compact('rooms', 'master_categories', 'occupant_types'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'room_type' => 'required|integer|gt:0',
            'member_category' => 'required|integer|gt:0',
            'occupant_type' => 'required|integer|gt:0',
            'price' => 'required|integer|gt:0',
            'gst' => 'required|integer',
        ]);

        $room_type_id = $request->room_type;
        $member_category_id = $request->member_category;
        $occupant_type_id = $request->occupant_type;
        $price = $request->price;
        $gst = $request->gst;

        try {
            if (!RoomPrice::where([
                'room_type_id' => $room_type_id,
                'member_category_id' => $member_category_id,
                'occupant_type_id' => $occupant_type_id,
            ])->exists()) {
                RoomPrice::create([
                    'room_type_id' => $room_type_id,
                    'member_category_id' => $member_category_id,
                    'occupant_type_id' => $occupant_type_id,
                    'price' => $price,
                    'gst' => $gst,
                ]);
                return redirect()->back()->with('success', 'Room price added successfully.');
            } else {
                return redirect()->back()->with('error', 'Room price is already exists.');
            }
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }

    public function list()
    {
        $roomPrices = RoomPrice::all();
        return view('admin.room-price-list', compact('roomPrices'));
    }

    public function edit(RoomPrice $roomPrice)
    {
        $rooms = Room::all();
        $master_categories = CategoryMaster::all();
        $occupant_types = OccupantType::all();
        //dd($roomPrice); // route model binding
        return view('admin.room-price-edit', compact('roomPrice', 'rooms', 'master_categories', 'occupant_types'));
    }

    public function update(RoomPrice $roomPrice, Request $request)
    {
        $request->validate([
            'room_type' => 'required|integer|gt:0',
            'member_category' => 'required|integer|gt:0',
            'occupant_type' => 'required|integer|gt:0',
            'price' => 'required|integer|gt:0',
            'gst' => 'required|integer',
        ]);

        $id = $roomPrice->id;
        $room_type_id = $request->room_type;
        $member_category_id = $request->member_category;
        $occupant_type_id = $request->occupant_type;
        $price = $request->price;
        $gst = $request->gst;

        try {
            RoomPrice::find($id)->update([
                'room_type_id' => $room_type_id,
                'member_category_id' => $member_category_id,
                'occupant_type_id' => $occupant_type_id,
                'price' => $price,
                'gst' => $gst,
            ]);
            return redirect()->back()->with('success', 'Room price updated successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong.');
        }
    }
}
