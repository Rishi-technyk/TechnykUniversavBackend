<?php 
namespace App\Http\Controllers\Admin\Tee;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TeeSheet;

class TeeSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teeSheets = TeeSheet::all();
        return view('admin.tee.tee_sheets.index', compact('teeSheets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.tee.tee_sheets.create');
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
            'tee_booking_id' => 'required|numeric',
            'tee_time' => 'required|string',
            'is_locked_by_admin' => 'boolean',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);
        // print_r($request->all());
        // die();
        $request = Helpers::set_common_request($request);

        TeeSheet::create($request->all());

        return redirect()->route('tee_sheets.index')->with('success', 'Tee Sheet created successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\TeeSheet  $teeSheet
     * @return \Illuminate\View\View
     */
    public function edit(TeeSheet $teeSheet)
    {
        return view('admin.tee.tee_sheets.edit', compact('teeSheet'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\TeeSheet  $teeSheet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, TeeSheet $teeSheet)
    {
        $request->validate([
            'tee_booking_id' => 'required|numeric',
            'tee_time' => 'required|string',
            'is_locked_by_admin' => 'boolean',
            'is_active' => 'boolean',
            'created_by' => 'nullable|numeric',
            'updated_by' => 'nullable|numeric',
        ]);

        $teeSheet->update($request->all());

        return redirect()->route('tee_sheets.index')->with('success', 'Tee Sheet updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\TeeSheet  $teeSheet
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(TeeSheet $teeSheet)
    {
        $teeSheet->delete();

        return redirect()->route('tee_sheets.index')->with('success', 'Tee Sheet deleted successfully!');
    }
}

?>