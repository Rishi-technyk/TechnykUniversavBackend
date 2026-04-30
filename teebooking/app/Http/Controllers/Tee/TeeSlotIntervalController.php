<?php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSlotInterval;
use App\Models\TeeSessionName;
use App\Models\TeeHole;
use App\Models\TeeSheet;
use App\Models\TeeSessionTime;


class TeeSlotIntervalController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teeSlotIntervals = TeeSlotInterval::all();
        return view('admin.tee.tee_slot_intervals.index', compact('teeSlotIntervals'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $teeSheets = TeeSheet::where('is_active', true)->get();
        $sessionNames = TeeSessionName::where('is_active', true)->get();
        $sessionTimes = TeeSessionTime::where('is_active', true)->get();
        $teeHoles = TeeHole::where('is_active', true)->get();

        return view('admin.tee.tee_slot_intervals.create', compact('teeSheets', 'sessionNames', 'sessionTimes', 'teeHoles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'tee_sheet_id' => 'required|numeric',
            'session_name_id' => 'required|numeric',
            'session_time_id' => 'required|numeric',
            'slot_interval' => 'required|string',
            'tee_off_hole' => 'required|string',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        TeeSlotInterval::create($request->all());

        return redirect()->route('tee_slot_intervals.index')->with('success', 'Tee Slot Interval created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeeSlotInterval  $teeSlotInterval
     * @return \Illuminate\View\View
     */
    public function edit(TeeSlotInterval $teeSlotInterval)
    {
        $teeSheets = TeeSheet::where('is_active', true)->get();
        $sessionNames = TeeSessionName::where('is_active', true)->get();
        $sessionTimes = TeeSessionTime::where('is_active', true)->get();
        $teeHoles = TeeHole::where('is_active', true)->get();

        return view('admin.tee.tee_slot_intervals.edit', compact('teeSlotInterval', 'teeSheets', 'sessionNames', 'sessionTimes', 'teeHoles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeeSlotInterval  $teeSlotInterval
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeeSlotInterval $teeSlotInterval)
    {
        $request->validate([
            'tee_sheet_id' => 'required|numeric',
            'session_name_id' => 'required|numeric',
            'session_time_id' => 'required|numeric',
            'slot_interval' => 'required|string',
            'tee_off_hole' => 'required|string',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $teeSlotInterval->update($request->all());

        return redirect()->route('tee_slot_intervals.index')->with('success', 'Tee Slot Interval updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeeSlotInterval  $teeSlotInterval
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeeSlotInterval $teeSlotInterval)
    {
        $teeSlotInterval->delete();

        return redirect()->route('tee_slot_intervals.index')->with('success', 'Tee Slot Interval deleted successfully!');
    }
}
