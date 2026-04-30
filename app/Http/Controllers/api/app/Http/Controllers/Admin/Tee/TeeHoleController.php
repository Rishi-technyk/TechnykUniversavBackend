<?php
// app/Http/Controllers/TeeHoleController.php

namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Teehole;
use App\CPU\Helpers;
use App\Models\CategoryMaster;
use App\Models\OccupantType;
use Illuminate\Support\Facades\Auth;

class TeeHoleController extends Controller
{
  
    public function index()
    {
        $teeHoles = Teehole::all();
        return view('admin.tee.tee_holes.index', compact('teeHoles'));
    }

 
    public function create()
    {
        return view('admin.tee.tee_holes.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'hole_number' => 'required|numeric'
        ]);
        $request= Helpers::set_common_request($request);

        Teehole::create($request->all());

        return redirect()->route('tee_holes.index')->with('success', 'Tee Hole created successfully!');
    }

   
    public function edit(TeeHole $teeHole)
    {
        return view('admin.tee.tee_holes.edit', compact('teeHole'));
    }

   
    public function update(Request $request, Teehole $teeHole)
    {
        $request->validate([
            'hole_number' => 'required|numeric',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $teeHole->update($request->all());

        return redirect()->route('tee_holes.index')->with('success', 'Tee Hole updated successfully!');
    }

    
    public function destroy(Teehole $teeHole)
    {
        $teeHole->delete();

        return redirect()->route('tee_holes.index')->with('success', 'Tee Hole deleted successfully!');
    }

    public function status_update(Request $request)
    {
        $tableData= Teehole::find($request['id']);
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

?>