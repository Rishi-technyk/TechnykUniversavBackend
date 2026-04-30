<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use Illuminate\Http\Request;
use App\Models\OccupantMaster;

class VenueChargesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = VenueCharge::orderBy('id', 'DESC')->get();
        return view('backend.venue_charges.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['occupant']   = OccupantMaster::where('status', 'Active')->get();
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();

        return view('backend.venue_charges.create', $data);
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
            'venue_id'      => 'required',
            'session_id'    => 'required',
            'occupant_id'   => 'required',
            'rate'          => 'required|numeric',
        ]); 
        $venueCharge = new VenueCharge();
        $venueCharge->venue_id      = $request->venue_id;
        $venueCharge->session_id    = $request->session_id;
        $venueCharge->occupant_id   = $request->occupant_id;
        $venueCharge->rate          = $request->rate;
        $venueCharge->status        = 'Active';
        $venueCharge->save();

        return redirect()->route('admin.venue_charges')->with('success', 'Venue Charge created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VenueCharges  $venueCharges
     * @return \Illuminate\Http\Response
     */
    public function show(VenueCharges $venueCharges)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VenueCharges  $venueCharges
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data']       = VenueCharge::findOrFail(decrypt($id));
        $data['occupant']   = OccupantMaster::where('status', 'Active')->get();
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();

        return view('backend.venue_charges.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VenueCharges  $venueCharges
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'venue_id'      => 'required',
            'session_id'    => 'required',
            'occupant_id'   => 'required',
            'rate'          => 'required|numeric',
        ]); 

        $venueCharge = VenueCharge::findOrFail(decrypt($id));
        $venueCharge->venue_id      = $request->venue_id;
        $venueCharge->session_id    = $request->session_id;
        $venueCharge->occupant_id   = $request->occupant_id;
        $venueCharge->rate          = $request->rate;
        $venueCharge->save();

        return redirect()->route('admin.venue_charges')->with('success', 'Venue Charge updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VenueCharges  $venueCharges
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $venueCharge = VenueCharge::findOrFail(decrypt($id));
        $venueCharge->delete();

        return redirect()->route('admin.venue_charges')->with('success', 'Venue Charge deleted successfully.');
    }
}
