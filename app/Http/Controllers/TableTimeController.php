<?php

namespace App\Http\Controllers;

use App\Models\TableTime;
use App\Models\TableMeal;
use Illuminate\Http\Request;

class TableTimeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = TableTime::orderBy('id', 'desc')->get();
        return view('backend.table_master.timing.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meals'] = TableMeal::where('status', 'Active')->get();
        return view('backend.table_master.timing.create', $data);
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
            'time' => 'required',
            'meal_id' => 'required',
        ]);

        $tableTime = new TableTime();
        $tableTime->time = $request->time;
        $tableTime->meal_id = $request->meal_id;
        $tableTime->save();

        return redirect()->route('admin.table_times')->with('success', 'Time created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TableTime  $tableTime
     * @return \Illuminate\Http\Response
     */
    public function show(TableTime $tableTime)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TableTime  $tableTime
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = TableTime::find(decrypt($id));
        $data['meals'] = TableMeal::where('status', 'Active')->get();
        return view('backend.table_master.timing.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableTime  $tableTime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'time' => 'required',
            'meal_id' => 'required',
        ]);

        $tableTime = TableTime::find(decrypt($id));
        $tableTime->time = $request->time;
        $tableTime->meal_id = $request->meal_id;
        $tableTime->save();

        return redirect()->route('admin.table_times')->with('success', 'Time updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableTime  $tableTime
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tableTime = TableTime::find(decrypt($id));
        $tableTime->delete();

        return redirect()->route('admin.table_times')->with('success', 'Time deleted successfully.');
    }

    function status($id)
    {
        $tableTime = TableTime::find(decrypt($id));
        if ($tableTime->status == 'Active') {
            $tableTime->status = 'Inactive';
        } else {
            $tableTime->status = 'Active';
        }
        $tableTime->save();

        return redirect()->route('admin.table_times')->with('success', 'Time status updated successfully.');
        
    }
}
