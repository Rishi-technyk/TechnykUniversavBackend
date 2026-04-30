<?php

namespace App\Http\Controllers\Activity;

use App\Models\ActivityOccupantMaster;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityOccupantMasterController extends Controller
{

    public function index()
    {
        $data['datas'] = ActivityOccupantMaster::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.occupant-master.index', $data);
    }

    public function create()
    {
        return view('backend.activity.occupant-master.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric',
        ]);

        ActivityOccupantMaster::create([
            'name' => $request->name,
            'charge' => $request->charge,
            'additional_info' => $request->additional_info,
            'status' => 'Active',
        ]);

        return redirect()->route('admin.activity_occupant_masters')->with('success', 'Occupant created successfully.');

    }

    public function status($id)
    {
        $occupant = ActivityOccupantMaster::findOrFail(decrypt($id));
        $occupant->status = $occupant->status == 'Active' ? 'Inactive' : 'Active';
        $occupant->save();

        return redirect()->route('admin.activity_occupant_masters')->with('success', 'Occupant status updated successfully.');
    }

    public function edit($id)
    {
        $data['data'] = ActivityOccupantMaster::findOrFail(decrypt($id));

        return view('backend.activity.occupant-master.edit', $data);
    }

    
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'charge' => 'required|numeric',
        ]);

        $occupant = ActivityOccupantMaster::findOrFail(decrypt($id));
        
        $occupant->update([
            'name' => $request->name,
            'charge' => $request->charge,
            'additional_info' => $request->additional_info,
        ]);

        return redirect()->route('admin.activity_occupant_masters')->with('success', 'Occupant updated successfully.');

    }

    public function destroy($id)
    {
        $occupant = ActivityOccupantMaster::findOrFail(decrypt($id));

        $occupant->delete();

        return redirect()->route('admin.activity_occupant_masters')->with('success', 'Occupant deleted successfully.');
    }
}