<?php

namespace App\Http\Controllers;

use App\Models\BanquetOccupant;
use Illuminate\Http\Request;

class BanquetOccupantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = BanquetOccupant::orderBy('id', 'DESC')->get();
        return view('backend.banquet_occupant.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.banquet_occupant.create');
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

        $occupant = new BanquetOccupant();
        $occupant->name = $request->name;
        $occupant->additional_info = $request->additional_info;
        $occupant->status = "Active";
        $occupant->save();

        return redirect()->route('admin.banquet_occupants')->with('success', 'Occupant created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BanquetOccupant  $banquetOccupant
     * @return \Illuminate\Http\Response
     */
    public function show(BanquetOccupant $banquetOccupant)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BanquetOccupant  $banquetOccupant
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = BanquetOccupant::find(decrypt($id));
        return view('backend.banquet_occupant.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BanquetOccupant  $banquetOccupant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'additional_info' => 'required|in:Yes,No',
        ]);

        $occupant = BanquetOccupant::findOrFail(decrypt($id));
        $occupant->name = $request->name;
        $occupant->additional_info = $request->additional_info;
        $occupant->save();

        return redirect()->route('admin.banquet_occupants')->with('success', 'Occupant updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BanquetOccupant  $banquetOccupant
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $occupant = BanquetOccupant::findOrFail(decrypt($id));
        $occupant->delete();

        return redirect()->route('admin.banquet_occupants')->with('success', 'Occupant deleted successfully.');
    }

    public function status($id)
    {
        $occupant = BanquetOccupant::findOrFail(decrypt($id));
        $occupant->status = $occupant->status == 'Active' ? 'Inactive' : 'Active';
        $occupant->save();

        return redirect()->route('admin.banquet_occupants')->with('success', 'Occupant status updated successfully.');
    }
}
