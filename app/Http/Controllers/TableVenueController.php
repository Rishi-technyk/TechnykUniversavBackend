<?php

namespace App\Http\Controllers;

use App\Models\TableVenue;
use Illuminate\Http\Request;

class TableVenueController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = TableVenue::orderBy('id', 'desc')->get();
        return view('backend.table_master.venue.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.table_master.venue.create');
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
            'name' => 'required',
        ]);

        $tableVenue = new TableVenue();
        $tableVenue->name = $request->name;
        $tableVenue->save();

        return redirect()->route('admin.table_venues')->with('success', 'Table Venue created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TableVenue  $tableVenue
     * @return \Illuminate\Http\Response
     */
    public function show(TableVenue $tableVenue)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TableVenue  $tableVenue
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = TableVenue::findOrFail(decrypt($id));
        return view('backend.table_master.venue.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableVenue  $tableVenue
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tableVenue = TableVenue::findOrFail(decrypt($id));
        $request->validate([
            'name' => 'required',
        ]);
        $tableVenue->name = $request->name;
        $tableVenue->save();

        return redirect()->route('admin.table_venues')->with('success', 'Table Venue updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableVenue  $tableVenue
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tableVenue = TableVenue::findOrFail(decrypt($id));
        $tableVenue->delete();

        return redirect()->route('admin.table_venues')->with('success', 'Table Venue deleted successfully.');
    }

    function status($id)
    {
        $tableVenue = TableVenue::findOrFail(decrypt($id));
        if ($tableVenue->status == "Active") {
            $tableVenue->status = "Inactive";
        } else {
            $tableVenue->status = "Active";
        }
        $tableVenue->save();

        return redirect()->route('admin.table_venues')->with('success', 'Table Venue status updated successfully.');
        
    }
}
