<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Session;
use App\Models\Room;
use Illuminate\Support\Facades\Auth;


class RoomController extends Controller
{
    public function view()
    {
        $rooms = Room::selectRaw("*")->get();
        return view('admin.room-list', compact('rooms'));
    }
    public function add()
    {
        return view('admin.room-add');
    }

    public function addStore(Request $request)
    {
//        echo "<pre>"; print_r($request->all());

        $request->validate(
            [
                'room_type' => 'required|unique:rooms,type|max:150|min:3',
                'short_description' => 'required|max:200|min:3',
                'total_rooms' => 'required|integer|lt:50|gte:0',
                'max_guest' => 'required|integer|lt:20|gte:1',
            ],
        );

        $room = new Room;

        $room->title = $request['room_type'];
        $room->type = $request['room_type'];
        $room->short_description = $request['short_description'];
        $room->total_rooms = $request['total_rooms'];
        $room->max_guest = $request['max_guest'];
        $room->status = '1';

        $room->save();

        return redirect()->route('admin.roomAdd')->with('success', 'Room Added successfully.');
//        return redirect(route('admin.roomAdd'));
    }

}
