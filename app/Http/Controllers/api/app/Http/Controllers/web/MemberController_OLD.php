<?php
namespace App\Http\Controllers\web;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Models\TeeSessionCategory;
use Illuminate\Http\Request;
use App\Models\TeeSheet;
use App\Models\Member;
use App\Models\TeeMyBuddies;
use App\Models\TeeBookingDetails;
use App\Models\TeeHole;
use App\Models\TeeSession;
use App\Models\TeeGroup;
use Rap2hpoutre\FastExcel\FastExcel;
use DB;

class MemberController extends Controller
{
   
 
    public function store_buddy(Request $request)
    {
        $data = [
            "member_id"=>$request['buddy_member_id'],
            'created_by' => auth()->user()->id, 
        ];
        
       $buddyExist =  TeeMyBuddies::where($data)->first();
        if ($buddyExist) {
            return response()->json(['error' => "This Member already added in your buddy list."], 400);
        } else{
            TeeMyBuddies::create($data);
        }

        return response()->json(['message' => 'Buddy added successfully']);   
    }

    public function store_group(Request $request)
    {
        $validator = $request->validate([
            'player6_id' => 'required|different:player7_id,player8_id,player9_id',
            'player7_id' => 'required|different:player6_id,player8_id,player9_id',
            'player8_id' => 'required|different:player6_id,player7_id,player9_id',
            'player9_id' => 'nullable|different:player6_id,player7_id,player8_id',
        ], [

            'player6_id.required' => 'Player 1 required for Tee Booking.',
            'player7_id.required' => 'Player 2 required for Tee Booking.',
            'player8_id.required' => 'Player 3 required for Tee Booking.',
            'player9_id.required' => 'Player 4 required for Tee Booking.',
            'player6_id.different' => 'Player 1 must be different from other players.',
            'player7_id.different' => 'Player 2 must be different from other players.',
            'player8_id.different' => 'Player 3 must be different from other players.',
            'player9_id.different' => 'Player 4 must be different from other players.',

        ]);

        $playerId1 = $request->input("player6_id");
        $playerId2 = $request->input("player7_id");

        if ($playerId1 == $playerId2) {
            return response()->json(['Player 3 already selected as Player1'], 400);
        }




        $playerIds = array_filter($request->only('player6_id', 'player7_id', 'player8_id', 'player9_id'));

        if (count($playerIds) < 3) {
            return response()->json(['error' => 'At least 3 players are required.'], 400);
        }
       

        	
        $teeGroup = new TeeGroup();
        $teeGroup->group_name="title";
        $teeGroup->player1_id= $request->player6_id;
        $teeGroup->player2_id= $request->player7_id;
        $teeGroup->player3_id= $request->player8_id;
        $teeGroup->player4_id= $request->player9_id;
        $teeGroup->created_by= auth()->user()->id;
        $teeGroup->save();

        /*$teeBookingDetailId = $request->tee_booking_detail_id;
        foreach ($playerIds as $index => $playerIdField) {
            if ($request->has($playerIdField)) {
                $playerId = $request->input($playerIdField);
                if ($playerId) {
                    $bookingExists = TeeBookingDetails::whereDate('created_at', now()->toDateString())
                        ->whereNot("id", $teeBookingDetailId)
                        ->where("is_cancelled", 0)
                        ->where(function ($query) use ($playerId) {
                            $query->where('player1_id', $playerId)
                                ->orWhere('player2_id', $playerId)
                                ->orWhere('player3_id', $playerId)
                                ->orWhere('player4_id', $playerId);
                        })
                        ->exists();

                    if ($bookingExists) {
                        $playerNumber = $index + 1; // Get the player number (1-indexed)
                        return response()->json(['error' => "Player $playerNumber has already booked a slot on the same day."], 400);
                    }
                }
            }
        }*/
       



        return response()->json(['message' => 'TeeBookingDetails created successfully']);
    }

    
    public function delete($id)
    {
        TeeMyBuddies::where("id",$id)->delete();
        return redirect()->back()->with('success', 'Buddy deleted successfully!');
    }
    public function delete_group($id)
    {
        TeeGroup::where("id",$id)->delete();
        return redirect()->back()->with('success', 'Group deleted successfully!');
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

    public function member_profile(){
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        return view('website.pages.member_profile',compact('member'));
    }
    public function member_edit(){
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        return view('website.pages.member_edit',compact('member'));
    }
    public function update(Request $request)
{
    // $request->validate([
    //     'DOB' => 'required|date',
    //     'SpouseName' => 'required|string',
    //     'SpouseDOB' => 'required|date',
    //     'AnniversaryDate' => 'required|date',
    //     'Email' => 'required|email',
    //     'Mobile' => 'required|numeric',
    //     'state' => 'required|string',
    //     'city' => 'required|string',
    //     'pin' => 'required|numeric',
    //     'Address' => 'required|string',
    // ]);

    $member = Member::findOrFail(auth()->user()->id);

    // Update member details individually
    $member->DOB = $request->input('DOB');
    $member->SpouseName = $request->input('SpouseName');
    $member->SpouseDOB = $request->input('SpouseDOB');
    $member->AnniversaryDate = $request->input('AnniversaryDate');
    $member->Email = $request->input('Email');
    $member->Mobile = $request->input('Mobile');
    //$member->state = $request->input('state');
    //$member->city = $request->input('city');
    $member->pin = $request->input('pin');
    $member->Address = $request->input('Address');

    // Save the changes
    $member->save();

    return redirect()->back()->with('success', 'Member details updated successfully!');
}
    public function member_transactions(){
        return view('website.pages.transactions');
    }
    public function member_subscription(){
        return view('website.pages.subscription');
    }
    public function member_otp(){
        $sc_id = auth()->user()->id;
        $otp = rand(100000,999999);

        DB::delete("DELETE FROM OTP WHERE MemberId = ? AND Verified = 0", [$sc_id]);

    // Insert new OTP record
     DB::insert("INSERT INTO OTP (MemberId, OTP) VALUES (?, ?)", [$sc_id, $otp]);

    
        return view('website.pages.otp',compact('otp'));
    }
    public function member_card_recharge(){
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        
       // print_r($member);
        return view('website.pages.card_recharge',compact('member'));
    }
    public function member_change_password(){
        return view('website.pages.change_password');
    }

    public function get_otp(){
        $sc_id = auth()->user()->id;
        $otp = rand(100000,999999);

        DB::delete("DELETE FROM OTP WHERE MemberId = ? AND Verified = 0", [$sc_id]);

    // Insert new OTP record
     DB::insert("INSERT INTO OTP (MemberId, OTP) VALUES (?, ?)", [$sc_id, $otp]);

    echo $otp;
    }

    
   
    
}

?>