<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoryType;
use App\Models\CategoryMaster;
use App\Models\OccupantMaster;
use App\Models\RoomChargesMaster;
use App\Models\RoomCategoryMaster;

class RoomChargesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = RoomChargesMaster::orderBy('id', 'DESC')->get();
        return view('backend.room_charges.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['catgeory']       = CategoryMaster::where('status', 'Active')->get();
        $data['catgeory_type']  = CategoryType::where('status', 'Active')->get();
        $data['occupants']      = OccupantMaster::where('status', 'Active')->get();
        $data['room_cates']     = RoomCategoryMaster::where('status', 'Active')->get();
        return view('backend.room_charges.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id'       => 'required',
            'category_type_id'  => 'required',
            'occupant_type_id'  => 'required',
            'room_category_id'  => 'required',
            'charges'           => 'required',
            'no_of_booked_room' => 'required',
            'max_no_of_nites'   => 'required',
        ]);

        $data                   = new RoomChargesMaster();
        $data->category_id      = $request->category_id;
        $data->category_type_id = $request->category_type_id;
        $data->occupant_type_id = $request->occupant_type_id;
        $data->room_category_id = $request->room_category_id;
        $data->charges_nite     = $request->charges;
        $data->no_of_booked_room= $request->no_of_booked_room;
        $data->max_no_of_nites  = $request->max_no_of_nites;
        $data->status           = 'Active';
        $data->save();

        return redirect()->route('admin.room_charges')->with('success', 'Room Charge Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data']           = RoomChargesMaster::find(decrypt($id));
        $data['catgeory']       = CategoryMaster::where('status', 'Active')->get();
        $data['catgeory_type']  = CategoryType::where('status', 'Active')->get();
        $data['occupants']      = OccupantMaster::where('status', 'Active')->get();
        $data['room_cates']     = RoomCategoryMaster::where('status', 'Active')->get();
        return view('backend.room_charges.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_id'       => 'required',
            'category_type_id'  => 'required',
            'occupant_type_id'  => 'required',
            'room_category_id'  => 'required',
            'charges'           => 'required',
            'no_of_booked_room' => 'required',
            'max_no_of_nites'   => 'required',
        ]);

        $data                   = RoomChargesMaster::find(decrypt($id));
        $data->category_id      = $request->category_id;
        $data->category_type_id = $request->category_type_id;
        $data->occupant_type_id = $request->occupant_type_id;
        $data->room_category_id = $request->room_category_id;
        $data->charges_nite     = $request->charges;
        $data->no_of_booked_room= $request->no_of_booked_room;
        $data->max_no_of_nites  = $request->max_no_of_nites;
        $data->save();

        return redirect()->route('admin.room_charges')->with('success', 'Room Charge Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = RoomChargesMaster::find(decrypt($id));
        $data->delete();

        return redirect()->route('admin.room_charges')->with('success', 'Room Charge Deleted Successfully');
    }

    function status($id)
    {
        $data = RoomChargesMaster::find(decrypt($id));
        if ($data->status == 'Active') {
            $data->status = 'Inactive';
        } else {
            $data->status = 'Active';
        }
        $data->save();

        return redirect()->route('admin.room_charges')->with('success', 'Room Charge Status Updated Successfully');
    }
}
