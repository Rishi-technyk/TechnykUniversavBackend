<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data['datas'] = Document::orderBy('id', 'DESC')->get();

        return view('backend.documents.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.documents.create');
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
            'label' => 'required',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        $document = new Document();
        $document->label = $request->label;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = public_path('uploads/documents/');
            $file->move($filePath, $filename);
            $document->file_path = 'uploads/documents/' . $filename;
        }

        $document->save();

        return redirect()->route('admin.documents')->with('success', 'Document created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function show(Document $document)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data['data'] = Document::findOrFail(decrypt($id));

        return view('backend.documents.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail(decrypt($id));
        $document->label = $request->label;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = public_path('uploads/documents/');
            $file->move($filePath, $filename);
            $document->file_path = 'uploads/documents/' . $filename;
        }
        $document->save();

        return redirect()->route('admin.documents')->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Document  $document
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $document = Document::findOrFail(decrypt($id));
        if ($document->file_path && File::exists(public_path($document->file_path))) {
            File::delete(public_path($document->file_path));
        }
        $document->delete();

        return redirect()->route('admin.documents')->with('success', 'Document deleted successfully.');
    }

    function status($id)
    {
        $document = Document::findOrFail(decrypt($id));
        $document->status = $document->status == "Active" ? "Inactive" : "Active";
        $document->save();

        return redirect()->route('admin.documents')->with('success', 'Document status updated successfully.');
        
    }


    // Menu Functions
    public function menu_index()
    {
        $data['datas'] = Menu::orderBy('id', 'DESC')->get();

        return view('backend.menu.index', $data);
    }

    public function menu_create()
    {
        return view('backend.menu.create');
    }

    public function menu_store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'file' => 'required|mimes:pdf|max:2048',
        ]);

        $menu = new Menu();
        $menu->name = $request->name;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = public_path('uploads/menus/');
            $file->move($filePath, $filename);
            $menu->data = 'uploads/menus/' . $filename;
        }
       
        $menu->save();

        return redirect()->route('admin.menus')->with('success', 'Party Menu created successfully.');
    }

    public function menu_edit($id)
    {
        $data['data'] = Menu::findOrFail(decrypt($id));

        return view('backend.menu.edit', $data);
    }

    public function menu_update(Request $request, $id)
    {
        $menu = Menu::findOrFail(decrypt($id));
        $menu->name = $request->name;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $filePath = public_path('uploads/menus/');
            $file->move($filePath, $filename);
            $menu->data = 'uploads/menus/' . $filename;
        }
        $menu->save();

        return redirect()->route('admin.menus')->with('success', 'Party Menu updated successfully.');
    }

    public function menu_destroy($id)
    {
        $document = Menu::findOrFail(decrypt($id));
        if ($document->data && File::exists(public_path($document->data))) {
            File::delete(public_path($document->data));
        }
        $document->delete();

        return redirect()->route('admin.menus')->with('success', 'Party Menu deleted successfully.');
    }

    function menu_status($id)
    {
        $document = Menu::findOrFail(decrypt($id));
        $document->status = $document->status == "Active" ? "Inactive" : "Active";
        $document->save();

        return redirect()->route('admin.menus')->with('success', 'Party Menu status updated successfully.');
        
    }
}
