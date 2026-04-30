<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FB_KOTHead;
use App\Models\FB_KOTBody;
use App\Models\FB_BillHead;
use App\Models\FB_BillBody;
use App\Models\Member;
use App\Models\IM_ItemMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\KOTSetting;
use App\Models\AC_FinancialYear;
use App\Models\AC_ModeOfPayment;
use App\Models\IM_LocationMaster;
use App\Models\CompanyInfoVendor;
use App\Models\AC_UserMaster;
use Illuminate\Support\Str;
use App\Models\FB_KOTRemark;

class OrderController extends Controller
{
    
    public function placeOrder(Request $request, $billNo = null)
{
    // 1️⃣ Common Validation
    $validator = Validator::make($request->all(), [
        'MemberID'              => 'required',
        'TableCode'             => 'required|string',
        'WaiterCode'            => 'required|integer',
        'UserCode'              => 'required|string',
        'PAX'                   => 'nullable|integer|min:1',
        'ModeOfPayment'         => 'required|string',
        'ValidationMode'        => 'required|string',
        'items'                 => 'required|array|min:1',
        'items.*.Itemcode'      => 'required|integer',
        'items.*.SP'            => 'required|numeric',
        'items.*.Note'          => 'nullable|string',
        'items.*.DiscountPercentage' => 'nullable|numeric|min:0',
        'items.*.ValuePercentage'=> 'required|numeric|min:0',
        'items.*.Qty'           => 'required|numeric|min:1',
        'items.*.ItemName'      => 'required|string',
        'items.*.openItem'      => 'required|integer',
        'items.*.TaxType'       => 'required|string',
        'items.*.ServiceCharge' => 'nullable|numeric|min:0',
        'items.*.SaleUnit'      => 'required|integer',
        'Total'                 => 'required|numeric|min:1',
        'LocationCode'          => 'required|string',
        'YearCode'              => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();

    // 2️⃣ Fetch Member
    $member = Member::where('MemberID', $validated['MemberID'])
        ->select('DisplayName', 'SC_ID')
        ->first();

    if (!$member) {
        return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
    }

    // 3️⃣ Validate Table and Location
    $Location = DB::table('FB_LocationTableLink')
        ->where('TableCode', $validated['TableCode'])
        ->first();

    if (!$Location) {
        return response()->json(['status' => false, 'message' => 'Invalid TableCode. Location not found.'], 404);
    }

    // 4️⃣ Validate Items
    $itemCodes = collect($validated['items'])->pluck('Itemcode')->toArray();
    $itemsData = IM_ItemMaster::whereIn('Itemcode', $itemCodes)
        ->select('Itemcode', 'Displayas', 'SP')
        ->get();

    if ($itemsData->count() !== count($itemCodes)) {
        return response()->json(['status' => false, 'message' => 'Some items are invalid.'], 400);
    }

    // 5️⃣ Prepare Maps
    $spMap = $itemsData->pluck('SP', 'Itemcode');
    $nameMap = $itemsData->pluck('Displayas', 'Itemcode');

    // 6️⃣ Balance Check
    $CardBalance = DB::table('CardClosingBalance')
        ->where('MemberID', $member->SC_ID)
        ->first();

    $availableBalance = $CardBalance?->CardBalance ?? 0;
    $IssueNo = $CardBalance->IssueNo ?? null;

    if ($availableBalance < $validated['Total']) {
        return response()->json(['status' => false, 'message' => 'Insufficient card balance.'], 400);
    }

    $currentDate = Carbon::now();
    $locationCode = $validated['LocationCode'];
    $yearCode = $validated['YearCode'];
    $ModeOfPayment = AC_ModeOfPayment::where('Location', 'POS')->first();
    $billItems = [];
   $today = now()->toDateString();
$activeBill = DB::table('FB_BillHead')
    ->where('LocationCode', $locationCode)
    ->whereDate('CreationDate', $today)
    ->where('YearCode', $yearCode)
    ->where('TableCode', $validated['TableCode'])
    ->where('BillStatus', 0)
    ->first();

if ($activeBill && $activeBill->MemberID !== $member->SC_ID) {
    return response()->json([
        'status'  => false,
        'message' => 'This table is already occupied by another member. Please close the current bill before placing a new order.',
    ], 400);
}
    try {
        DB::beginTransaction();

        // Check if reorder or new order
        $isReorder = !is_null($billNo);
        
        $existingBill = $isReorder ? FB_BillHead::where('BillNo', $billNo)
    ->where('LocationCode', $locationCode)
    ->where('YearCode', $yearCode)
    ->first() : null;

        if ($isReorder && !$existingBill) {
            return response()->json(['status' => false, 'message' => 'Bill not found.'], 404);
        }

        // Generate Numbers
        $kotNo = FB_KOTHead::getNextKOTNo($locationCode, $yearCode);
        $nextBillNo = $isReorder ? $existingBill->BillNo : FB_BillHead::getNextBillNo($locationCode, $yearCode);

        // 🔹 Create KOT Head
        $kotHead = FB_KOTHead::create([
            'KOTNo'            => $kotNo,
            'KOTDate'          => $currentDate,
            'MemberID'         => $member->SC_ID,
            'WaiterCode'       => $validated['WaiterCode'],
            'TableCode'        => $validated['TableCode'],
            'IssueNo'          => $IssueNo,
            'PAX'              => $isReorder ? $existingBill->PAX : $validated['PAX'],
            'ModeOfPayment'    => $ModeOfPayment->ModeDesc ?? $validated['ModeOfPayment'],
            'OpeningBalance'   => $availableBalance,
            'ClosingBalance'   => $availableBalance - round($validated['Total']),
            'UserCode'         => $validated['UserCode'],
            'LocationCode'     => $locationCode,
            'YearCode'         => $yearCode,
            'CreationDate'     => $currentDate,
            'ModificationDate' => $currentDate,
        ]);

        // 🔹 Process Items
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $sp = $spMap[$item['Itemcode']] ?? 0;
            $name = $nameMap[$item['Itemcode']] ?? '';
            $baseAmount = $item['Qty'] * $item['SP'];
            $discountAmt = $baseAmount * (($item['DiscountPercentage'] ?? 0) / 100);
            $taxAmt = ($baseAmount - $discountAmt) * (($item['ValuePercentage'] ?? 0) / 100);
            $serviceChargeAmt = $baseAmount * (($item['ServiceCharge'] ?? 0) / 100);
            $amount = $baseAmount - $discountAmt + $taxAmt + $serviceChargeAmt;
            
           
      
            $totalAmount += $amount;

            FB_KOTBody::create([
                'KOTNo'            => $kotNo,
                'Itemcode'         => $item['Itemcode'],
                'ItemName'         => $name,
                'Qty'              => $item['Qty'],
                'ActualQty'        => $item['Qty'],
                'OpenItem'         => $item['openItem'],
                'Rate'             => $item['SP'],
                'Amount'           => $baseAmount,
                'DiscountPer'      => $item['DiscountPercentage'] ?? 0,
                'TaxPer'           => $item['ValuePercentage'] ?? 0,
                'TaxType'          => $item['TaxType'],
                'SCPer'            => $item['ServiceCharge'] ?? 0,
                'UserCode'         => $validated['UserCode'],
                'LocationCode'     => $locationCode,
                'YearCode'         => $yearCode,
                'CreationDate'     => $currentDate,
                'ModificationDate' => $currentDate,
                'sKotStatus'       => 'NA',
                'KotID'            => $kotHead->id,
                'UnitCode'         => $item['SaleUnit'],
                'ItemStatus'       => 0,
                'Remarks'          => $item['Note'] ?? '',
            ]);

            FB_BillBody::create([
                'BillNo'           => $nextBillNo,
                'KOTNo'            => $kotNo,
                'UserCode'         => $validated['UserCode'],
                'LocationCode'     => $locationCode,
                'YearCode'         => $yearCode,
                'CreationDate'     => $currentDate,
                'ModificationDate' => $currentDate,
            ]);

            $billItems[] = [
                'Itemcode' => $item['Itemcode'],
                'ItemName' => $name,
                'Qty'      => $item['Qty'],
                'Rate'     => $sp,
                'Amount'   => round($amount, 2),
            ];
        }

        // 🔹 Bill Head update/create
        if ($isReorder) {
            $newTotal = $existingBill->Amount + $totalAmount;
            $existingBill->update([
                'Amount'         => $newTotal,
                'RoundOff'       => round($newTotal,0)-$newTotal,
                
                'ClosingBalance' => $existingBill->ClosingBalance - round($totalAmount),
                'ModificationDate' => $currentDate,
                'WaiterCode'=>$validated['WaiterCode']
            ]);
        } else {
            FB_BillHead::create([
                'BillNo'           => $nextBillNo,
                'BillDate'         => $currentDate,
                'MemberID'         => $member->SC_ID,
                'MemberName'       => $member->DisplayName,
                'BookingNo'         =>0,
                'WaiterCode'       => $validated['WaiterCode'],
                'TableCode'        => $validated['TableCode'],
                'PAX'              => $validated['PAX'],
                'BillStatus'       => 0,
                'BillType'         => 10,
                'ValidationMode'   => $validated['ValidationMode'],
                'IssueNo'          => $IssueNo,
                'Amount'           => $totalAmount,
                'RoundOff'         => round($totalAmount, 0)-$totalAmount,
                'OpeningBalance'   => $availableBalance,
                'ClosingBalance'   => $availableBalance - round($totalAmount),
                'ModeOfPayment'    => $validated['ModeOfPayment'],
                'UserCode'         => $validated['UserCode'],
                'LocationCode'     => $locationCode,
                'YearCode'         => $yearCode,
                'CreationDate'     => $currentDate,
                'ModificationDate' => $currentDate,
            ]);
        }

        DB::commit();
// if ($CardBalance) {
//     DB::table('CardClosingBalance')
//         ->where('MemberID', $member->SC_ID)
//         ->update([
//             'CardBalance' => DB::raw("CardBalance - " . round($validated['Total'])),
       
//         ]);
// }

        $service = KOTSetting::find(1);
         $singlekotservice = KOTSetting::find(2);
        $status = (bool) ($service?->status ?? false);
        $singlekotservicestatus =(bool) ($singlekotservice?->status ?? false);
        return response()->json([
            'status' => true,
            'recipt_service' => $status,
            'single_kot_service'=>$singlekotservicestatus,
            'message' => $isReorder ? 'Reorder placed successfully' : 'Order placed successfully',
            'receipt' => [
                'BillNo'         => $nextBillNo,
                'KOTNo'          => $kotNo,
                'MemberName'     => $member->DisplayName,
                'TableCode'      => $validated['TableCode'],
                'WaiterCode'     => $validated['WaiterCode'],
                'DateTime'       => $currentDate->format('d-M-Y h:i A'),
                'PAX'            => $isReorder ? $existingBill->PAX : $validated['PAX'],
                'ModeOfPayment'  => $validated['ModeOfPayment'],
                'Items'          => $billItems,
                'TotalAmount'    => round($totalAmount, 2),
                'OpeningBalance' => round($availableBalance, 2),
                'ClosingBalance' => round($availableBalance - $totalAmount, 2),
                'YearCode'       => $yearCode,
            ],
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order Error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Error placing order: ' . $e->getMessage()], 500);
    }
}


 public function MemberPlaceOrder(Request $request)
 {
    // 1️⃣ Common Validation
    $validator = Validator::make($request->all(), [
        'MemberID'              => 'required',
        'TableCode'             => 'required|string',
        'BillType'              =>'required|string',
        'WaiterCode'            => 'required|integer',
        'Address'               =>'string|nullable',
        'DeliveryTime'               =>'string|nullable',
        'PAX'                   => 'nullable|integer|min:1',
        'ModeOfPayment'         => 'required|string',
        'ValidationMode'        => 'required|string',
        'items'                 => 'required|array|min:1',
        'items.*.Itemcode'      => 'required|integer',
        'items.*.SP'            => 'required|numeric',
        'items.*.Note'          => 'nullable|string',
        'items.*.DiscountPercentage' => 'nullable|numeric|min:0',
        'items.*.ValuePercentage'=> 'required|numeric|min:0',
        'items.*.Qty'           => 'required|numeric|min:1',
        'items.*.ItemName'      => 'required|string',
        'items.*.openItem'      => 'required|integer',
        'items.*.TaxType'       => 'required|string',
        'items.*.ServiceCharge' => 'nullable|numeric|min:0',
        'items.*.SaleUnit'      => 'required|integer',
        'Total'                 => 'required|numeric|min:1',
        'LocationCode'          => 'required|string',
        'YearCode'              => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated = $validator->validated();

    // 2️⃣ Fetch Member
    $member = Member::where('MemberID', $request->user()->MemberID)
        ->select('DisplayName', 'SC_ID')
        ->first();

    if (!$member) {
        return response()->json(['status' => false, 'message' => 'Member not found.'], 404);
    }

   if ($validated['BillType'] == 102) {
    $member = Member::where('MemberID', $request->user()->MemberID)->first();

    if ($member) {
        $member->Address = $validated['Address'];
        $member->save();
    }
}

    // 4️⃣ Validate Items
    $itemCodes = collect($validated['items'])->pluck('Itemcode')->toArray();
    $itemsData = IM_ItemMaster::whereIn('Itemcode', $itemCodes)
        ->select('Itemcode', 'Displayas', 'SP')
        ->get();

    if ($itemsData->count() !== count($itemCodes)) {
        return response()->json(['status' => false, 'message' => 'Some items are invalid.'], 400);
    }

    // 5️⃣ Prepare Maps
    $spMap = $itemsData->pluck('SP', 'Itemcode');
    $nameMap = $itemsData->pluck('Displayas', 'Itemcode');

    // 6️⃣ Balance Check
    $CardBalance = DB::table('CardClosingBalance')
        ->where('MemberID', $request->user()->SC_ID)
        ->first();

    $availableBalance = $CardBalance?->CardBalance ?? 0;
    $IssueNo = $CardBalance->IssueNo ?? null;

    if ($availableBalance < $validated['Total']) {
        return response()->json(['status' => false, 'message' => 'Insufficient card balance.'], 400);
    }

    $currentDate = Carbon::now();
    $locationCode = $validated['LocationCode'];
    $yearCode = $validated['YearCode'];
    $ModeOfPayment = AC_ModeOfPayment::where('Location', 'POS')->first();
    $billItems = [];
   $today = now()->toDateString();
$activeBill = DB::table('FB_BillHead')
    ->where('LocationCode', $locationCode)
    ->whereDate('CreationDate', $today)
    ->where('YearCode', $yearCode)
    ->where('TableCode', $validated['TableCode'])
    ->where('BillStatus', 0)
    ->first();

if ($activeBill && $activeBill->MemberID !== $member->SC_ID) {
    return response()->json([
        'status'  => false,
        'message' => 'This table is already occupied by another member. Please close the current bill before placing a new order.',
    ], 400);
}
    try {
        DB::beginTransaction();

        // Check if reorder or new order
        $isReorder = !is_null(null);
        
        $existingBill = $isReorder ? FB_BillHead::where('BillNo', $billNo)
    ->where('LocationCode', $locationCode)
    ->where('YearCode', $yearCode)
    ->first() : null;

        if ($isReorder && !$existingBill) {
            return response()->json(['status' => false, 'message' => 'Bill not found.'], 404);
        }

        // Generate Numbers
        $kotNo = FB_KOTHead::getNextKOTNo($locationCode, $yearCode);
        $nextBillNo = $isReorder ? $existingBill->BillNo : FB_BillHead::getNextBillNo($locationCode, $yearCode);

        // 🔹 Create KOT Head
        $kotHead = FB_KOTHead::create([
            'KOTNo'            => $kotNo,
            'KOTDate'          => $currentDate,
            'MemberID'         => $member->SC_ID,
            'WaiterCode'       => '1',
            'TableCode'        => $validated['TableCode'],
            'IssueNo'          => $IssueNo,
            'PAX'              => $isReorder ? $existingBill->PAX : $validated['PAX'],
            'ModeOfPayment'    => $ModeOfPayment->ModeDesc ?? $validated['ModeOfPayment'],
            'OpeningBalance'   => $availableBalance,
            'ClosingBalance'   => $availableBalance - round($validated['Total']),
            'UserCode'         => '1',
            'LocationCode'     => $locationCode,
            'YearCode'         => $yearCode,
            'CreationDate'     => $currentDate,
            'ModificationDate' => $currentDate,
        ]);

   if (! $isReorder) {
        // create the head so FK will succeed when inserting bodies
        $createdHead = FB_BillHead::create([
            'BillNo'           => $nextBillNo,
            'BillDate'         => $currentDate,
            'MemberID'         => $member->SC_ID,
            'MemberName'       => $member->DisplayName,
            'BookingNo'        => 1,
            'WaiterCode'       => '1',
            'TableCode'        => $validated['TableCode'],
            'PAX'              => $validated['PAX'],
            'BillStatus'       => '1',
            'RoomCode'         => 0,
            'RefNo'            => '',
            'Remarks'          => '',
            'RefDate'          => $currentDate,
            'BillType'         => $validated['BillType'],
            'ValidationMode'   => $validated['ValidationMode'],
            'IssueNo'          => $IssueNo,
            'Amount'           => 0,                      // will update after loop
            'RoundOff'         => 0,
            'OpeningBalance'   => $availableBalance,
            'ClosingBalance'   => $availableBalance,
            'ModeOfPayment'    => $validated['ModeOfPayment'],
            'UserCode'         => '1',
            'LocationCode'     => $locationCode,
            'YearCode'         => $yearCode,
            'CreationDate'     => $currentDate,
            'ModificationDate' => $currentDate,
        ]);
        // If your model returns BillNo from DB differently, you can fetch it:
        // $nextBillNo = $createdHead->BillNo;
    }
        // 🔹 Process Items
        $totalAmount = 0;
        foreach ($validated['items'] as $item) {
            $sp = $spMap[$item['Itemcode']] ?? 0;
            $name = $nameMap[$item['Itemcode']] ?? '';
            $baseAmount = $item['Qty'] * $item['SP'];
            $discountAmt = $baseAmount * (($item['DiscountPercentage'] ?? 0) / 100);
            $taxAmt = ($baseAmount - $discountAmt) * (($item['ValuePercentage'] ?? 0) / 100);
            $serviceChargeAmt = $baseAmount * (($item['ServiceCharge'] ?? 0) / 100);
            $amount = $baseAmount - $discountAmt + $taxAmt + $serviceChargeAmt;
            
           
      
            $totalAmount += $amount;

            FB_KOTBody::create([
                'KOTNo'            => $kotNo,
                'Itemcode'         => $item['Itemcode'],
                'ItemName'         => $name,
                'Qty'              => $item['Qty'],
                'ActualQty'        => $item['Qty'],
                'OpenItem'         => $item['openItem'],
                'Rate'             => $item['SP'],
                'Amount'           => $baseAmount,
                'DiscountPer'      => $item['DiscountPercentage'] ?? 0,
                'TaxPer'           => $item['ValuePercentage'] ?? 0,
                'TaxType'          => $item['TaxType'],
                'SCPer'            => $item['ServiceCharge'] ?? 0,
                'UserCode'         => '1',
                'LocationCode'     => $locationCode,
                'YearCode'         => $yearCode,
                'CreationDate'     => $currentDate,
                'ModificationDate' => $currentDate,
                'sKotStatus'       => 'NA',
                'KotID'            => $kotHead->id,
                'UnitCode'         => $item['SaleUnit'],
                'ItemStatus'       => 0,
                'Remarks'          => $item['Note'] ?? '',
            ]);

            FB_BillBody::create([
                'BillNo'           => $nextBillNo,
                'KOTNo'            => $kotNo,
                'UserCode'         => '1',
                'LocationCode'     => $locationCode,
                'YearCode'         => $yearCode,
                'CreationDate'     => $currentDate,
                'ModificationDate' => $currentDate,
            ]);

            $billItems[] = [
                'Itemcode' => $item['Itemcode'],
                'ItemName' => $name,
                'Qty'      => $item['Qty'],
                'Rate'     => $sp,
                'Amount'   => round($amount, 2),
            ];
        }

        // 🔹 Bill Head update/create
        if ($isReorder) {
            $newTotal = $existingBill->Amount + $totalAmount;
            $existingBill->update([
                'Amount'         => $newTotal,
                'RoundOff'       => round($newTotal,0)-$newTotal,
                
                'ClosingBalance' => $existingBill->ClosingBalance - round($totalAmount),
                'ModificationDate' => $currentDate,
                'WaiterCode'=>'1',
                'delivery_time' => $validated['DeliveryTime'] 
    ? date('Y-m-d H:i:s', strtotime($validated['DeliveryTime']))
    : null,

            ]);
           
    } else {
        // update the head we created earlier with correct amount and closing balance
        FB_BillHead::where('BillNo', $nextBillNo)
            ->where('LocationCode', $locationCode)
            ->where('YearCode', $yearCode)
            ->update([
                'Amount'         => round($totalAmount, 0) ,
                'RoundOff'       => round($totalAmount, 0) - $totalAmount,
                'OpeningBalance' => $availableBalance,
                'ClosingBalance' => $availableBalance - round($totalAmount),
                'ModificationDate'=> $currentDate,
                'delivery_time' => $validated['DeliveryTime'] 
    ? date('Y-m-d H:i:s', strtotime($validated['DeliveryTime']))
    : null,

            ]);
    }
        // } else {
        //   Log::info($validated['DeliveryTime']);
        //     FB_BillHead::create([
        //         'BillNo'           => $nextBillNo,
        //         'BillDate'         => $currentDate,
        //         'MemberID'         => $member->SC_ID,
        //         'MemberName'       => $member->DisplayName,
        //         'BookingNo'         =>0,
        //         'WaiterCode'       => '1',
                
        //         'TableCode'        => $validated['TableCode'],
        //         'PAX'              => $validated['PAX'],
        //         'BillStatus'       => 1,
                
        //         'ValidationMode'   => $validated['ValidationMode'],
        //         'IssueNo'          => $IssueNo,
        //         'Amount'           => $totalAmount,
        //         'RoundOff'         => round($totalAmount, 0)-$totalAmount,
        //         'OpeningBalance'   => $availableBalance,
        //         'ClosingBalance'   => $availableBalance - round($totalAmount),
        //         'ModeOfPayment'    => $validated['ModeOfPayment'],
        //         'UserCode'         => '1',
        //         'LocationCode'     => $locationCode,
        //         'YearCode'         => $yearCode,
        //         'CreationDate'     => $currentDate,
        //         'ModificationDate' => $currentDate,
        //     ]);
        // }

        DB::commit();
// if ($CardBalance) {
//     DB::table('CardClosingBalance')
//         ->where('MemberID', $member->SC_ID)
//         ->update([
//             'CardBalance' => $availableBalance - round($totalAmount),
       
//         ]);
// }

        $service = KOTSetting::find(1);
        $status = (bool) ($service?->status ?? false);

        return response()->json([
            'status' => true,
            'recipt_service' => $status,
            'message' => $isReorder ? 'Reorder placed successfully' : 'Order placed successfully',
            'receipt' => [
                'BillNo'         => $nextBillNo,
                'KOTNo'          => $kotNo,
                'MemberName'     => $member->DisplayName,
                'TableCode'      => $validated['TableCode'],
                'WaiterCode'     => $validated['WaiterCode'],
                'DateTime'       => $currentDate->format('d-M-Y h:i A'),
                'PAX'            => $isReorder ? $existingBill->PAX : $validated['PAX'],
                'ModeOfPayment'  => $validated['ModeOfPayment'],
                'Items'          => $billItems,
                'TotalAmount'    => round($totalAmount, 2),
                'OpeningBalance' => round($availableBalance, 2),
                'ClosingBalance' => round($availableBalance - $totalAmount, 2),
                'YearCode'       => $yearCode,
            ],
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Order Error: ' . $e->getMessage());
        return response()->json(['status' => false, 'message' => 'Error placing order: ' . $e->getMessage()], 500);
    }
}

public function getReciept(Request $request, $billNo = null, $Location = null, $Year = null)
{
    if (!$billNo || !$Location || !$Year) {
        return response()->json(['status' => false, 'message' => 'Missing parameters: BillNo, Location, or Year.'], 400);
    }

    $bill = DB::table('FB_BillHead')
        ->where(['BillNo' => $billNo, 'LocationCode' => $Location, 'YearCode' => $Year])
        ->first();

    if (!$bill) {
        return response()->json(['status' => false, 'message' => 'Bill not found.'], 404);
    }

  $member = Member::where('SC_ID', $bill->MemberID)
        ->select('MemberID', 'SC_ID')
        ->first();
    // ✅ Fetch all related data in minimal queries
    $vendor = IM_LocationMaster::where('Code', $Location)
        ->select('VendorID', 'LocationName')
        ->first();

    $vendorDetails = DB::table('company_info_vendor as V')
        ->leftJoin('AC_CityMaster as C', 'C.CityCode', '=', 'V.City')
        ->leftJoin('AC_StateMaster as S', 'S.StateCode', '=', 'V.State')
        ->where('V.VendorID', $vendor->VendorID)
        ->select('V.Name', 'V.Address', 'V.Pincode', 'V.Phone', 'V.GSTNo', 'C.CityName as City', 'S.StateName as State')
        ->first();

    $table = DB::table('FB_TableMaster')->where('Code', $bill->TableCode)->value('TableNo');
    $waiter = DB::table('AC_UserMaster')->where('UserCode', $bill->WaiterCode)->value('UserName');
    $cashier = DB::table('AC_UserMaster')->where('UserCode', $bill->UserCode)->value('UserName');

    // ✅ Fetch all bill items in a single optimized query
    $kotItems = DB::table('FB_KOTBody')
        ->whereIn('KOTNo', function ($query) use ($billNo, $Location, $Year) {
            $query->select('KOTNo')
                ->from('FB_BillBody')
                ->where(['BillNo' => $billNo, 'LocationCode' => $Location, 'YearCode' => $Year]);
        })
        ->where(['LocationCode' => $Location, 'YearCode' => $Year])
        ->select('id', 'KOTNo', 'ItemName', 'Qty', 'Rate', 'Amount', 'TaxPer', 'SCPer', 'TaxType', 'Remarks', 'DiscountPer')
        ->get();

    if ($kotItems->isEmpty()) {
        return response()->json(['status' => false, 'message' => 'No items found for this bill.'], 404);
    }

    // ✅ Group items by KOT number
    $KOTWiseData = $kotItems->groupBy('KOTNo')->map(fn($items, $KOTNo) => [
        'KOTNo' => $KOTNo,
        'Items' => $items->map(fn($i) => [
            'id' => $i->id,
            'ItemName' => $i->ItemName,
            'Qty' => (float) $i->Qty,
            'Rate' => (float) $i->Rate,
            'Amount' => round($i->Qty * $i->Rate, 2),
            'TaxPer' => (float) $i->TaxPer,
            'SCPer' => (float) $i->SCPer,
            'TaxType' => $i->TaxType,
            'DiscountPer' => (float) ($i->DiscountPer ?? 0),
            'Remarks' => $i->Remarks ?? '',
        ])->values()
    ])->values();

    // ✅ Compute summary efficiently
    $grossTotal = $kotItems->sum(fn($i) => $i->Rate * $i->Qty);

    $discountTotal = $kotItems->sum(fn($i) =>
        ($i->Rate * $i->Qty * ($i->DiscountPer ?? 0)) / 100
    );

    $taxTotal = $kotItems->sum(fn($i) =>
        (($i->Rate * $i->Qty) - ($i->Rate * $i->Qty * ($i->DiscountPer ?? 0) / 100)) * ($i->TaxPer / 100)
    );

    $serviceChargeTotal = $kotItems
        ->filter(fn($i) => $i->SCPer > 0)
        ->sum(fn($i) => ($i->Rate * $i->Qty * $i->SCPer) / 100);

    $billAmount = $grossTotal - $discountTotal + $taxTotal + $serviceChargeTotal;
    $roundOff = round($billAmount) - $billAmount;
    $finalAmount = round($billAmount);

    $serviceChargePercents = $kotItems->where('SCPer', '>', 0)->pluck('SCPer')->unique()->values();

    // ✅ Construct response
    return response()->json([
        'status' => true,
        'message' => 'Receipt fetched successfully.',
        'data' => [
            'Status' => (int) $bill->BillStatus,
            'BillNo' => $bill->BillNo,
            'BillDate' => $bill->CreationDate,
            'MemberID' => $member->MemberID,
            'SC_ID'=>$member->SC_ID,
            'ModeOfPayment' => $bill->ModeOfPayment,
            'OpeningBalance' => round($bill->OpeningBalance, 2),
            'ClosingBalance' => round($bill->ClosingBalance, 2),
            'Vendor' => $vendorDetails,
            'Location' => $vendor,
            'TableName' => $table ?? 'N/A',
            'WaiterName' => $waiter ?? 'N/A',
            'CashierName' => $cashier ?? 'N/A',
            'KOTWiseData' => $KOTWiseData,
            'Summary' => [
                'GrossTotal' => round($grossTotal, 2),
                'SubTotal' => round($grossTotal, 2),
                'Discount' => round($discountTotal, 2),
                'Tax' => round($taxTotal, 2),
                'ServiceChargePercent' => $serviceChargePercents,
                'ServiceChargeAmount' => round($serviceChargeTotal, 2),
                'RoundOff' => round($roundOff, 2),
                'BillAmount' => round($billAmount, 2),
                'AmountAfterRoundOff' => $finalAmount,
            ],
        ]
    ], 200);
}



public function completeOrder(Request $request, $billNo = null, $Location = null, $Year = null)
{
    // Validate essential parameters
    if (!$billNo || !$Location || !$Year) {
        return response()->json([
            'status'  => false,
            'message' => 'Missing: billNo, Location, or Year.',
        ], 400);
    }

    $today = now()->toDateString();

    // Fetch bill with minimal columns
    $bill = DB::table('FB_BillHead')
        ->select('MemberID', 'Amount', 'BookingNo')
        ->where([
            ['BillNo', $billNo],
            ['LocationCode', $Location],
            ['YearCode', $Year],
        ])
        ->first();

    if (!$bill) {
        return response()->json([
            'status'  => false,
            'message' => 'Bill not found for given BillNo/Location/Year.',
        ], 404);
    }

    // Get Card balance
    $cardBalance = (float) DB::table('CardClosingBalance')
        ->where('MemberID', $bill->MemberID)
        ->value('CardBalance') ?? 0;

    DB::beginTransaction();
    try {
        // Fetch today's bills for member in one query
        $todayBills = DB::table('FB_BillHead')
            ->where('MemberID', $bill->MemberID)
            ->whereDate('CreationDate', $today)
            ->where('BillStatus', 1)
            ->get(['Amount', 'BookingNo']);

       $totalOnspot = $todayBills
    ->where('BookingNo', 0)
    ->map(fn($item) => round($item->Amount))
    ->sum();
    $totalBooking = $todayBills
    ->where('BookingNo', )
    ->map(fn($item) => round($item->Amount))
    ->sum();
         

        // Compute new balances
        $baseOpening   =$cardBalance - round( $totalOnspot);
        $currentAmount = round($bill->Amount, 2);
        $currentClosing = $baseOpening - round(  $currentAmount);


Log::info(" currentClosing $currentClosing  currentAmount $currentAmount  currentClosing $currentClosing  baseOpening $baseOpening   totalOnspot $totalOnspot totalBooking $totalBooking");
        // Update BillHead
        DB::table('FB_BillHead')
            ->where([
                ['BillNo', $billNo],
                ['LocationCode', $Location],
                ['YearCode', $Year],
            ])
            ->update([
                'OpeningBalance'   => $baseOpening,
                'ClosingBalance'   => $currentClosing,
                'BillStatus'       => 1,
                'ModificationDate' => now(),
            ]);

        // Update KOTHead (bulk update)
        DB::table('FB_KOTHead')
            ->where([
                ['MemberID', $bill->MemberID],
                ['LocationCode', $Location],
                ['YearCode', $Year],
            ])
            ->whereDate('CreationDate', $today)
            ->update([
                'OpeningBalance'   => $baseOpening,
                'ClosingBalance'   => $currentClosing,
                'ModificationDate' => now(),
            ]);

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Order settled successfully.',
            'data'    => [
                'BillNo'       => $billNo,
                'LocationCode' => $Location,
                'YearCode'     => $Year,
                'BillStatus'   => 1,
                'NewOpening'   => $baseOpening,
                'NewClosing'   => $currentClosing,
            ],
        ]);
    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'status'  => false,
            'message' => 'Settlement failed: ' . $e->getMessage(),
        ], 500);
    }
}

public function getMemberOrder(Request $request)
{
    $user = auth()->user();

    // Filters
    $status = $request->query('status'); // pending/completed/cancelled
    $perPage = $request->query('per_page', 10); // default 10

    $query = FB_BillHead::where('FB_BillHead.MemberID', $user->SC_ID)
        ->leftJoin('FB_TableMaster', 'FB_TableMaster.code', '=', 'FB_BillHead.TableCode')
        ->select(
            'FB_BillHead.id',
            'FB_BillHead.BillNo',
            'FB_BillHead.TableCode',
            DB::raw('COALESCE(FB_TableMaster.TableNo, "Unknown") as TableNo'),
            'FB_BillHead.Amount',
            'FB_BillHead.BillStatus',
            'FB_BillHead.CreationDate',
            'FB_BillHead.MemberID',
            'FB_BillHead.MemberName',
            'FB_BillHead.YearCode',
            'FB_BillHead.LocationCode',
            'FB_BillHead.delivery_time',
            'FB_BillHead.BillType'
        )
        ->orderBy('FB_BillHead.CreationDate', 'desc');

    // Apply filter if not "all"
    if ($status && $status !== 'all') {
        $query->where('FB_BillHead.BillStatus', ucfirst($status));
    }
    

    $Orders = $query->paginate($perPage);
 $duration = DB::table('KOT_settings')->value('Delivery_Time_Duration');
    return response()->json([
        'status'  => true,
        'message' => 'Orders fetched successfully',
        'data'    => $Orders,
        'cancel_durations'=>$duration//mins.
    ]);
}



public function modifyKOT(Request $request)
{
    // 1️⃣ Validate input
    $validator = Validator::make($request->all(), [
        'KOTNo'        => 'required|integer',
        'LocationCode' => 'required|integer',
        'YearCode'     => 'required|string',
        'items'        => 'required|array|min:1',
        'Remark'        =>'required|string',
        'items.*.id'   => 'required|integer|exists:FB_KOTBody,id',
        'items.*.Qty'  => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated     = $validator->validated();
    $KOTNo         = $validated['KOTNo'];
    $Remark=$validated['Remark'];
    $locationCode  = $validated['LocationCode'];
    $yearCode      = $validated['YearCode'];
    $modifiedItems = collect($validated['items']);

    try {
        DB::beginTransaction();

        // 2️⃣ Fetch KOT Head
        $kotHead = FB_KOTHead::where([
            ['KOTNo', $KOTNo],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->first();

        if (!$kotHead) {
            return response()->json(['status' => false, 'message' => 'KOT not found.'], 404);
        }

        // 3️⃣ Fetch related Bill Info
        $billBody = FB_BillBody::where([
            ['KOTNo', $KOTNo],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->first();

        if (!$billBody) {
            return response()->json(['status' => false, 'message' => 'Bill Body not found.'], 404);
        }

        $billHead = FB_BillHead::where([
            ['BillNo', $billBody->BillNo],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->first();

        if (!$billHead) {
            return response()->json(['status' => false, 'message' => 'Bill not found.'], 404);
        }

// ✅ 1️⃣ Create a unique modification number
$modificationNo = DB::table('FB_DuplicateKOTHead_1')
    ->where('LocationCode', $locationCode)
    ->where('YearCode', $yearCode)
    ->max('ModificationNo') + 1 ?? 1;

// ✅ 2️⃣ Insert into FB_DuplicateKOTHead_1 & FB_DuplicateKOTHead_2
foreach (['FB_DuplicateKOTHead_1', 'FB_DuplicateKOTHead_2'] as $dupHeadTable) {
    DB::table($dupHeadTable)->insert([
        'ModificationNo'       => $modificationNo,
        'ModifiedBy'           => auth()->id() ?? 0,
        'ModifiedOn'           => now(),
        'ModificationRemarks'  => $Remark,
        'KOTNo'                => $kotHead->KOTNo,
        'KOTDate'              => $kotHead->KOTDate,
        'MemberID'             => $kotHead->MemberID,
        'IssueNo'              => $kotHead->IssueNo,
        'WaiterCode'           => $kotHead->WaiterCode,
        'TableCode'            => $kotHead->TableCode,
        'PAX'                  => $kotHead->PAX,
        'RefNo'                => $kotHead->RefNo,
        'ModeOfPayment'        => $kotHead->ModeOfPayment,
        'OpeningBalance'       => $kotHead->OpeningBalance,
        'ClosingBalance'       => $kotHead->ClosingBalance,
        'CreationDate'         => $kotHead->CreationDate,
        'ModificationDate'     => now(),
        'UserCode'             => $kotHead->UserCode,
        'LocationCode'         => $kotHead->LocationCode,
        'YearCode'             => $kotHead->YearCode,
        'HOST_NAME'            => request()->getHost(),
        'HOST_IP'              => request()->ip(),
    ]);
}

// ✅ 3️⃣ Insert KOT Body rows into FB_DuplicateKOTBody_1 & FB_DuplicateKOTBody_2
$kotBodyItems = FB_KOTBody::where([
    ['KOTNo', $KOTNo],
    ['LocationCode', $locationCode],
    ['YearCode', $yearCode],
])->get();

foreach (['FB_DuplicateKOTBody_1', 'FB_DuplicateKOTBody_2'] as $dupBodyTable) {
    foreach ($kotBodyItems as $item) {
        DB::table($dupBodyTable)->insert([
            'ModificationNo'  => $modificationNo,
            'KOTNo'           => $item->KOTNo,
            'Itemcode'        => $item->Itemcode,
            'ItemName'        => $item->ItemName,
            'OpenItem'        => $item->OpenItem,
            'UnitCode'        => $item->UnitCode,
            'Qty'             => $item->Qty,
            'ActualQty'       => $item->ActualQty,
            'Rate'            => $item->Rate,
            'SchemeRate'      => $item->SchemeRate,
            'Amount'          => $item->Amount,
            'DiscountPer'     => $item->DiscountPer,
            'TaxType'         => $item->TaxType,
            'TaxPer'          => $item->TaxPer,
            'SCPer'           => $item->SCPer,
            'CreationDate'    => $item->CreationDate,
            'ModificationDate'=> now(),
            'UserCode'        => $item->UserCode,
            'LocationCode'    => $item->LocationCode,
            'YearCode'        => $item->YearCode,
            'sKotStatus'      => $item->sKotStatus,
            'KotID'           => $item->KotID,
            'ItemStatus'      => $item->ItemStatus,
            'Remarks'         => $Remark,
        ]);
    }
}

        // 4️⃣ Process item updates
        foreach ($modifiedItems as $itemData) {
            $kotItem = FB_KOTBody::find($itemData['id']);
            if (!$kotItem) continue;

            $oldQty = (float)$kotItem->Qty;
            $newQty = (float)$itemData['Qty'];

            // Allow only reducing quantity, not increasing
            if ($newQty > $oldQty) {
                DB::rollBack();
                return response()->json([
                    'status'  => false,
                    'message' => "You cannot increase quantity for Item ID {$kotItem->id}. Only reduction is allowed.",
                ], 400);
            }

            // Update Qty and recalculate item amount
            $kotItem->update([
                'Qty'              => $newQty,
                'Amount'           => $kotItem->Rate * $newQty,
                'Remarks'           =>$Remark,
                'ModificationDate' => now(),
            ]);
        }

        // 5️⃣ Recalculate totals based on updated KOT items
        $AllKOTs = FB_BillBody::where([
    ['BillNo', $billBody->BillNo],
    ['LocationCode', $locationCode],
    ['YearCode', $yearCode],
])->pluck('KOTNo');  // returns collection like ['KOT001', 'KOT002']

        
        $kotItems = FB_KOTBody::whereIn('KOTNo', $AllKOTs)
    ->where('LocationCode', $locationCode)
    ->where('YearCode', $yearCode)
    ->get();
Log::info("$AllKOTs Fetching KOT items for KOTNos:", $AllKOTs->toArray());


      $grossTotal = 0;
$discountTotal = 0;
$taxTotal = 0;
$serviceChargeTotal = 0;

foreach ($kotItems as $i) {

    $rate = floatval($i->Rate);
    $qty  = floatval($i->Qty);
    $amount = $rate * $qty;

    $discPer  = floatval($i->DiscountPer ?? 0);
    $taxPer   = floatval($i->TaxPer ?? 0);
    $scPer    = floatval($i->SCPer ?? 0);

    // Gross
    $grossTotal += $amount;

    // Discount
    $discountTotal += ($amount * $discPer) / 100;

    // Tax (after discount)
    $taxable = $amount - (($amount * $discPer) / 100);
    $taxTotal += ($taxable * $taxPer) / 100;

    // Service Charge
    if ($scPer > 0) {
        $serviceChargeTotal += ($amount * $scPer) / 100;
    }
}

        $billAmount = $grossTotal - $discountTotal + $taxTotal + $serviceChargeTotal;
        $roundOff   = round($billAmount) - $billAmount;
        $finalAmount = round($billAmount);
        
        
        // 6️⃣ Update Bill Head
        $newClosing = (float)$billHead->OpeningBalance - $finalAmount;
        
        
        $billHead->update([
            'Amount'          => $finalAmount,
            'RoundOff'        => $roundOff,
            'ClosingBalance'  => round($newClosing, 2),
            'ModificationDate'=> now(),
        ]);

        // 7️⃣ Update KOT Head (same closing balance logic)
        $kotNewClosing = (float)$kotHead->OpeningBalance - $finalAmount;

        $kotHead->update([
            'ClosingBalance'  => round($kotNewClosing, 2),
            'ModificationDate'=> now(),
        ]);

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'KOT modified successfully.',
            'KOTNo'   => $KOTNo,
            'Amount'  => $finalAmount,
            'RoundOff' => $roundOff,
            'ClosingBalance' => round($newClosing, 2)
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status'  => false,
            'message' => 'Error modifying KOT: ' . $e->getMessage(),
        ], 500);
    }
}


public function getActiveOrdersByWaiter(Request $request)
{
    $validator = Validator::make($request->all(), [
        'WaiterCode' => 'required|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 422);
    }

    $validated = $validator->validated();

    // Fetch active KOT bodies assigned to this waiter
    $activeKOTBodies = FB_KOTBody::where('UserCode', $validated['WaiterCode'])
                        ->where('sKotStatus', 'OPEN')
                        ->where('ItemStatus', 'ACTIVE')
                        ->get();

    if ($activeKOTBodies->isEmpty()) {
        return response()->json([
            'status'  => true,
            'message' => 'No active orders found.',
            'data'    => []
        ]);
    }

    // Group items by KOTNo
    $orders = $activeKOTBodies->groupBy('KOTNo')->map(function ($items, $kotNo) {
        $firstItem = $items->first();
        return [
            'KOTNo'      => $kotNo,
            'TableCode'  => $firstItem->TableCode,
            'WaiterCode' => $firstItem->UserCode,
            'YearCode'   => $firstItem->YearCode,
            'LocationCode'=> $firstItem->LocationCode,
            'Items'      => $items->map(function ($item) {
                return [
                    'Itemcode'  => $item->Itemcode,
                    'ItemName'  => $item->ItemName,
                    'Qty'       => $item->Qty,
                    'Rate'      => $item->Rate,
                    'Amount'    => $item->Amount,
                    'ItemStatus'=> $item->ItemStatus,
                    'Remarks'   => $item->Remarks,
                ];
            })->values(),
            'CreationDate'=> $firstItem->CreationDate,
        ];
    })->values();

    return response()->json([
        'status'  => true,
        'message' => 'Active orders fetched successfully.',
        'data'    => $orders
    ]);
}

    public function KOTRemarks()
    {
        $remarks = FB_KOTRemark::all();
        return response()->json([
            'status' => true,
            'data' => $remarks
        ], 200);
    }

public function cancelMemberOrder(Request $request)
{
    // 1️⃣ Validate only required fields (items are NOT needed)
    $validator = Validator::make($request->all(), [
        'BillNo'        => 'required|integer',
        'LocationCode'  => 'required|string',
        'YearCode'      => 'required|string',
        'Remark'        => 'required|string',
    ]);
Log::info($request->all());
    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors(),
        ], 422);
    }

    $validated    = $validator->validated();
    $billNo       = $validated['BillNo'];
    $locationCode = $validated['LocationCode'];
    $yearCode     = $validated['YearCode'];
    $remark       = $validated['Remark'];

    try {
        DB::beginTransaction();

        // 2️⃣ Fetch Bill Head
        $billHead = FB_BillHead::where([
            ['BillNo', $billNo],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->first();

        if (!$billHead) {
            return response()->json(['status' => false, 'message' => 'Bill not found'], 404);
        }
 $CardBalance = DB::table('CardClosingBalance')
        ->where('MemberID', auth()->user()->SC_ID)
        ->first();
        
        $AMT=$billHead->Amount;
        // 3️⃣ Fetch All KOT Numbers for this Bill
        $allKOTs = FB_BillBody::where([
            ['BillNo', $billNo],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->pluck('KOTNo');

        if ($allKOTs->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'No KOT found'], 404);
        }

        // 4️⃣ Fetch KOT Head (first KOT)
        $kotHead = FB_KOTHead::where([
            ['KOTNo', $allKOTs->first()],
            ['LocationCode', $locationCode],
            ['YearCode', $yearCode],
        ])->first();

        // 5️⃣ Generate Modification No
        $modNo = DB::table('FB_DuplicateKOTHead_1')
                ->where('LocationCode', $locationCode)
                ->where('YearCode', $yearCode)
                ->max('ModificationNo');
        $modNo = ($modNo ?? 0) + 1;

        // 6️⃣ Insert Backup into Duplicate KOT Head tables
        foreach (['FB_DuplicateKOTHead_1', 'FB_DuplicateKOTHead_2'] as $dupTable) {
            DB::table($dupTable)->insert([
                'ModificationNo'      => $modNo,
                'ModifiedBy'          => auth()->id() ?? 0,
                'ModifiedOn'          => now(),
                'ModificationRemarks' => $remark,
                'KOTNo'               => $kotHead->KOTNo,
                'KOTDate'             => $kotHead->KOTDate,
                'MemberID'            => $billHead->MemberID,
                'IssueNo'             => $kotHead->IssueNo,
                'WaiterCode'          => $kotHead->WaiterCode,
                'TableCode'           => $kotHead->TableCode,
                'PAX'                 => $kotHead->PAX,
                'ModeOfPayment'       => $kotHead->ModeOfPayment,
                'OpeningBalance'      => $kotHead->OpeningBalance,
                'ClosingBalance'      => $kotHead->ClosingBalance,
                'CreationDate'        => $kotHead->CreationDate,
                'ModificationDate'    => now(),
                'UserCode'            => $kotHead->UserCode,
                'LocationCode'        => $locationCode,
                'YearCode'            => $yearCode,
                'HOST_NAME'           => request()->getHost(),
                'HOST_IP'             => request()->ip(),
            ]);
        }

        // 7️⃣ Fetch ALL KOT Items
        $kotItems = FB_KOTBody::whereIn('KOTNo', $allKOTs)
                    ->where('LocationCode', $locationCode)
                    ->where('YearCode', $yearCode)
                    ->get();

        // 8️⃣ Insert Backup into Duplicate KOT Body tables
        foreach (['FB_DuplicateKOTBody_1', 'FB_DuplicateKOTBody_2'] as $dupBody) {
            foreach ($kotItems as $item) {
                DB::table($dupBody)->insert([
                    'ModificationNo'  => $modNo,
                    'KOTNo'           => $item->KOTNo,
                    'Itemcode'        => $item->Itemcode,
                    'ItemName'        => $item->ItemName,
                    'Qty'             => $item->Qty,
                    'Rate'            => $item->Rate,
                    'Amount'          => $item->Amount,
                    'DiscountPer'     => $item->DiscountPer,
                    'TaxPer'          => $item->TaxPer,
                    'SCPer'           => $item->SCPer,
                    'CreationDate'    => $item->CreationDate,
                    'ModificationDate'=> now(),
                    'UserCode'        => $item->UserCode,
                    'LocationCode'    => $item->LocationCode,
                    'YearCode'        => $item->YearCode,
                    'Remarks'         => $remark,
                ]);
            }
        }

        // 9️⃣ Cancel ALL KOT Items → Qty = 0
        foreach ($kotItems as $item) {
            $item->update([
                'Qty'              => 0,
                'Remarks'          => $remark,
                'ModificationDate' => now(),
            ]);
        }

        // 🔟 Update Bill → Amount = 0, Closing Balance restored
        $billHead->update([
            'Amount'          => 0,
            'RoundOff'        => 0,
            'ClosingBalance'  => $billHead->OpeningBalance,
            'BillStatus'      => 1,
            'ModificationDate'=> now(),
        ]);

        // 1️⃣1️⃣ Update KOT HEAD also
        FB_KOTHead::whereIn('KOTNo', $allKOTs)
            ->where('LocationCode', $locationCode)
            ->where('YearCode', $yearCode)
            ->update([
                'ClosingBalance'  => $billHead->OpeningBalance,
                'ModificationDate'=> now(),
            ]);
// if ($CardBalance) {
//     DB::table('CardClosingBalance')
//         ->where('MemberID', auth()->user()->SC_ID)
//         ->update([
//             'CardBalance' => DB::raw("CardBalance + " . round($AMT)),
       
//         ]);
// }
        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => 'Bill cancelled successfully',
            'BillNo'  => $billNo,
            'Amount'  => 0,
            'ClosingBalance' => $billHead->OpeningBalance,
            'ModificationNo' => $modNo
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => "Error: " . $e->getMessage(),
        ], 500);
    }
}
public function getMemberOrdersForSync(Request $request)
{
    $duration = DB::table('KOT_settings')->value('Delivery_Time_Duration');
    $today    = now()->toDateString();

    $activeBills = DB::table('FB_BillHead')
        ->where('SyncStatus', 0)
        ->whereDate('CreationDate', $today)

        ->where(function ($q) use ($duration) {

            // Case 1: BillType != 102 → always send
            $q->where('BillType', '!=', 102)

            // Case 2: BillType 102 → follow timing logic
            ->orWhere(function ($sub) use ($duration) {

                $sub->where('BillType', 102)
                    ->whereNotNull('delivery_time')

                    // Convert NOW() to IST so comparison works
                    ->where(function ($timer) use ($duration) {

                        // Condition A → within duration window
                        $timer->whereRaw("
                            CONVERT_TZ(NOW(), '+00:00', '+05:30')
                            >= delivery_time - INTERVAL {$duration} MINUTE
                        ")
                        ->whereRaw("
                            CONVERT_TZ(NOW(), '+00:00', '+05:30')
                            < delivery_time
                        ")

                        // OR Condition B → delivery time already passed
                        ->orWhereRaw("
                            CONVERT_TZ(NOW(), '+00:00', '+05:30') >= delivery_time
                        ");
                    });
            });
        })

        ->get();

    return response()->json([
        'status' => true,
        'data'   => $activeBills
    ]);
}
public function updateMemberOrdersForSync(Request $request)
{
    // 🛑 Validate incoming request
    $validator = Validator::make($request->all(), [
        'ids' => 'required|array|min:1',
        'ids.*' => 'integer|exists:FB_BillHead,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $ids = $request->ids;
    $updated = [];
    $alreadySynced = [];

    DB::beginTransaction();

    try {
        foreach ($ids as $id) {

            $bill = DB::table('FB_BillHead')->where('id', $id)->first();

            if (!$bill) {
                continue;
            }

            // ✔ CASE 1: Already synced → do nothing
            if ($bill->SyncStatus == 1) {
                $alreadySynced[] = $id;
                continue;
            }

           $CardBalance = DB::table('CardClosingBalance')
        ->where('MemberID',  $bill->MemberID)
        ->first();

    $availableBalance = $CardBalance?->CardBalance ?? 0;

            if ($bill->MemberID) {
    DB::table('CardClosingBalance')
        ->where('MemberID', $bill->MemberID)
        ->update([
            'CardBalance' => $availableBalance - round($bill->Amount),
       
        ]);
}


            // Update the bill SyncStatus to 1
            DB::table('FB_BillHead')
                ->where('id', $id)
                ->update([
                    'SyncStatus' => 1,
                    'ModificationDate' => now()
                ]);

            $updated[] = $id;
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Orders updated successfully',
            'updated_ids' => $updated,
            'already_synced_ids' => $alreadySynced
        ]);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Error updating orders: ' . $e->getMessage()
        ], 500);
    }
}



}
