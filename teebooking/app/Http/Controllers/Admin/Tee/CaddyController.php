<?php
// app/Http/Controllers/CaddyController.php
namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;

use App\Models\TeeCaddy;

use Illuminate\Http\Request;

class CaddyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $caddies = TeeCaddy::all();
        return view('admin.tee.caddies.index', compact('caddies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.tee.caddies.create');
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

        TeeCaddy::create($request->all());

        return redirect()->route('service_manage')->with('success', 'Caddy created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeeCaddy  $caddy
     * @return \Illuminate\View\View
     */
    public function edit(TeeCaddy $caddy)
    {
        return view('admin.tee.caddies.edit', compact('caddy'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeeCaddy  $caddy
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeeCaddy $caddy)
    {
        $request->validate([
            'name' => 'required|string',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $caddy->update($request->all());

        return redirect()->route('service_manage')->with('success', 'TeeCaddy updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeeCaddy  $caddy
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeeCaddy $caddy)
    {
        $caddy->delete();

        return redirect()->route('service_manage')->with('success', 'TeeCaddy deleted successfully!');
    }

    public function status_update(Request $request)
    {
        $tableData= TeeCaddy::find($request['id']);
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