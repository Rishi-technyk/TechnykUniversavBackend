<?php

namespace App\Http\Controllers;

use App\Models\Session;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = Session::orderBy('id', 'DESC')->get();
        return view('backend.session.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.session.create');
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
            'name' => 'required|string|max:255|unique:sessions,name',
        ]);
        $session = new Session();
        $session->name = $request->name;
        $session->status = "Active";
        $session->save();
        return redirect()->route('admin.sessions')->with('success', 'Session created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function show(Session $session)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['session'] = Session::findOrFail(decrypt($id));
        return view('backend.session.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:sessions,name,' . decrypt($id),
        ]);
        $session = Session::findOrFail(decrypt($id));
        $session->name = $request->name;
        $session->save();
        return redirect()->route('admin.sessions')->with('success', 'Session updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Session  $session
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $session = Session::findOrFail(decrypt($id));
        $session->delete();
        return redirect()->route('admin.sessions')->with('success', 'Session deleted successfully.');
    }

    function status($id)
    {
        $session = Session::findOrFail(decrypt($id));
        if ($session->status == "Active") {
            $session->status = "Inactive";
        } else {
            $session->status = "Active";
        }
        $session->save();
        return redirect()->route('admin.sessions')->with('success', 'Session status updated successfully.');
    }
}
