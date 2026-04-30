<?php
namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Models\ACHomeMenu;
use App\Models\IM_LocationMaster;
use App\Models\AC_UserMaster;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\AC_FinancialYear;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\Member;
use App\Models\IM_ItemMaster;
use App\Models\FB_LocationGroupLink;
use App\Models\CMCardIssueMember;
use Illuminate\Support\Facades\DB;
use App\Models\TableLocation;
use App\Models\FB_KOTModifier;
use App\Models\FB_BillHead;
use App\Models\FB_BillBody;

class StaffController extends Controller
{
    public function getHomeMenu($type,Request $request)

{
    
     $locationCode = $request->query('locationCode');
    $menues= ACHomeMenu::where('status', 1)
                     ->where('type', $type)
                     ->get(['name', 'icon', 'navigate','gradient_colors']);
                      $userData = $request->user(); // âœ… works because of setUserResolver
Log::info( $userData);
    if($locationCode){
        $user = AC_UserMaster::where('UserCode', $userData->UserCode)->first();
        $user->LocationCode=$locationCode;
        $user->save();
        
    }
    $locations = IM_LocationMaster::select('code', 'LocationName')->where('status',1)->get();
    
    return response()->json([
        'status' => true,
        'message' => 'Fetched successfully',
        'data' => $menues,
        'locations'=>$locations
      
    ], 200);
}

public function getHomeDetails($type, Request $request)
{
    $user = $request->user();
    $today = now()->toDateString();
Log::info( $user);
    // ✅ Fetch Orders with LEFT JOIN to include tables even if not found
    $Orders = FB_BillHead::where('FB_BillHead.UserCode', $user->UserCode)
        ->whereDate('FB_BillHead.CreationDate', $today)
        ->where('FB_BillHead.LocationCode', $user->LocationCode)
        ->leftJoin('FB_TableMaster', 'FB_TableMaster.code', '=', 'FB_BillHead.TableCode')
        ->select(
            'FB_BillHead.id',
            'FB_BillHead.BillNo',
            'FB_BillHead.TableCode',
            DB::raw('COALESCE(FB_TableMaster.TableNo, "Unknown") as TableNo'), // fallback if table missing
             'FB_BillHead.Amount',
            'FB_BillHead.BillStatus',
            'FB_BillHead.CreationDate',
            'FB_BillHead.MemberID',
            'FB_BillHead.MemberName',
            'FB_BillHead.YearCode',
            'FB_BillHead.LocationCode'
        )
        ->orderBy('FB_BillHead.BillStatus', 'asc') // Pending first
        ->orderBy('FB_BillHead.CreationDate', 'desc') // Latest first
        ->get();

    // ✅ Summary Section
    $summary = [
        'TotalOrders'     => $Orders->count(),
        'CompletedOrders' => $Orders->where('BillStatus', 1)->count(),
        'PendingOrders'   => $Orders->where('BillStatus', 0)->count(),
        'TotalSales'      => round($Orders->sum('Amount'), 2),
        'Date'            => $today,
        'User'            => [
            'UserCode' => $user->UserCode,
            'UserName' => $user->UserName,
        ],
    ];

    // ✅ Return Response
    return response()->json([
        'status'  => true,
        'message' => 'Dashboard data fetched successfully.',
        'data'    => [
            'Summary' => $summary,
            'Orders'  => $Orders,
        ],
    ]);
}




public function getGroupItems(Request $request){
      $validator = Validator::make($request->all(), [
        'Code' => 'required|int',
        'GroupCode' => 'required|int',
    ]);
     if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422); // 422 = Unprocessable Entity
    }
//   $items= IM_ItemMaster::where('ItemGroup',$request->GroupCode)->where('ItemSubGroup',$request->Code)->where('Status','Active')->get();
   $items = DB::table('IM_ItemMaster as i')
    ->join('AC_TaxMaster as t', 'i.Saletaxcode', '=', 't.Code')
    ->where('i.ItemGroup', $request->GroupCode)
    ->where('i.ItemSubGroup', $request->Code)
    ->where('i.Status', 'Active')
    ->select(
                'i.ItemCode',
                'i.ItemName',
                'i.SP',
                'i.Displayas',
                'i.OpenItem',
                't.ValuePercentage',
                't.TaxType',
                'i.ServiceCharge',
                'i.SaleUnit',
                'i.ModifierAllow',
                't.ValuePercentage', // tax percentage
                't.TaxType'
        
    )
    ->get();
  
    return response()->json([
        'success' => true,
        'message' => 'Group items fetched Successfully.',
        'data'=>$items
       
    ]);
   
    
}
public function verifyMember(Request $request){
    
    $validator = Validator::make($request->all(), [
        'member_id' => 'required|string',
        'otp' => 'required|int',
        'LocationCode'=>'required',
        'TableCode'=>'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422); // 422 = Unprocessable Entity
    }
     $member = Member::where('MemberId', $request->member_id)
                    ->select('MemberId', 'DisplayName','SC_ID')
                    ->first();
                    Log::info($member);
                    $cardBalance = DB::table('CardClosingBalance')->where('MemberID', $member->SC_ID)->first();
                    
                 

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found',
        ], 404);
    }
    $currentDate = Carbon::now()->toDateString();

