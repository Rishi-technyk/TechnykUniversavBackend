<?php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSessionTime;

class TeeSessionTimeController extends Controller
{
    public function index()
    {
        $teeSessionTimes = TeeSessionTime::all();
        return view('admin.tee.tee-session-times.index', compact('teeSessionTimes'));
    }

    public function create()
    {
        return view('admin.tee.tee-session-times.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'session_name_id' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'required|boolean',
            'created_by' => 'required|numeric',
            'updated_by' => 'required|numeric'
        ]);

        TeeSessionTime::create($validatedData);

        return redirect()->route('tee-session-times.index')
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
        return view('admin.tee.tee-session-times.edit', compact('teeSessionTime'));
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'session_name_id' => 'required|numeric',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'required|boolean',
            'created_by' => 'required|numeric',
            'updated_by' => 'required|numeric'
        ]);

        $teeSessionTime = TeeSessionTime::findOrFail($id);
        $teeSessionTime->update($validatedData);

        return redirect()->route('tee-session-times.index')
            ->with('success', 'Tee Session Time updated successfully');
    }

    public function destroy($id)
    {
        $teeSessionTime = TeeSessionTime::findOrFail($id);
        $teeSessionTime->delete();

        return redirect()->route('tee-session-times.index')
            ->with('success', 'Tee Session Time deleted successfully');
    }
}
