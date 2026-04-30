<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivitySession;
use App\Models\FacilitySlot;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Facility;
use App\Models\Slot;

class FacilitySlotController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = FacilitySlot::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.facility-slot.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['session']    = ActivitySession::where('status', 'Active')->orderBy('id', 'DESC')->get();
        $data['slot']       = Slot::where('status', 'Active')->orderBy('id', 'DESC')->get();
        $data['facility']   = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();
        return view('backend.activity.facility-slot.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($request->slot_id) {

            $session_id = $request->session_id;
            $facility_id = $request->facility_id;
            $slot_ids = $request->slot_id;

            $existingSlots = FacilitySlot::where('session_id', $session_id)
                ->where('facility_id', $facility_id)
                ->whereIn('slot_id', $slot_ids)
                ->pluck('slot_id')
                ->toArray();

            $newSlots = array_diff($slot_ids, $existingSlots);

            $insertData = [];

            foreach ($newSlots as $slot_id) {
                $insertData[] = [
                    'session_id'  => $session_id,
                    'slot_id'     => $slot_id,
                    'facility_id' => $facility_id,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }

            if (!empty($insertData)) {
                FacilitySlot::insert($insertData);
            }
        }
        return redirect()->route('admin.facility_slots')->with('success', 'Facility slot created successfully.');
    }

    public function edit($id)
    {
        $data['data'] = FacilitySlot::findOrFail(decrypt($id));

        $data['session'] = ActivitySession::where('status', 'Active')->orderBy('id', 'DESC')->get();
        $data['slot'] = Slot::where('status', 'Active')->orderBy('id', 'DESC')->get();
        $data['facility'] = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('backend.activity.facility-slot.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $facilitySlot = FacilitySlot::findOrFail(decrypt($id));

        $facilitySlot->session_id = $request->session_id;
        $facilitySlot->slot_id = $request->slot_id;
        $facilitySlot->facility_id = $request->facility_id;

        $facilitySlot->save();

        return redirect()->route('admin.facility_slots')->with('success', 'Facility slot updated successfully.');
    }

    public function destroy($id)
    {
        $facilitySlot = FacilitySlot::findOrFail(decrypt($id));
        $facilitySlot->delete();
        return redirect()->route('admin.facility_slots')->with('success', 'Facility slot deleted successfully.');
    }
}