$currentFY = AC_FinancialYear::whereDate('DateFrom', '<=', $currentDate)
    ->whereDate('DateTo', '>=', $currentDate)
    ->first();

if ($currentFY) {
    $yearCode = $currentFY->YearCode;
} else {
    $yearCode = null; // or handle the case where no record is found
}
  $otpRecord = DB::table('OTP')  // your table name
        ->where('MemberId', $request->member_id)
        ->select('MemberId', 'otp')
        ->first();

    if (!$otpRecord) {
        return response()->json([
            'success' => false,
            'message' => 'OTP not found for this member',
        ], 404);
    }
     if ($otpRecord->otp !== $request->otp) {
        return response()->json([
            'success' => false,
            'message' =>"Invalid OTP $otpRecord->otp",
        ], 401);
    }



$groups = FB_LocationGroupLink::getGroupsByLocation($request->LocationCode);
 $today = now()->toDateString();
$total = FB_BillHead::where('MemberID', $member->SC_ID)
    ->whereDate('CreationDate', $today)
    ->where('BookingNo', 0)
    ->where('BillType', 10)
    ->sum(DB::raw('Amount + RoundOff'));
    
    return response()->json([
        'success' => true,
        'message' => 'Member Verified Successfully',
        'data'=>['member'=>$member,
         'group'=>$groups,
         'card_balance'=>$cardBalance->CardBalance-$total,
         'yearCode'=>$yearCode
        ],
       
    ]);
}

public function verifyNFCCard(Request $request){
    
    $validator = Validator::make($request->all(), [
    
        'nfc_serial' => 'required|string',
         'LocationCode'=>'required',
        'TableCode'=>'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

  
 

   \Log::info($request->nfc_serial);

    // 1. Verify NFC Card serial number
    $serial = strtoupper($request->nfc_serial);
    
    \Log::info($serial);

    $cardRecord = CMCardIssueMember::where('Card_SerialNo', $serial)->first();
    
    
    if (!$cardRecord) {
        return response()->json([
            'success' => false,
            'message' => "Card is not registered",
        ], 404);
    }
      // 2. Verify Member
      $member = Member::where('SC_ID', $cardRecord->Cardid)
                    ->select('MemberId', 'DisplayName','SC_ID')
                    ->first();
                    Log::info($member);
                    $cardBalance = DB::table('CardClosingBalance')->where('MemberID', $cardRecord->Cardid)->first();
                    
                 

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found',
        ], 404);
    }
    $currentDate = Carbon::now()->toDateString();

$currentFY = AC_FinancialYear::whereDate('DateFrom', '<=', $currentDate)
    ->whereDate('DateTo', '>=', $currentDate)
    ->first();

if ($currentFY) {
    $yearCode = $currentFY->YearCode;
} else {
    $yearCode = null; // or handle the case where no record is found
}



    // 4. Get Groups
    $groups = FB_LocationGroupLink::getGroupsByLocation($request->LocationCode);
     $today = now()->toDateString();
    $total = FB_BillHead::where('MemberID', $cardRecord->Cardid)
    ->whereDate('CreationDate', $today)
    ->where('BookingNo', 0)
    ->sum(DB::raw('Amount + RoundOff'));

    // 5. Final Response
    return response()->json([
        'success' => true,
        'message' => 'Member Verified Successfully',
        'data'=>['member'=>$member,
         'group'=>$groups,
         'card_balance'=>$cardBalance->CardBalance-$total,
         'yearCode'=>$yearCode
        ],
    ]);
}

public function verifyRunningMember(Request $request){
    
    $validator = Validator::make($request->all(), [
        'member_id' => 'required|string',
        'LocationCode'=>'required',
        'TableCode'=>'required'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422); // 422 = Unprocessable Entity
    }
     $member = Member::where('MemberId', $request->member_id)
                    ->select('MemberId', 'DisplayName','SC_ID')
                    ->first();
                    Log::info($member);
                    $cardBalance = DB::table('CardClosingBalance')->where('MemberID', $member->SC_ID)->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found',
        ], 404);
    }
    $currentDate = Carbon::now()->toDateString();

$currentFY = AC_FinancialYear::whereDate('DateFrom', '<=', $currentDate)
    ->whereDate('DateTo', '>=', $currentDate)
    ->first();

if ($currentFY) {
    $yearCode = $currentFY->YearCode;
} else {
    $yearCode = null; // or handle the case where no record is found
}
 


