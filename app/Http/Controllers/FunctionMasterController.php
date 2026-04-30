<?php

namespace App\Http\Controllers;

use App\Models\FunctionMaster;
use Illuminate\Http\Request;

class FunctionMasterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas']=FunctionMaster::orderBy('id', 'DESC')->get();
        return view('backend.function.index',$data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.function.create');
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
            'name'=>'required|string|max:255',
            'description'=>'required|string|max:255',
        ]);

        $functionMaster=new FunctionMaster();
        $functionMaster->name=$request->name;
        $functionMaster->description=$request->description;
        $functionMaster->save();

        return redirect()->route('admin.functions')->with('success','Function created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\FunctionMaster  $functionMaster
     * @return \Illuminate\Http\Response
     */
    public function show(FunctionMaster $functionMaster)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\FunctionMaster  $functionMaster
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data']=FunctionMaster::findOrFail(decrypt($id));
        return view('backend.function.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\FunctionMaster  $functionMaster
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'description'=>'required|string|max:255',
        ]);

        $functionMaster=FunctionMaster::findOrFail(decrypt($id));
        $functionMaster->name=$request->name;
        $functionMaster->description=$request->description;
        $functionMaster->save();

        return redirect()->route('admin.functions')->with('success','Function updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\FunctionMaster  $functionMaster
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $functionMaster=FunctionMaster::findOrFail(decrypt($id));
        $functionMaster->delete();

        return redirect()->route('admin.functions')->with('success','Function deleted successfully.');
    }

    function status($id)
    {
        $functionMaster=FunctionMaster::findOrFail(decrypt($id));
        if ($functionMaster->status == "Active") {
            $functionMaster->status = "Inactive";
        } else {
            $functionMaster->status = "Active";
        }
        $functionMaster->save();

        return redirect()->route('admin.functions')->with('success','Function status updated successfully.');
    }
}
