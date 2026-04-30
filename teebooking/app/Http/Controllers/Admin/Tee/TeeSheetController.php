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

class TeeSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $teeSheets = TeeSheet::with('teeBooking')->get();
        
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

    // In TeeSheetController.php
    public function show($id)
    {
        // Fetch TeeSheet details for the specified date
        $teeSheetsData = TeeSheet::with('teeBooking')
        ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
        ->where('tee_booking_id', $id)
        ->get();

        // $teeSheets = TeeSheet::with('teeBooking')
        //                         ->where('tee_booking_id', $id)->get();
        $teeSheets = TeeSheet::with('teeBooking')
                                ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
                                ->select('tee_sheet.id as tee_sheet_pk', 'tee_sheet.*', 'tee_booking.*')
                                ->where('tee_sheet.tee_booking_id', $id)
                                ->get();
        $teeHoles = TeeHole::where("is_active", 1)->get();
        $teeSessions = TeeSession::active()->get();
        

        // You can modify this to handle the view display as needed
        return view('admin.tee.tee_sheets.show', compact('id','teeSheetsData','teeSheets','teeHoles','teeSessions'));
    }
    public function show_search(Request $request)
    {
        $id= $request->id;
        $teeHole= $request->teeHole;
        $session_id= $request->session_id;
       
        // Fetch TeeSheet details for the specified date
        $teeSheetsData = TeeSheet::with('teeBooking')
        ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
        ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
        ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
        ->where('tee_booking_id', $id)
        ->get();

        $teeSheets = TeeSheet::with('teeBooking')
            ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
            ->select('tee_sheet.id as tee_sheet_pk','tee_sheet.is_active as active_status', 'tee_sheet.*', 'tee_booking.*', 'tee_holes.*', 'tee_sessions.*')
            ->where('tee_booking_id', $id)
            ->when($teeHole, function ($query) use ($teeHole) {
                return $query->where('tee_holes.id', $teeHole);
            })
            ->when($session_id, function ($query) use ($session_id) {
                return $query->where('tee_sessions.id', $session_id);
            })
            ->get();
            
        // Sorting the collection
        $sortedTeeSheets = $teeSheets->sortBy(function ($teeSheet) {
            return [
                $teeSheet->tee_time,
                $teeSheet->session->session_name
            ];
        });

        // if($teeHole){
        //     $teeSheets->where('tee_holes.id', $teeHole);
        // }
        // if($session_id){
        //     $teeSheets->where('tee_sessions.id', $session_id);
        // }
         //$teeSheets->get();
        // dd($teeSheets);
        $teeHoles = TeeHole::where("is_active", 1)->get();
        $teeSessions = TeeSession::active()->get();
        

        // You can modify this to handle the view display as needed
        return view('admin.tee.tee_sheets.show', compact('id','teeSheetsData','teeSheets','teeHoles','teeSessions','teeHole','session_id'));
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

    public function status_update(Request $request)
    {
        $tableData = TeeSheet::find($request['id']);
        $tableData->is_active = $request['status'];

        if ($tableData->save()) {
            $success = 1;
        } else {
            $success = 0;
        }
        return response()->json([
            'success' => $success,
        ], 200);
    }

    public function is_locked_by_admin_status_update(Request $request)
    {
        $tableData = TeeSheet::find($request['id']);
        $tableData->is_locked_by_admin = $request['status'];

        if ($tableData->save()) {
            $success = 1;
        } else {
            $success = 0;
        }
        return response()->json([
            'success' => $success,
        ], 200);
    }
    public function export_tee_sheet(Request $request)
    {
        $id = $request->id;
        $holeNumber = $request->teeHole;
        $session_id = $request->session_id;
        $tableObj = TeeSheet::select(
            'tee_sheet.tee_time',
            'tee_sheet.is_locked_by_admin',
            'tee_holes.hole_number',


            'mp1.MemberID as player1_member_id',
            'mp1.Email as player1_email',
            'mp2.MemberID as player2_member_id',
            'mp3.MemberID as player3_member_id',
            'mp4.MemberID as player4_member_id',
            'mp1.DisplayName as player1_name',
            'mp2.DisplayName as player2_name',
            'mp3.DisplayName as player3_name',
            'mp4.DisplayName as player4_name',
            'tee_booking.booking_date',
            'tee_sessions.session_name',
            'tbd.created_at',
            'tbd.id'
        )
            ->active()
            ->leftJoin('tee_booking', 'tee_booking.id', '=', 'tee_sheet.tee_booking_id')
            ->leftJoin('tee_holes', 'tee_holes.id', '=', 'tee_sheet.tee_off_hole_id')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_sheet.session_id')
            ->leftJoin('tee_booking_details as tbd', 'tbd.tee_sheet_id', '=', 'tee_sheet.id')
            ->leftJoin('memberprofile as mp1', 'mp1.id', '=', 'tbd.player1_id')
            ->leftJoin('memberprofile as mp2', 'mp2.id', '=', 'tbd.player2_id')
            ->leftJoin('memberprofile as mp3', 'mp3.id', '=', 'tbd.player3_id')
            ->leftJoin('memberprofile as mp4', 'mp4.id', '=', 'tbd.player4_id')
            ->where('tee_sheet.tee_booking_id', $id)
            ->where('tee_holes.id', $holeNumber)
           
            ->where('tbd.is_cancelled', 0)
            ->orderBy('tee_sheet.id');
            if($session_id)
            $tableObj ->where('tee_sessions.id',  $session_id );

        $data = $tableObj->get();
        // dd($teeSheets[0]->booking_date);
        //   die();
        $teeSheets=[]; 
        if(@$data[0]->booking_date){

             // Additional header row
             $teeSheets[] = [
           
            'Date' => $this->date_format($data[0]->booking_date),
            "Tee Off Hole"=>$data[0]->hole_number
        ];

        // Main header row
        $teeSheets[] = [
           'TeeTime', 'BookStatus', 
            'M_ID1', 'Name1', 'M_ID2', 'Name2', 'M_ID3', 'Name3', 'M_ID4', 'Name4',
            'BookedOn', 'Email', 'Booking ID'
        ];

        // Data rows
        foreach ($data as $teeSheet) {
             $status = ($teeSheet->player1_member_id) ? 'Booked' : 'Open';
            if($teeSheet->is_locked_by_admin ==1){
                $status = "Closed";
            }
          
            $teeSheets[] = [
                'TeeTime' => $teeSheet->tee_time,
                'BookStatus' => $status,
                // 'Tee Off Hole' => $teeSheet->hole_number,
                'M_ID1' => $teeSheet->player1_member_id,
                'Name1' => $teeSheet->player1_name,
                'M_ID2' => $teeSheet->player2_member_id,
                'Name2' => $teeSheet->player2_name,
                'M_ID3' => $teeSheet->player3_member_id,
                'Name3' => $teeSheet->player3_name,
                'M_ID4' => $teeSheet->player4_member_id,
                'Name4' => $teeSheet->player4_name,
                'BookedOn' => $this->date_format($teeSheet->created_at),
                'Email' => $teeSheet->player1_email,
                'Booking ID' => $teeSheet->id
            ];
        }

       // dd($teeSheets[2]);

        return (new FastExcel($teeSheets))->download("TeeSheet_".$this->date_format($data[0]['booking_date'])."_hole_".(@$data[0]['hole_number']).".xlsx");
        //$collection = new Collection($data);

        // return (new FastExcel( $collection))->download(, function ($teeSheet) {
        //     return [
        //         'TeeSheetDate' =>
        //          $this->date_format($teeSheet->booking_date),
        //         'TeeTime' => $teeSheet->tee_time,
        //         'BookStatus' => ($teeSheet->player1_member_id)?'Booked':'Open',
        //         'Tee Off Hole' => $teeSheet->hole_number,
        //         'M_ID1' =>$teeSheet->player1_member_id ,
        //         'Name1' => $teeSheet->player1_name,
        //         'M_ID2' =>$teeSheet->player2_member_id ,
        //         'Name2' => $teeSheet->player2_name,
        //         'M_ID3' =>$teeSheet->player3_member_id ,
        //         'Name3' => $teeSheet->player3_name,
        //         'M_ID4' =>$teeSheet->player4_member_id ,
        //         'Name4' => $teeSheet->player4_name,
        //         'BookedOn' => $this->date_format( $teeSheet->created_at),
        //     ];
        // });
    } else{
        return redirect()->back()->with('error', 'Tee Sheet Not found!');
    }
    }

    public function date_format($date)
    {
        $dateStr = "";
        if ($date ) {
          $dateStr = date('d-m-Y',strtotime( $date));
        } 

        return $dateStr;
    }


    public function autocompleteMembers(Request $request)
    {
        $query = $request->query('name');
        $userInput = $request->query('userInput');
        $teeSheetId = $request->query('teeSheetId');
        $selectedSessionId = TeeSheet::select('session_id')->where("id",$teeSheetId)->first()->session_id;

        $categoryCodes = TeeSessionCategory::where("tee_session_id",$selectedSessionId)->pluck('category_type_Code')->toArray();

        // $categoryCodes = TeeSessionCategory::where("tee_session_id",1)->pluck('category_type_Code')->toArray();
        
        // Check if dependent is allowed for the selected session
        $isDependentAllowed = TeeSession::where('id', $selectedSessionId)->value('dependent_allowed');

        // dd($isDependentAllowed);
        
        if ($isDependentAllowed) {
            // Query members where SpouseName is either 'Member' or 'Dependent'
            $membersObj = Member::where(function ($query) use ($userInput) {
                    $query->where('DisplayName', 'like', "%$userInput%")
                        ->orWhere('MemberID', 'like', "$userInput%");
                })
                ->select(
                    'memberprofile.id',
                    'memberprofile.DisplayName as name',
                    'memberprofile.MemberID',
                    'memberprofile.CategoryTypeCode',
                    'memberprofile.Status'
                )
                ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
                ->when($isDependentAllowed, function ($query) {
                    // If dependent is allowed, select members where SpouseName is either 'Member' or 'Dependent'
                    $query->where('SpouseName', 'Member')->orWhere('SpouseName', 'Dependant')->orWhere('SpouseName', '!=', '');
                }, function ($query) {
                    // If dependent is not allowed, select only members where SpouseName is 'Member'
                    $query->where('SpouseName', 'Member')->orWhere('SpouseName', 'Dependant')->orWhere('SpouseName', '!=', '');
                })
                ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryTypeCode')
                ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
                ->distinct()
                ->get();
        } else {
            // Query members where SpouseName is 'Member' only
            $membersObj = Member::where(function ($query) use ($userInput) {
                    $query->where('DisplayName', 'like', "%$userInput%")
                        ->orWhere('MemberID', 'like', "$userInput%");
                })
                ->select(
                    'memberprofile.id',
                    'memberprofile.DisplayName as name',
                    'memberprofile.MemberID',
                    'memberprofile.CategoryTypeCode',
                    'memberprofile.Status'
                )
                ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
                ->where('SpouseName', 'Member')->orWhere('SpouseName', '!=', '')
                ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryTypeCode')
                ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
                ->distinct()
                ->get();
        }
        
        // dd($membersObj);
        
        // $membersObj = Member::where(function ($query) use ($userInput) {
        //     $query->where('DisplayName', 'like', "%$userInput%")
        //         ->orWhere('MemberID', 'like', "%$userInput%");
        // })
        // ->select(
        //     'memberprofile.id',
        //     'memberprofile.DisplayName as name',
        //     'memberprofile.MemberID',
        //     'memberprofile.CategoryTypeCode',
        //     'memberprofile.Status'
        // )
        // ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
        // ->when($isDependentAllowed, function ($query) {
        //     // If dependent is allowed, select members where SpouseName is either 'Member' or 'Dependent'
        //     $query->where('SpouseName', 'Member')->orWhere('SpouseName', 'Dependant');
        // }, function ($query) {
        //     // If dependent is not allowed, select only members where SpouseName is 'Member'
        //     $query->where('SpouseName', 'Member')->orWhere('SpouseName', 'Dependant');
        // })
        // ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryTypeCode')
        // ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
        // ->distinct()
        // ->get();
    
    // Filter members based on category codes
    $filteredMembers = $membersObj->filter(function ($member) use ($categoryCodes) {
        return in_array($member->CategoryTypeCode, $categoryCodes);
    });
       
      
        // $members = Member::where(function($q) use ($query, $userInput) {
        //             $q->where('DisplayName', 'like', "%$query%")
        //               ->orWhere('MemberID', '=', $userInput);
        //         })
        //         ->select('id', 'DisplayName as name')
        //         ->get();
        // dd($query, $userInput);
        // if ($query != null) {
        //     $members = Member::where('DisplayName', 'like', "%$query%")
        //     ->select('id', 'DisplayName as name')
        //     ->get();
        // } else {
        //     $members = Member::where('MemberID', '=', $userInput)
        //     ->select('id', 'DisplayName as name')
        //     ->get();
        // }

      /*  $members = Member::where('DisplayName', 'like', "%$userInput%")
            ->orWhere('MemberID', '=', $userInput)
            ->select('id', 'DisplayName as name', 'MemberID')
            ->leftJoin('tee_sheet', 'tee_sheet.id', '=', 'tee_sheet.tee_booking_id')

            ->get();*/

            /*$membersObj = Member::where('DisplayName', 'like', "%$userInput%")
            ->orWhere('MemberID', '=', $userInput)
            ->select('memberprofile.id', 'memberprofile.DisplayName as name', 'memberprofile.MemberID')
            ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')
            ->leftJoin('tee_session_categories', 'tee_session_categories.category_type_Code', '=', 'memberprofile.CategoryTypeCode')
            ->leftJoin('tee_sessions', 'tee_sessions.id', '=', 'tee_session_categories.tee_session_id')
            ->whereIn()
            ->get();*/
        // dd($members, $request->query('userInput'));
        $StatusArray = ["Direct Out Station","In Station","Out Station"];
        // dd($filteredMembers);
      
        $response = [];
        foreach ($filteredMembers as $member) {
            if(in_array($member->Status,$StatusArray)){
            $newAre = array();
            $newAre['value'] = $member->id;
            $newAre['label'] = trim($member->name) . "/" . $member->MemberID;
            $response[] = $newAre;
            }
        }
        
        // dd($response);

        return response()->json($response);

        // return response()->json($members);
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