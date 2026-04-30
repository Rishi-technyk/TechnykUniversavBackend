<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OccupantMaster;

class OccupantMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = OccupantMaster::orderBy('id', 'DESC')->get();

        return view('backend.occupant_master.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.occupant_master.create');
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
            'name' => 'required|string|max:255',
            'additional_info' => 'required|in:Yes,No',
        ]);

        $occupant = new OccupantMaster();
        $occupant->name = $request->name;
        $occupant->additional_info = $request->additional_info;
        $occupant->status = "Active";
        $occupant->save();

        return redirect()->route('admin.occupants')->with('success', 'Occupant created successfully.');
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
        $data['data'] = OccupantMaster::findOrFail(decrypt($id));
        return view('backend.occupant_master.edit', $data);
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
            'name' => 'required|string|max:255',
            'additional_info' => 'required|in:Yes,No',
        ]);

        $occupant = OccupantMaster::findOrFail(decrypt($id));
        $occupant->name = $request->name;
        $occupant->additional_info = $request->additional_info;
        $occupant->save();

        return redirect()->route('admin.occupants')->with('success', 'Occupant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $occupant = OccupantMaster::findOrFail(decrypt($id));
        $occupant->delete();

        return redirect()->route('admin.occupants')->with('success', 'Occupant deleted successfully.');
    }

    public function status($id)
    {
        $occupant = OccupantMaster::findOrFail(decrypt($id));
        $occupant->status = $occupant->status == 'Active' ? 'Inactive' : 'Active';
        $occupant->save();

        return redirect()->route('admin.occupants')->with('success', 'Occupant status updated successfully.');
    }
}
