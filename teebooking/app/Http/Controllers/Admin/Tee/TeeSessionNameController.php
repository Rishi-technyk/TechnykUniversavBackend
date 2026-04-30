<?php
namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSessionName;

class TeeSessionNameController extends Controller
{
    public function index()
    {
        $sessionNames = TeeSessionName::all();
        return view('admin.tee.session_manage', compact('sessionNames'));
    }

    public function create()
    {
        return view('admin.tee.session_names.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            // 'is_active' => 'boolean',
            // 'created_by' => 'nullable|numeric',
            // 'updated_by' => 'nullable|numeric',
        ]);

        TeeSessionName::create($request->all());

        return redirect()->route('session_manage')->with('success', 'Session name created successfully!');
    }

    public function edit(TeeSessionName $sessionName)
    {
        return view('admin.tee.session_names.edit', compact('sessionName'));
    }

    public function update(Request $request, TeeSessionName $sessionName)
    {
        $request->validate([
            'name' => 'required|max:255',
            // 'is_active' => 'boolean',
            // 'created_by' => 'nullable|numeric',
            // 'updated_by' => 'nullable|numeric',
        ]);

        $sessionName->update($request->all());

        return redirect()->route('session_manage')->with('success', 'Session name updated successfully!');
    }

    public function destroy(TeeSessionName $sessionName)
    {
        $sessionName->delete();

        return redirect()->route('session_manage')->with('success', 'Session name deleted successfully!');
    }
}


?>