<?php

namespace App\Http\Controllers\Activity;

use App\Models\ActivityCancellationPolicy;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Facility;

class ActivityCancellationPolicyController extends Controller
{

    public function index()
    {
        $data['datas'] = ActivityCancellationPolicy::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.activity-cancellation-policy.index', $data);
    }

    public function create()
    {
        $data['facility']   = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();
        return view('backend.activity.activity-cancellation-policy.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'facility_id' => 'required',
            'from_days' => 'required|integer|min:0',
            'to_days' => 'required|integer|min:0',
            'deduction' => 'required|numeric|min:0',
            'GST' => 'required|numeric|min:0|max:100',
        ]);

        $data = new ActivityCancellationPolicy();
        $data->facility_id = $request->facility_id;
        $data->from_days = $request->from_days;
        $data->to_days = $request->to_days;
        $data->deduction = $request->deduction;
        $data->GST = $request->GST;
        $data->save();

        return redirect()->route('admin.activity_cancellation_policies')->with('success', 'Activity cancellation policy created successfully.');
    }

    public function edit($id)
    {
        $data['data'] = ActivityCancellationPolicy::findOrFail(decrypt($id));

        $data['facility']   = Facility::where('status', 'Active')->orderBy('id', 'DESC')->get();

        return view('backend.activity.activity-cancellation-policy.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'facility_id' => 'required',
            'from_days' => 'required|integer|min:0',
            'to_days' => 'required|integer|min:0',
            'deduction' => 'required|numeric|min:0',
            'GST' => 'required|numeric|min:0|max:100',
        ]);

        $data = ActivityCancellationPolicy::findOrFail(decrypt($id));
        $data->facility_id = $request->facility_id;
        $data->from_days = $request->from_days;
        $data->to_days = $request->to_days;
        $data->deduction = $request->deduction;
        $data->GST = $request->GST;
        $data->save();

        return redirect()->route('admin.activity_cancellation_policies')->with('success', 'Activity cancellation policy updated successfully.');
    }

    public function destroy($id)
    {
        $data = ActivityCancellationPolicy::findOrFail(decrypt($id));

        $data->delete();

        return redirect()->route('admin.activity_cancellation_policies')->with('success', 'Activity cancellation policy deleted successfully.');
    }
}