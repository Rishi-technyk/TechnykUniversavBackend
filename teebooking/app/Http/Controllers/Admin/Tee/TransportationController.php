<?php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transportation;


class TransportationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $transportations = Transportation::all();
        return view('admin.tee.transportation.index', compact('transportations'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.tee.transportation.create');
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
            'name' => 'required|string',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        Transportation::create($request->all());

        return redirect()->route('service_manage')->with('success', 'Transportation created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Transportation  $transportation
     * @return \Illuminate\View\View
     */
    public function edit(Transportation $transportation)
    {
        return view('admin.tee.transportation.edit', compact('transportation'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Transportation  $transportation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transportation $transportation)
    {
        $request->validate([
            'name' => 'required|string',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $transportation->update($request->all());

        return redirect()->route('service_manage')->with('success', 'Transportation updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Transportation  $transportation
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transportation $transportation)
    {
        $transportation->delete();

        return redirect()->route('service_manage')->with('success', 'Transportation deleted successfully!');
    }

    public function status_update(Request $request)
    {
        $tableData= Transportation::find($request['id']);
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
}
