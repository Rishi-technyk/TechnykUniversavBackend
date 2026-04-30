<?php

namespace App\Http\Controllers;

use App\Models\TableMeal;
use Illuminate\Http\Request;

class TableMealController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = TableMeal::orderBy('id', 'desc')->get();
        return view('backend.table_master.meal.index', $data);   
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.table_master.meal.create');
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
        ]);

        $tableMeal = new TableMeal();
        $tableMeal->name = $request->name;
        $tableMeal->save();

        return redirect()->route('admin.table_meals')->with('success', 'Table Meal created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TableMeal  $tableMeal
     * @return \Illuminate\Http\Response
     */
    public function show(TableMeal $tableMeal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TableMeal  $tableMeal
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = TableMeal::find(decrypt($id));
        return view('backend.table_master.meal.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TableMeal  $tableMeal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $tableMeal = TableMeal::find(decrypt($id));
        $tableMeal->name = $request->name;
        $tableMeal->save();

        return redirect()->route('admin.table_meals')->with('success', 'Table Meal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TableMeal  $tableMeal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tableMeal = TableMeal::find(decrypt($id));
        $tableMeal->delete();

        return redirect()->route('admin.table_meals')->with('success', 'Table Meal deleted successfully.');
    }
}
