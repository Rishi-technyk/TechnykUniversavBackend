<?php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSessionTime;
use App\Models\TeeSessionName;

class TeeSessionTimeController extends Controller
{
    public function index()
    {
        $teeSessionTimes = TeeSessionTime::where('is_active', true)->get();
        return view('admin.tee.tee-session-times.index', compact('teeSessionTimes'));
    }

    public function create()
    {
        $sessionNames = TeeSessionName::where('is_active', true)->get();
        return view('admin.tee.tee-session-times.create', compact('sessionNames'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'session_name_id' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

        TeeSessionTime::create($validatedData);

        return redirect()->route('session_manage')
            ->with('success', 'Tee Session Time created successfully');
    }

    public function show($id)
    {
        $teeSessionTime = TeeSessionTime::findOrFail($id);
        return view('admin.tee.tee-session-times.show', compact('teeSessionTime'));
    }

    public function edit($id)
    {
        $teeSessionTime = TeeSessionTime::findOrFail($id);
        $sessionNames = TeeSessionName::where('is_active', true)->get();
        return view('admin.tee.tee-session-times.edit', compact('teeSessionTime', 'sessionNames'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'session_name_id' => 'required|numeric',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time'
        ]);

        $teeSessionTime = TeeSessionTime::find($id);
        $teeSessionTime->update($validatedData);

        return redirect()->route('session_manage')
            ->with('success', 'Tee Session Time updated successfully');
    }

    public function status_update(Request $request)
    {
        
        $tableData= TeeSessionTime::find($request['id']);
        $tableData->is_active = $request['status'];

        if($tableData->save()){
            $success = 1;
        }else{
            $success = 0;
        }
        return response()->json([
            'success' => $success,
        ], 200);
    }

    public function destroy($id)
    {
        $teeSessionTime = TeeSessionTime::findOrFail($id);
        $teeSessionTime->delete();

        return redirect()->route('session_manage')
            ->with('success', 'Tee Session Time deleted successfully');
    }
}
