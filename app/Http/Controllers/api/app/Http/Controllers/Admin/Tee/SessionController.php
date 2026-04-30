<?php
namespace App\Http\Controllers\Admin\Tee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSession;
use App\Models\SessionCategory;
use App\Models\CategoryMaster;
use App\Models\CategoryType;
use App\Models\OccupantType;
use Illuminate\Support\Facades\Auth;

class SessionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $sessions = TeeSession::all();
        return view('admin.tee.sessions.index', compact('sessions'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $categories = CategoryType::all();

        // Pass the categories to the view
        return view('admin.tee.sessions.create', compact('categories'));
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
            'session_name' => 'required|string',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'categories' => 'required|array|min:1'
        ]);

        // Create the TeeSession
        $teeSession = TeeSession::create($request->only(['session_name', 'start_time', 'end_time', 'dependent_allowed']));
    
        foreach ($request->input('categories') as $categoryId) {
            $teeSession->sessionCategories()->create([
                'category_type_Code' => $categoryId,
            ]);
        }

        return redirect()->route('sessions.index')->with('success', 'TeeSession created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeeSession  $session
     * @return \Illuminate\View\View
     */
    public function edit(TeeSession $session)
    {
        $categories = CategoryType::all(); // Assuming you have a CategoryType model

        // Eager load the 'categories' relationship
        $session = TeeSession::with('sessionCategories')->find($session->id);
    
        // Get the selected categories for the session
        $selectedCategories = $session->sessionCategories->pluck('category_type_Code')->toArray();
        return view('admin.tee.sessions.edit', compact('session', 'categories', 'selectedCategories'));
        // return view('admin.tee.sessions.edit', compact('session'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeeSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeeSession $session)
    {
        $request->validate([
            'session_name' => 'required|string',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'categories' => 'required|array|min:1',
        ]);
        // dd([
        //     'session_name' => $request->input('session_name'),
        //     'start_time' => $request->input('start_time'),
        //     'end_time' => $request->input('end_time'),
        //     'dependent_allowed' => $request->input('dependent_allowed') == "on" ? 1 : 0,
        // ]);
        $dependentAllowed = $request->has('dependent_allowed') ? 1 : 0;
        $session->update([
            'session_name' => $request->input('session_name'),
            'start_time' => $request->input('start_time'),
            'end_time' => $request->input('end_time'),
            // 'dependent_allowed' => $dependentAllowed
        ]);
        
        $session->dependent_allowed = $dependentAllowed;

        $session->save();

        // Sync the selected categories for the session
        $session->categories()->sync($request->input('categories'));


        return redirect()->route('sessions.index')->with('success', 'TeeSession updated successfully!');
    }

    public function status_update(Request $request)
    {
        $tableData= TeeSession::find($request['id']);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeeSession  $session
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeeSession $session)
    {
        $session->delete();

        return redirect()->route('session_manage')->with('success', 'TeeSession deleted successfully!');
    }
}
