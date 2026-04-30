<?php
namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSession;
use App\Models\CategoryMaster;
use App\Models\OccupantType;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessions = TeeSession::all();
        return view('admin.tee.sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.tee.sessions.create');
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
            'session_name_id' => 'required|numeric',
            'session_time_name' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        TeeSession::create($request->all());

        return redirect()->route('sessions.index')->with('success', 'TeeSession created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeeSession  $session
     * @return \Illuminate\View\View
     */
    public function edit(TeeSession $session)
    {
        return view('admin.tee.sessions.edit', compact('session'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeeSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeeSession $session)
    {
        $request->validate([
            'session_name_id' => 'required|numeric',
            'session_time_name' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $session->update($request->all());

        return redirect()->route('sessions.index')->with('success', 'TeeSession updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeeSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeeSession $session)
    {
        $session->delete();

        return redirect()->route('sessions.index')->with('success', 'TeeSession deleted successfully!');
    }
}
