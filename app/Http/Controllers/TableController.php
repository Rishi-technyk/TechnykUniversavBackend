<?php

namespace App\Http\Controllers;

use App\Models\Table;
use App\Models\TableMeal;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = Table::orderBy('id', 'desc')->get();
        return view('backend.table_master.table.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['meals'] = TableMeal::where('status', 'Active')->get();
        return view('backend.table_master.table.create', $data);
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
            'name' => 'required',
            'meal_id' => 'required',
        ]);

        $table = new Table();
        $table->name = $request->name;
        $table->meal_id = $request->meal_id;
        $table->save();
        return redirect()->route('admin.tables')->with('success', 'Table created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function show(Table $table)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = Table::find(decrypt($id));
        $data['meals'] = TableMeal::where('status', 'Active')->get();
        return view('backend.table_master.table.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'meal_id' => 'required',
        ]);

        $table = Table::find(decrypt($id));
        $table->name = $request->name;
        $table->meal_id = $request->meal_id;
        $table->save();
        return redirect()->route('admin.tables')->with('success', 'Table updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Table  $table
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $table = Table::find(decrypt($id));
        $table->delete();
        return redirect()->route('admin.tables')->with('success', 'Table deleted successfully.');
    }

    function status($id)
    {
        $table = Table::find(decrypt($id));
        if ($table->status == 'Active') {
            $table->status = 'Inactive';
            $message = 'Table Inactive successfully.';
        } else {
            $table->status = 'Active';
            $message = 'Table Active successfully.';
        }
        $table->save();
        return redirect()->route('admin.tables')->with('success', $message);
    }
}
