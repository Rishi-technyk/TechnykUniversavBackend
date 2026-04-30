<?php

namespace App\Http\Controllers;

use App\Models\BlockRoom;
use Illuminate\Http\Request;
use App\Models\RoomCategoryMaster;

class BlockRoomController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = BlockRoom::orderBy('id', 'DESC')->get();
        return view('backend.block_rooms.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['rooms'] = RoomCategoryMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('backend.block_rooms.create', $data);
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
            'room_category_id' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $blockRoom = new BlockRoom();
        $blockRoom->room_category_id = $request->room_category_id;
        $blockRoom->from_date = $request->from_date.' '.env('CheckIn',null);
        $blockRoom->to_date = $request->to_date.' '.env('CheckIn',null);
        $blockRoom->remark = $request->remark;
        $blockRoom->blocked_room = $request->blocked_room;
        $blockRoom->save();

        return redirect()->route('admin.block_rooms')->with('success', 'Block Room created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BlockRoom  $blockRoom
     * @return \Illuminate\Http\Response
     */
    public function show(BlockRoom $blockRoom)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BlockRoom  $blockRoom
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['rooms'] = RoomCategoryMaster::where('status', 'Active')->orderBy('id', 'DESC')->get();
        $data['data'] = BlockRoom::findOrFail(decrypt($id));
        return view('backend.block_rooms.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BlockRoom  $blockRoom
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'room_category_id' => 'required',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);

        $blockRoom = BlockRoom::findOrFail(decrypt($id));
        $blockRoom->room_category_id = $request->room_category_id;
        $blockRoom->from_date = $request->from_date.' '.env('CheckIn',null);
        $blockRoom->to_date = $request->to_date.' '.env('CheckIn',null);
        $blockRoom->blocked_room = $request->blocked_room;
        $blockRoom->remark = $request->remark;
        $blockRoom->save();

        return redirect()->route('admin.block_rooms')->with('success', 'Block Room updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BlockRoom  $blockRoom
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        BlockRoom::destroy(decrypt($id));
        return redirect()->route('admin.block_rooms')->with('success', 'Block Room deleted successfully.');
    }
}
