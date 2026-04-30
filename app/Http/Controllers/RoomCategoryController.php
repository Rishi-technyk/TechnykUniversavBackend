<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RoomCategoryMaster;

class RoomCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datas = RoomCategoryMaster::orderBy('id', 'DESC')->get();
        return view('backend.room_category.index', compact('datas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.room_category.create');
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
            'name' => 'required|string|max:255',
            'gst' => 'required|numeric',
            'no_of_rooms' => 'required|integer',
            'room_image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('room_image')) {
            $image = $request->file('room_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/room_category'), $name);
            $imagePath = 'uploads/room_category/'.$name;
        }
       
        $roomCategory = new RoomCategoryMaster();
        $roomCategory->name = $request->name;
        $roomCategory->GST = $request->gst;
        $roomCategory->no_of_rooms = $request->no_of_rooms;
        $roomCategory->size = $request->size;
        $roomCategory->capacity = $request->capacity;
        $roomCategory->bed_type = $request->bed_type;
        $roomCategory->services = $request->services;
        $roomCategory->description = $request->description;
        $roomCategory->status = 'Active';
        $roomCategory->room_image = $imagePath;
        $roomCategory->save();

        return redirect()->route('admin.room_categories')->with('success', 'Room Category created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = RoomCategoryMaster::findOrFail(decrypt($id));
        return view('backend.room_category.edit', compact('data'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'gst' => 'required|numeric',
            'no_of_rooms' => 'required|integer',
            // 'room_image' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $roomCategory = RoomCategoryMaster::findOrFail(decrypt($id));

        if ($request->hasFile('room_image')) {

            // Image Deletion From Folder Code 
            $imagePath = $roomCategory->room_image;
            if ($imagePath && file_exists(public_path($imagePath))) {
                unlink(public_path($imagePath));
            }

            $image = $request->file('room_image');
            $name = time().'.'.$image->getClientOriginalExtension();
            $image->move(public_path('uploads/room_category'), $name);
            $roomCategory->room_image = 'uploads/room_category/'.$name;
            
        }
       
        $roomCategory->name = $request->name;
        $roomCategory->GST = $request->gst;
        $roomCategory->no_of_rooms = $request->no_of_rooms;
        $roomCategory->size = $request->size;
        $roomCategory->capacity = $request->capacity;
        $roomCategory->bed_type = $request->bed_type;
        $roomCategory->services = $request->services;
        $roomCategory->description = $request->description;
        $roomCategory->save();

        return redirect()->route('admin.room_categories')->with('success', 'Room Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $data = RoomCategoryMaster::findOrFail(decrypt($id));
        // Image Deletion From Folder Code 
        $imagePath = $data->room_image;
        if ($imagePath && file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
        $data->delete();
        return redirect()->route('admin.room_categories')->with('success', 'Room Category deleted successfully.');
    }

    function status($id)
    {
        $data = RoomCategoryMaster::findOrFail(decrypt($id));
        if ($data->status == 'Active') {
            $data->status = 'Inactive';
        } else {
            $data->status = 'Active';
        }
        $data->save();
        return redirect()->route('admin.room_categories')->with('success', 'Room Category status updated successfully.');   
    }
}