$groups = FB_LocationGroupLink::getGroupsByLocation($request->LocationCode);

    return response()->json([
        'success' => true,
        'message' => 'Member Verified Successfully',
        'data'=>['member'=>$member,
         'group'=>$groups,
         'card_balance'=>$cardBalance->CardBalance,
         'yearCode'=>$yearCode
        ],
       
    ]);
}
public function searchItems(Request $request)
{
    try {
        $search = $request->query('search'); // e.g. ?search=AlooPuff
        $perPage = 10; // limit 10 results per page

        $items = DB::table('IM_ItemMaster as i')
            ->leftJoin('IM_GroupMaster as g', 'i.ItemGroup', '=', 'g.Code')
            ->leftJoin('IM_SubGroupMaster as sg', 'i.ItemSubGroup', '=', 'sg.Code')
            ->join('AC_TaxMaster as t', 'i.Saletaxcode', '=', 't.Code')
            ->select(
                'i.ItemCode',
                'i.ItemName',
                'i.SP',
                'g.GroupName',
                'sg.SubgroupDisplyas',
                'i.Displayas',
                'i.OpenItem',
                't.ValuePercentage',
                't.TaxType',
                'i.ServiceCharge',
                'i.SaleUnit',
                'i.ModifierAllow'
            )
            ->when($search, function ($query, $search) {
                return $query->where('i.ItemName', 'LIKE', '%' . $search . '%');
            })
            ->orderBy('i.ItemName', 'asc')
            ->paginate($perPage); // Laravel pagination

        return response()->json([
            'status' => true,
            'message' => 'Items fetched successfully',
            'total' => $items->total(),
            'current_page' => $items->currentPage(),
            'last_page' => $items->lastPage(),
            'per_page' => $items->perPage(),
            'data' => $items->items()
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Error fetching items',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function getTables(Request $request){
     $locationCode = $request->query('locationCode');

    

        if (!$locationCode) {
            return response()->json(['status' => false, 'message' => 'Location code is required'], 400);
        }

     
$tables = DB::table('FB_LocationTableLink as L')
    ->join('FB_TableMaster as T', 'T.Code', '=', 'L.TableCode')
    ->leftJoin('FB_BillHead as B', function ($join) {
        $join->on('B.TableCode', '=', 'T.Code')
             ->on('B.LocationCode', '=', 'L.LocationCode')
             ->where('B.BillStatus', '=', 0);
    })
    ->leftJoin('memberprofile as M', 'M.SC_ID', '=', 'B.MemberID')
    ->where('L.LocationCode', $locationCode)
    ->select(
        'T.Code as TableCode',
        'T.TableNo',
        'L.LocationCode',
        'B.MemberID',
        'M.MemberID as MemberProfileID', // ✅ selecting from memberprofile
        DB::raw('CAST(COALESCE(B.BillStatus, 1) AS SIGNED) as BillStatus')
    )
    ->orderByRaw("CAST(SUBSTRING(T.TableNo, 2) AS UNSIGNED) ASC")
    ->get();





    
        return response()->json([
            'status' => true,
            'data' => $tables
        ]);
    
   
}
public function getWaiters(Request $request){
    $userCode= $request->query('userCode');
    $LocationCode=$request->query('LocationCode')??null;
 
    $staff = AC_UserMaster::where('DesignationCode', $userCode)
    ->where('LocationCode', $LocationCode)
    ->select('UserName','UserCode')->get();
   Log::info($staff);
     return response()->json([
            'status' => true,
            'staff_list' => $staff
        ]);
}
public function foodGroups(Request $request){
     
     $user=auth()->user();
   
      $member = Member::where('MemberId', $user->MemberID)
                    ->select('MemberId', 'DisplayName','SC_ID')
                    ->first();
                    $cardBalance = DB::table('CardClosingBalance')->where('MemberID', $member->SC_ID)->first();
    
      if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found',
        ], 404);
    }
    $currentDate = Carbon::now()->toDateString();

$currentFY = AC_FinancialYear::whereDate('DateFrom', '<=', $currentDate)
    ->whereDate('DateTo', '>=', $currentDate)
    ->first();

if ($currentFY) {
    $yearCode = $currentFY->YearCode;
} else {
    $yearCode = null; // or handle the case where no record is found
}
$groups = FB_LocationGroupLink::getGroupsByLocation($request->LocationCode);
  Log::info($groups);
 $today = now()->toDateString();
$total = FB_BillHead::where('MemberID', $member->SC_ID)
    ->whereDate('CreationDate', $today)
    ->where('BookingNo', 0)
    ->where('BillType', 10)
    ->sum(DB::raw('Amount + RoundOff')) ??0;
 $totalForMember = FB_BillHead::where('MemberID', $member->SC_ID)
    ->whereDate('CreationDate', $today)
    ->where('SyncStatus', 0)
    ->sum(DB::raw('Amount + RoundOff'))??0;  
    return response()->json([
        'success' => true,
        'message' => 'Member Verified Successfully',
        'data'=>['member'=>$member,
         'group'=>$groups,
         'card_balance'=>$cardBalance->CardBalance-$total -$totalForMember,
         'yearCode'=>$yearCode
        ],
       
    ]);
    
}
}