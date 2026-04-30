<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Slot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SlotController extends Controller
{

    public function index()
    {
        $data['datas'] = Slot::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.slot.index', $data);
    }

    public function create()
    {
        return view('backend.activity.slot.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'value' => 'required',
            'label' => 'required',
        ]);

        $slot = new Slot();
        $slot->name = $request->value;
        $slot->label = $request->label;
        $slot->save();

        return redirect()->route('admin.slots')->with('success', 'Slot created successfully.');
    }

    public function status($id)
    {
        $slot = Slot::findOrFail(decrypt($id));
        $slot->status = $slot->status == 'Active' ? 'Inactive' : 'Active';
        $slot->save();

        return redirect()->route('admin.slots')->with('success', 'Slot status updated successfully.');
    }

    public function edit($id)
    {
        $data['data'] = Slot::findOrFail(decrypt($id));

        return view('backend.activity.slot.edit', $data);
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
            'value' => 'required',
            'label' => 'required',
        ]);

        $slot = Slot::findOrFail(decrypt($id));
        $slot->name = $request->value;
        $slot->label = $request->label;
        $slot->save();

        return redirect()->route('admin.slots')->with('success', 'Slot updated successfully.');
    }

    public function destroy($id)
    {
        $slot = Slot::findOrFail(decrypt($id));

        $slot->delete();

        return redirect()->route('admin.slots')->with('success', 'Slot deleted successfully.');
    }
}