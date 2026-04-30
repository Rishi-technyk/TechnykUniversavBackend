<?php

namespace App\Http\Controllers\Activity;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FacilityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = Facility::orderBy('id', 'DESC')->get();
        
        return view('backend.activity.facility.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.activity.facility.create');
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
            'inventory' => 'required',
            'charge' => 'required',
            'gst' => 'required',
        ]);

        $facility = new Facility();
        $facility->slug = Str::slug($request->name);
        $facility->name = $request->name;
        $facility->inventory = $request->inventory;
        $facility->charge = $request->charge;
        $facility->GSTper = $request->gst;
        $facility->description = $request->description;

        $imagePath = null;
        if ($request->hasFile('image_1')) {
            $image = $request->file('image_1');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/facility/slider'), $name);
            $facility->first_image = 'uploads/facility/slider/'.$name;
        }
   
        $facility->save();

        return redirect()->route('admin.facilities')->with('success', 'Facility created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function status($id)
    {
        $facility = Facility::findOrFail(decrypt($id));
        $facility->status = $facility->status == 'Active' ? 'Inactive' : 'Active';
        $facility->save();

        return redirect()->route('admin.facilities')->with('success', 'Facility status updated successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = Facility::findOrFail(decrypt($id));

        return view('backend.activity.facility.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'inventory' => 'required',
            'charge' => 'required',
            'gst' => 'required',
        ]);

        $facility = Facility::findOrFail(decrypt($id));
        $facility->slug = Str::slug($request->name);
        $facility->name = $request->name;
        $facility->inventory = $request->inventory;
        $facility->charge = $request->charge;
        $facility->GSTper = $request->gst;
        $facility->description = $request->description;

        if ($request->hasFile('image_1')) {
            // Delete old image if exists
            if ($facility->first_image && file_exists(public_path($facility->first_image))) {
                unlink(public_path($facility->first_image));
            }

            // Upload new image
            $image = $request->file('image_1');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/facility/slider'), $name);
            $facility->first_image = 'uploads/facility/slider/'.$name;
        }
   
        $facility->save();

        return redirect()->route('admin.facilities')->with('success', 'Facility updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Facility  $facility
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $facility = Facility::findOrFail(decrypt($id));

        // Delete image if exists
        if ($facility->first_image && file_exists(public_path($facility->first_image))) {
            unlink(public_path($facility->first_image));
        }

        $facility->delete();

        return redirect()->route('admin.facilities')->with('success', 'Facility deleted successfully.');
    }
}
