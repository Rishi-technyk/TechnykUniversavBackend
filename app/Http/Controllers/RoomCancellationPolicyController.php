<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomCancellationPolicy;

class RoomCancellationPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = RoomCancellationPolicy::orderBy('id', 'DESC')->get();
        return view('backend.room_cancellation_policies.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.room_cancellation_policies.create');
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
            'from_days' => 'required|integer|min:0',
            'to_days' => 'required|integer|min:0|gte:from_days',
            'deduction' => 'required|numeric|min:0|max:100',
            'GST' => 'required|numeric|min:0|max:100',
        ]);

        $data = new RoomCancellationPolicy();
        $data->from_days = $request->from_days;
        $data->to_days = $request->to_days;
        $data->deduction = $request->deduction;
        $data->GST = $request->GST;
        $data->save();

        return redirect()->route('admin.room_cancellation_policies')->with('success', 'Room Cancellation Policy created successfully.');
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
        $data = RoomCancellationPolicy::findOrFail(decrypt($id));
        return view('backend.room_cancellation_policies.edit', compact('data'));
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
            'from_days' => 'required|integer|min:0',
            'to_days' => 'required|integer|min:0|gte:from_days',
            'deduction' => 'required|numeric|min:0|max:100',
            'GST' => 'required|numeric|min:0|max:100',
        ]);

        $data = RoomCancellationPolicy::findOrFail(decrypt($id));
        $data->from_days = $request->from_days;
        $data->to_days = $request->to_days;
        $data->deduction = $request->deduction;
        $data->GST = $request->GST;
        $data->save();

        return redirect()->route('admin.room_cancellation_policies')->with('success', 'Room Cancellation Policy updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = RoomCancellationPolicy::findOrFail(decrypt($id));
        $data->delete();    
        return redirect()->route('admin.room_cancellation_policies')->with('success', 'Room Cancellation Policy deleted successfully.');
    }
}
