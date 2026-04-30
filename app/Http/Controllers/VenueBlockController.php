<?php

namespace App\Http\Controllers;

use App\Models\Session;
use App\Models\VenueBlock;
use App\Models\VenueMaster;
use Illuminate\Http\Request;

class VenueBlockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = VenueBlock::orderBy('id', 'DESC')->get();
        return view('backend.venue_blocks.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();
        return view('backend.venue_blocks.create', $data);
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
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
        ]);

        $venueBlock = new VenueBlock();
        $venueBlock->venue_id      = $request->venue_id;
        $venueBlock->session_id    = $request->session_id;
        $venueBlock->from_date     = $request->from_date;
        $venueBlock->to_date       = $request->to_date;
        $venueBlock->remark        = $request->remark;
        $venueBlock->save();

        return redirect()->route('admin.venue_blocks')->with('success', 'Venue Block created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\VenueBlock  $venueBlock
     * @return \Illuminate\Http\Response
     */
    public function show(VenueBlock $venueBlock)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\VenueBlock  $venueBlock
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data']       = VenueBlock::find(decrypt($id));
        $data['venue']      = VenueMaster::where('status', 'Active')->get();
        $data['session']    = Session::where('status', 'Active')->get();
        return view('backend.venue_blocks.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\VenueBlock  $venueBlock
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'venue_id'      => 'required',
            'session_id'    => 'required',
            'from_date'     => 'required|date',
            'to_date'       => 'required|date|after_or_equal:from_date',
        ]);

        $data = VenueBlock::find(decrypt($id));
        $data->venue_id      = $request->venue_id;
        $data->session_id    = $request->session_id;
        $data->from_date     = $request->from_date;
        $data->to_date       = $request->to_date;
        $data->remark        = $request->remark;
        $data->save();

        return redirect()->route('admin.venue_blocks')->with('success', 'Venue Block updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\VenueBlock  $venueBlock
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = VenueBlock::find(decrypt($id));
        $data->delete();

        return redirect()->route('admin.venue_blocks')->with('success', 'Venue Block deleted successfully.');
    }
}
