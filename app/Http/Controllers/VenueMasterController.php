<?php

namespace App\Http\Controllers;

use App\Models\VenueMaster;
use Illuminate\Http\Request;

class VenueMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = VenueMaster::orderBy('id', 'DESC')->get();
        return view('backend.venue_master.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.venue_master.create');
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
            'capacity' => 'required|integer|min:1',
            'gst' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
        ]);

        $venueMaster = new VenueMaster();
        $venueMaster->name = $request->name;
        $venueMaster->capacity = $request->capacity;
        $venueMaster->GSTper = $request->gst;
        $venueMaster->security_deposit = $request->security_deposit;
        $venueMaster->save();

        return redirect()->route('admin.venue_masters')->with('success', 'Venue Master created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VenueMaster  $venueMaster
     * @return \Illuminate\Http\Response
     */
    public function show(VenueMaster $venueMaster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VenueMaster  $venueMaster
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = VenueMaster::findOrFail(decrypt($id));
        return view('backend.venue_master.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VenueMaster  $venueMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $venueMaster = VenueMaster::findOrFail(decrypt($id));
        $request->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'gst' => 'required|numeric|min:0',
            'security_deposit' => 'required|numeric|min:0',
        ]);

        $venueMaster->name = $request->name;
        $venueMaster->capacity = $request->capacity;
        $venueMaster->GSTper = $request->gst;
        $venueMaster->security_deposit = $request->security_deposit;
        $venueMaster->save();

        return redirect()->route('admin.venue_masters')->with('success', 'Venue Master updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VenueMaster  $venueMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $venueMaster = VenueMaster::findOrFail(decrypt($id));
        $venueMaster->delete();

        return redirect()->route('admin.venue_masters')->with('success', 'Venue Master deleted successfully.');
    }

    function status($id)
    {
        $venueMaster = VenueMaster::findOrFail(decrypt($id));
        if ($venueMaster->status == "Active") {
            $venueMaster->status = "Inactive";
        } else {
            $venueMaster->status = "Active";
        }
        $venueMaster->save();

        return redirect()->route('admin.venue_masters')->with('success', 'Venue Master status updated successfully.');
    }
}
