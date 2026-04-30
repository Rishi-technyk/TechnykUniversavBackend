<?php
// app/Http/Controllers/CaddyController.php
namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;

use App\Models\TeeCaddy;
use App\Models\TeeSessionName;
use App\Models\TeeSessionTime;
use App\Models\TeeSession;

use Illuminate\Http\Request;

class SessionManageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessionNames = TeeSessionName::all();
        $teeSessionTimes = TeeSessionTime::all();
        $sessions = TeeSession::all();
        return view('admin.tee.session_manage.index', compact('sessionNames','teeSessionTimes','sessions'));
       
       
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

        return redirect()->route('caddies.index')->with('success', 'Caddy created successfully!');
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

        return redirect()->route('caddies.index')->with('success', 'TeeCaddy updated successfully!');
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

        return redirect()->route('caddies.index')->with('success', 'TeeCaddy deleted successfully!');
    }
}

?>