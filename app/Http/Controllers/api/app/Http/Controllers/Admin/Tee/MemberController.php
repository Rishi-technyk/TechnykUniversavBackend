<?php
namespace App\Http\Controllers\Admin\Tee;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Models\TeeSessionCategory;
use Illuminate\Http\Request;
use App\Models\TeeSheet;
use App\Models\Member;
use App\Models\TeeHole;
use App\Models\TeeSession;
use Rap2hpoutre\FastExcel\FastExcel;
use DB;

class MemberController extends Controller
{
   
 
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

    
    public function destroy(TeeSheet $teeSheet)
    {
        $teeSheet->delete();

        return redirect()->route('tee_sheets.index')->with('success', 'Tee Sheet deleted successfully!');
    }
 

    public function autocompleteBuddy(Request $request)
    {
        //$query = $request->query('name');
        $userInput = $request->query('userInput');
        //$teeSheetId = $request->query('teeSheetId');
        //$selectedSessionId = TeeSheet::select('session_id')->where("id",$teeSheetId)->first()->session_id;

        //$categoryCodes = TeeSessionCategory::where("tee_session_id",$selectedSessionId)->pluck('category_type_Code')->toArray();

        //$categoryCodes = TeeSessionCategory::where("tee_session_id",1)->pluck('category_type_Code')->toArray();
        $membersObj = Member::where(function ($query) use ($userInput) {
            $query->where('DisplayName', 'like', "%$userInput%")
                ->orWhere('MemberID', '=', $userInput);
        })
        ->select(
            'memberprofile.id',
            'memberprofile.DisplayName as name',
            'memberprofile.MemberID',
            'memberprofile.CategoryCode',
            'memberprofile.Status',

        )
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryCode')
        ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryCode')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
        ->distinct()
        ->get();
    
   
        $StatusArray = ["Direct Out Station","In Station","Out Station"];
      
        $response = [];
        foreach ($membersObj as $member) {
            if(in_array($member->Status,$StatusArray)){
            $newAre = array();
            $newAre['value'] = $member->id;
            $newAre['label'] = trim($member->name) . "/" . $member->MemberID;
            $response[] = $newAre;
            }
        }

        return response()->json($response);

        // return response()->json($members);
    }
}

?>