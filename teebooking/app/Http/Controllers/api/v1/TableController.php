<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\OccupantMaster;
use Illuminate\Http\Request;
use App\Models\FunctionMaster;
use App\Models\BanquetBooking;
use App\Models\VenueMaster;
use App\Models\VenueCharge;
use App\Models\Member;
use App\Models\AdminSetting;
use App\Models\FB_LocationGroupLink;
use App\Models\SOP;
use App\Models\FB_BillHead;
use App\Models\BanquetBookingCharges;
use App\Models\TableLocation;
use App\Models\VenuePax;
use App\Models\CancellationPolicy;
use Illuminate\Support\Facades\Validator;
use AESEncDec;
use DB;
use Razorpay\Api\Api;

use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
public function getValidateQR(Request $request)
{
    
      $validator = \Validator::make($request->all(), [
        'location' => 'required',
        'table'    => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors()
        ], 422);
    }
     $today = now()->toDateString();
    $activeBill = DB::table('FB_BillHead')
    ->where('TableCode', $request->table)
    ->whereDate('CreationDate', $today)
    ->where('LocationCode', $request->location)
    ->where('BillStatus', 0)
    ->select('BillStatus','BillNo','MemberID','YearCode','WaiterCode')
    ->first();

// If active bill found => table occupied

    $fetchTable = TableLocation::where('FB_LocationTableLink.LocationCode', $request->location)
    ->where('FB_LocationTableLink.TableCode', $request->table)
    ->join('FB_TableMaster as t', 't.Code', '=', 'FB_LocationTableLink.TableCode')
    ->join('IM_LocationMaster as l', 'l.Code', '=', 'FB_LocationTableLink.LocationCode')
    ->leftJoin('FB_Configuration as C', function ($join) {
        $join->on('C.LocationCode', '=', 'FB_LocationTableLink.LocationCode')
             ->where('C.PropertyName', '=', 'KOTDiscount');
    })
    ->select(
        'FB_LocationTableLink.*',
        't.TableNo as table_name',
        'l.LocationName as location_name',
        'C.PropertyValue as KOTDiscount'
    )
    ->first();
         if (!$fetchTable) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid QR code. Table or Location not found.'
        ], 404);
    }

      if ($activeBill) {
             $member = Member::where('SC_ID', $activeBill->MemberID)
                    ->select('MemberId', 'DisplayName','SC_ID')
                    ->first();
                    $cardBalance = DB::table('CardClosingBalance')->where('MemberID', $member->SC_ID)->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found',
        ], 404);
    }



$groups = FB_LocationGroupLink::getGroupsByLocation($request->location);

$total = FB_BillHead::where('MemberID', $member->SC_ID)
    ->whereDate('CreationDate', $today)
    ->where('BookingNo', 0)
    ->where('BillType', 10)
    ->sum(DB::raw('Amount + RoundOff'));
 $totalForMember = FB_BillHead::where('MemberID', $member->SC_ID)
    ->whereDate('CreationDate', $today)
    ->where('SyncStatus', 0)
    ->sum(DB::raw('Amount + RoundOff'));  
    Log::info($total);


   return response()->json([
        'status' => true,
        'occupied' => true,
        'table' => $fetchTable,
        'bill' => $activeBill,
        'groups'=>['member'=>$member,
         'group'=>$groups,
         'card_balance'=>$cardBalance->CardBalance-$total-$totalForMember,
         'yearCode'=>$activeBill->YearCode
         ],
        ''
      
    ]);
}else{
    return response()->json([
        'status'  => true,
        'message' => 'QR code validated successfully.',
        'table'    => $fetchTable
    ]);}
}
    
}