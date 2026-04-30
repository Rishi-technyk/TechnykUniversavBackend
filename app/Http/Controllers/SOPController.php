<?php

namespace App\Http\Controllers;

use App\Models\SOP;
use Illuminate\Http\Request;

class SOPController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function room_sop()
    {
        $data = SOP::where('type', 'Room Booking')->first();
        return view('backend.sop.room_sop', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function banquet_sop()
    {
        $data = SOP::where('type', 'Banquet Booking')->first();
        return view('backend.sop.banquet_sop', compact('data'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update_room_sop(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $sop = SOP::where('type', 'Room Booking')->first();

        if ($sop) {
            $sop->update(['content' => $request->content]);
        } else {
            SOP::create([
                'type' => 'Room Booking',
                'content' => $request->content
            ]);
        }

        return redirect()->back()->with('success', 'Room SOP updated successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\SOP  $sOP
     * @return \Illuminate\Http\Response
     */
    public function show(SOP $sOP)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\SOP  $sOP
     * @return \Illuminate\Http\Response
     */
    public function edit(SOP $sOP)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\SOP  $sOP
     * @return \Illuminate\Http\Response
     */
    public function update_banquet_sop(Request $request)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $sop = SOP::where('type', 'Banquet Booking')->first();

        if ($sop) {
            $sop->update(['content' => $request->content]);
        } else {
            SOP::create([
                'type' => 'Banquet Booking',
                'content' => $request->content
            ]);
        }

        return redirect()->back()->with('success', 'Banquet SOP updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\SOP  $sOP
     * @return \Illuminate\Http\Response
     */
    public function destroy(SOP $sOP)
    {
        //
    }
}
