<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\BlockSlot;
use App\Models\Facility;
use App\Models\Slot;

class BlockSlotController extends Controller
{

    public function index()
    {
        $data['datas'] = BlockSlot::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.block-slot.index', $data);
    }

    public function create()
    {
        $data['facility'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();
        
        $data['slot'] = Slot::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('backend.activity.block-slot.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required',
            'slot_id' => 'required',
            'date' => 'required',
        ]);

        $blockSlot = new BlockSlot();
        $blockSlot->facility_id = $request->facility_id;
        $blockSlot->slot_id = $request->slot_id;
        $blockSlot->date = $request->date;
        $blockSlot->remark = $request->remark;
        $blockSlot->save();

        return redirect()->route('admin.block_slots')->with('success', 'Block slot created successfully.');

    }

    public function edit($id)
    {
        $data['data'] = BlockSlot::findOrFail(decrypt($id));

        $data['facility'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();
        
        $data['slot'] = Slot::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('backend.activity.block-slot.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Slot  $slot
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'facility_id' => 'required',
            'slot_id' => 'required',
            'date' => 'required',
        ]);

        $blockSlot = BlockSlot::findOrFail(decrypt($id));
        $blockSlot->facility_id = $request->facility_id;
        $blockSlot->slot_id = $request->slot_id;
        $blockSlot->date = $request->date;
        $blockSlot->remark = $request->remark;
        $blockSlot->save();

        return redirect()->route('admin.block_slots')->with('success', 'Block slot updated successfully.');
    }

    public function destroy($id)
    {
        $blockSlot = BlockSlot::findOrFail(decrypt($id));

        $blockSlot->delete();

        return redirect()->route('admin.block_slots')->with('success', 'Block slot deleted successfully.');
    }
}