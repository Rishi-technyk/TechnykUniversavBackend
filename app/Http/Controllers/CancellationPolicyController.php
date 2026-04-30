<?php

namespace App\Http\Controllers;

use App\Models\VenueMaster;
use Illuminate\Http\Request;
use App\Models\CancellationPolicy;

class CancellationPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = CancellationPolicy::orderBy('id', 'DESC')->get();
        return view('backend.cancellation_policies.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['venue'] = VenueMaster::where('status', 'Active')->orderBy('name', 'ASC')->get();
        return view('backend.cancellation_policies.create', $data);
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
            'to_days' => 'required|integer',
            'venue_id' => 'required|integer',
            'deduction' => 'required|numeric',
            'GST' => 'required|numeric',
        ]);

        $cancellationPolicy = new CancellationPolicy();
        $cancellationPolicy->to_days = $request->to_days;
        $cancellationPolicy->venue_id = $request->venue_id;
        $cancellationPolicy->deduction = $request->deduction;
        $cancellationPolicy->GST = $request->GST;
        $cancellationPolicy->save();

        return redirect()->route('admin.cancellation_policies')->with('success', 'Cancellation Policy created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\CancellationPolicy  $cancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function show(CancellationPolicy $cancellationPolicy)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\CancellationPolicy  $cancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = CancellationPolicy::findOrFail(decrypt($id));
        $data['venue'] = VenueMaster::where('status', 'Active')->orderBy('name', 'ASC')->get();
        return view('backend.cancellation_policies.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\CancellationPolicy  $cancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, CancellationPolicy $cancellationPolicy)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\CancellationPolicy  $cancellationPolicy
     * @return \Illuminate\Http\Response
     */
    public function destroy(CancellationPolicy $cancellationPolicy)
    {
        //
    }
}
