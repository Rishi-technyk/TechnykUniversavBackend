<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\ActivitySession;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivitySessionController extends Controller
{

    public function index()
    {
        $data['datas'] = ActivitySession::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.activity-session.index', $data);
    }

    public function create()
    {
        return view('backend.activity.activity-session.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $activitySession = new ActivitySession();
        $activitySession->name = $request->name;
        $activitySession->save();

        return redirect()->route('admin.activity_sessions')->with('success', 'Activity session created successfully.');
    }

    public function status($id)
    {
        $activitySession = ActivitySession::findOrFail(decrypt($id));
        $activitySession->status = $activitySession->status == 'Active' ? 'Inactive' : 'Active';
        $activitySession->save();

        return redirect()->route('admin.activity_sessions')->with('success', 'Activity session status updated successfully.');
    }

    public function edit($id)
    {
        $data['data'] = ActivitySession::findOrFail(decrypt($id));

        return view('backend.activity.activity-session.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $activitySession = ActivitySession::findOrFail(decrypt($id));
        $activitySession->name = $request->name;
        $activitySession->save();

        return redirect()->route('admin.activity_sessions')->with('success', 'Activity session updated successfully.');
    }

    public function destroy($id)
    {
        $activitySession = ActivitySession::findOrFail(decrypt($id));

        $activitySession->delete();

        return redirect()->route('admin.activity_sessions')->with('success', 'Activity session deleted successfully.');
    }
}