<?php

namespace App\Http\Controllers\api\v1;

use App\Services\FCMService;

use App\CPU\Helpers;

use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

use App\Http\Controllers\Controller;

use App\Models\MemberAccountLedger;

use App\Models\AdminSetting;

use App\Models\AffilatedClubs;

use App\Models\Event;

use App\Models\CardClosingBalance;

use App\Models\CustomerStatement;

use App\Models\TeeSessionCategory;

use App\Models\MemberReceipt;

use App\Models\OtpModel;

use App\Models\TeeSheet;

use App\Models\Member;

use App\Models\TeeMyBuddies;

use App\Models\TeeBookingDetails;

use App\Models\TeeHole;

use App\Models\TeeSession;

use App\Models\TeeGroup;

use App\Rules\CurrentPasswordValidation;

use Rap2hpoutre\FastExcel\FastExcel;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Hash;

use DB;

use AESEncDec;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Razorpay\Api\Api;


class MemberController extends Controller

{

   public function member_profile_get(){

     

       $member = Member::where("memberprofile.id",auth()->user()->id)

       ->leftJoin('categorymaster', 'categorymaster.Catg_Code', '=', 'memberprofile.CategoryCode')->first();

        // ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();

        return response()->json(array('status'=> true,'data'=>$member) , 200);

    }

    

    public function getMemberReceipts()

    {
        
        \Log::info('customerstatement');
        $user = Member::where("id",auth()->user()->id)->first();

        if (!$user) {

            $return_data = [

                'status' => false,

                'message' => 'Member not found',

                'data' => []

            ];

            return response()->json($return_data , 400);

        }

        $memberReceipts = MemberReceipt::where('Mem_Id', auth()->user()->SC_ID)->first();

        if($memberReceipts == null){

            $user['pdf'] = "";

    	    $user['txn_id'] = "";

    	    $user['pay_status'] = "";

    	    $user['amount_payable'] = "";

    	    $user['received_date'] = "";    

    	    $user['bill_no'] = "";

            $user['bill_month_year'] = "";

            $user['bill_amount'] = "";

            

            $return_data['data'] = $user;

            $return_data['message'] = 'Bill not found';

            $return_data['status'] = false;

        }else{

            // $receipt = MemberReceipt::where('Mem_Id', $user->id)->first();

            $BillAmt = $memberReceipts->BillAmt;

            

            $PaymentReceived = $memberReceipts->PaymentReceived;



            $received_date = Carbon::parse($memberReceipts->ReceivingDate)->format('d/m/Y');

            $amount_payable = $BillAmt - $PaymentReceived;



            $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

            // Assuming there's a method in the User model to update the transaction ID

            $user->update(['txn_id' => $txnid]);

            

            $user['pdf'] = url('public/Bills/'.$user->SC_ID.'-'.str_replace(', ', '', $memberReceipts->BillMonthYear).'.pdf');

    	    $user['txn_id'] = $txnid;

    	    $user['pay_status'] = $memberReceipts->PayStatus;

    	    $user['amount_payable'] = $amount_payable;

    	    $user['received_date'] = $received_date;    

    	    $user['bill_no'] = $memberReceipts->BillNo;

            $user['bill_month_year'] = $memberReceipts->BillMonthYear;

            $user['bill_amount'] = $BillAmt;

            

            $return_data['data'] = $user;

            $return_data['message'] = '';

            $return_data['status'] = true;

        }



        return response()->json($return_data , 200);

    }

    

    public function getOTP()

    {

        $otp = rand(100000, 999999);

        OtpModel::where('MemberId', request()->user()->MemberID)->where('Verified', 0)->delete();
 

        // // Insert the new OTP

        // $newOTP = new OtpModel();
\Log::info(request()->user()->MemberID);
      $newOTP = new OtpModel(); // 🔥 REQUIRED
$newOTP->MemberId = request()->user()->MemberID;
$newOTP->OTP = $otp;
$newOTP->save();

        

        $return_data = [

            'status' => true,

            'message' => '',

            'data' => [

                'otp' => $otp

            ]

        ];

        

        return response()->json($return_data);

    }

    

    public function getCardBalance()

    {

        $objCardClosingBalance = CardClosingBalance::where('MemberID', auth()->user()->SC_ID)->first();

        

        $amount = '0';

    	$closing_date = '';

    	if($objCardClosingBalance){

    		$amount = $objCardClosingBalance['CardBalance'];

    		$closing_date = date('d-m-Y',strtotime($objCardClosingBalance['ClosingDate']));

    	}



    	$data = array('balance' => array('balance' => $amount ), 

    	                'closing_date'=>$closing_date);

    

    	$return_data['data'] = $data;

        $return_data['message'] = '';

        $return_data['status'] = true;

        

        return response()->json($return_data);

    }



    public function getAccountSummary(Request $request)

    {

        // $request->validate([

        //     'member_id' => 'required|integer',

        // ]);



        // $memberId = $request->input('member_id');



        // // Fetch the user using the member_id

        // $user = DB::table('Members')->where('MemberID', $memberId)->first();



        $user = auth()->user();



        if (!$user) {

            return response()->json([

                'status' => false,

                'message' => 'Invalid Member ID.',

                'data' => '',

            ], 404);

        }



        // Get the account summary for the member

        $result = $this->getMemberAccountSummary($user->SC_ID);



        if ($result) {

            $totalCredit = $result['total_credit'];

            $totalDebit = $result['total_debit'];

            $billAmount = $result['bill_amount'];

            $outstandingAmount = $billAmount - $totalCredit + $totalDebit;



            $data = [

                // 'total_credit' => $totalCredit,
                'total_credit' =>$user->credit_limit,

                'total_debit' => $totalDebit,

                'bill_amt' => $billAmount,

                'outstanding_amt' => $outstandingAmount,
                
                 'credit_limit'=>$user->credit_limit,
                
                'avaiable_limit'=>$user->credit_limit-$outstandingAmount,

            ];



            return response()->json([

                'status' => true,

                'message' => '',

                'data' => $data,

            ], 200);

        }



        return response()->json([

            'status' => false,

            'message' => 'Account summary not available.',

            'data' => '',

        ], 404);

    }



    // Function to get member account summary

    private function getMemberAccountSummary($scId)

    {

        // Get the bill amount and the next billing date

        $dateResult = DB::table('memberreceipts')

            ->select(DB::raw("

                BillAmt,

                CONCAT(

                    IF(BillMonth = 12, BillYear + 1, BillYear), 

                    '-', 

                    LPAD(IF(BillMonth = 12, 1, BillMonth + 1), 2, '0'), 

                    '-01'

                ) AS bill_date

            "))

            ->where('Mem_Id', $scId)

            ->first();



        if (!$dateResult) {

            return null;

        }



        $currDate = $dateResult->bill_date;

        $billAmt = $dateResult->BillAmt;



        // Calculate total credit

        $totalCredit = DB::table('MemberAccountLedger')

            ->where('member_id', $scId)

            ->where('voucher_date', '>=', $currDate)

            ->sum('credit_amt');



        // Calculate total debit

        $totalDebit = DB::table('MemberAccountLedger')

            ->where('member_id', $scId)

            ->where('voucher_date', '>=', $currDate)

            ->sum('debit_amt');



        return [

            'total_credit' => $totalCredit ?? 0,

            'total_debit' => $totalDebit ?? 0,

            'bill_amount' => $billAmt,

        ];

    }

    

    public function getTransactions(Request $request)

    {

        $pay_mode = $request->pay_mode;

        if($pay_mode != null || $pay_mode != '') {

            $customerStatements = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')

                ->where('MemberId', auth()->user()->SC_ID)

                // ->where('PayMode', $pay_mode)

                ->orderBy('BillDate', 'DESC')

                ->orderBy('SNo', 'DESC')

                ->get();

        } else {

            $customerStatements = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')

                ->where('MemberId', auth()->user()->SC_ID)

                ->orderBy('BillDate', 'DESC')


                ->orderBy('SNo', 'DESC')

                ->get();

        }





    	$return_data['data'] = $customerStatements;

        $return_data['message'] = '';

        $return_data['status'] = true;

        

        return response()->json($return_data);

    }

// public function uploadProfile(Request $request){
//      $user = auth()->user(); // Or use MemberID from request

//     if (!$user) {
//         return response()->json([
//             'status' => false,
//             'message' => 'User not authenticated',
//         ], 401);
//     }
//  $request->validate([
//         'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
//     ]);
//       if ($request->hasFile('profile_image')) {
//         $image = $request->file('profile_image');
//         $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

//         // Store in public/uploads/profile_pictures/
//         $image->move(public_path('profile_pictures'), $imageName);

//         // Update DB
//         DB::table('memberprofile')
//             ->where('MemberID', $user->MemberID)
//             ->update([
//                 'profile_image' => $imageName,
//                 'updated_at' => now()
//             ]);

//         return response()->json([
//             'status' => true,
//             'message' => 'Profile picture updated successfully',
//             'image_url' =>  $imageName,
//         ], 200);
//     }

//     return response()->json([
//         'status' => false,
//         'message' => 'Image not uploaded',
//     ], 400);
    
// }
public function uploadProfile(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not authenticated',
        ], 401);
    }

    $request->validate([
        'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($request->hasFile('profile_image')) {

        $image = $request->file('profile_image');

        // Image name: SC_ID.jpg
        $imageName = $user->SC_ID . '.jpg';

        // Destination path
        $destinationPath = public_path('member_profile');

        // Create directory if not exists
        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0755, true);
        }

        // Move & overwrite image
        $image->move($destinationPath, $imageName);

        // Update DB
        DB::table('memberprofile')
            ->where('MemberID', $user->MemberID)
            ->update([
                'profile_image' => $imageName,
                'updated_at' => now(),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Profile picture updated successfully',
            'image_url' => url('member_profile/' . $imageName),
        ], 200);
    }

    return response()->json([
        'status' => false,
        'message' => 'Image not uploaded',
    ], 400);
}


    public function getTransactionFilter(Request $request)

    {

        $pay_mode = $request->pay_mode;

        $locations = $request->locations;

        $start_date = $request->start_date;

        $end_date = $request->end_date;



        // Initialize query with base condition (MemberId)

        $query = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')

            ->where('MemberId', auth()->user()->SC_ID);



        // Apply the PayMode filter if provided

        if ($pay_mode != null) {

            $query->where('PayMode', $pay_mode);

        }



        // Apply the Locations filter if provided

        if ($locations != null) {

            $query->whereIn('LocationName', [$locations]);

        }



        // Apply the date range filter if both start_date and end_date are provided

        if ($start_date != null && $end_date != null) {

            $query->whereBetween('BillDate', [$start_date, $end_date]);

        }



        // Order the results by BillDate and SNo

        $query->orderBy('BillDate', 'DESC')

            ->orderBy('SNo', 'DESC');



        // Execute the query and get the results

        $customerStatements = $query->get();



        // Prepare the response

        if ($customerStatements->isNotEmpty()) {

            $return_data['data'] = $customerStatements;

            $return_data['message'] = '';

            $return_data['status'] = true;

        } else {

            $return_data['data'] = [];

            $return_data['message'] = 'No Transaction found.';

            $return_data['status'] = true;

        }



        return response()->json($return_data);

    }
    
    
public function getTransactionFilterNew(Request $request)
{
    $memberId = auth()->user()->SC_ID;

    $query = CustomerStatement::select(
            'BillNo',
            'BillDate',
            'Amount',
            'LocationName',
            'PayMode',
            'Balance',
            'SNo'
        )
        ->where('MemberId', $memberId);

    // ⭐ Pay Mode Filter
    if (!empty($request->pay_mode)) {
        $query->where('PayMode', $request->pay_mode);
    }

    // ⭐ Location Filter (frontend sends single or array)
    if (!empty($request->locations)) {
        $locations = is_array($request->locations)
            ? $request->locations
            : [$request->locations];

        $query->whereIn('LocationName', $locations);
    }

    // ⭐ Date Range Filter
    if (!empty($request->start_date) && !empty($request->end_date)) {
        $query->whereBetween('BillDate', [$request->start_date, $request->end_date]);
    }

    // ⭐ Transaction Type Filter
    if (!empty($request->txn_type)) {
        $query->where('TxnType', $request->txn_type);
    }

    // ⭐ Min Amount
    if ($request->min_amount !== null) {
        $query->where('Amount', '>=', $request->min_amount);
    }

    // ⭐ Max Amount
    if ($request->max_amount !== null) {
        $query->where('Amount', '<=', $request->max_amount);
    }

    // ⭐ Search Filter (BillNo or other fields)
    if (!empty($request->search)) {
        $search = $request->search;
        $query->where(function ($q) use ($search) {
            $q->where('BillNo', 'LIKE', "%$search%")
              ->orWhere('LocationName', 'LIKE', "%$search%")
              ->orWhere('PayMode', 'LIKE', "%$search%");
        });
    }

    // ⭐ Voucher Number Filter
    if (!empty($request->voucher_no)) {
        $query->where('VoucherNo', $request->voucher_no);
    }

    // ⭐ Category Filter
    if (!empty($request->category)) {
        $query->where('Category', $request->category);
    }

    // ⭐ Sorting (dynamic from frontend)
    $sortKey = $request->sort_key ?? 'BillDate';
    $sortDir = $request->sort_dir ?? 'desc';
    $query->orderBy($sortKey, $sortDir);

    $customerStatements = $query->get();

    return response()->json([
        "status" => true,
        "message" => $customerStatements->isNotEmpty() ? "" : "No Transaction found.",
        "data" => $customerStatements
    ]);
}



    public function transactionDownload(Request $request)

    {

        // Get the request parameters

        $sc_id = auth()->user()->SC_ID;

        $locations = $request->input('locations', []);

        $pay_mode = $request->input('pay_mode');

        $start_date = $request->input('start_date');

        $end_date = $request->input('end_date');



        // // Validate required parameters

        // if (!$sc_id || !$pay_mode || !$start_date || !$end_date) {

        //     return response()->json([

        //         'message' => 'Missing required parameters.',

        //         'status' => false,

        //     ]);

        // }



        // Query the CustomerStatement table using Eloquent

        $query = CustomerStatement::where('MemberId', $sc_id);



        // Filter by locations if provided

        if (!empty($locations)) {

            $query->whereIn('LocationName', $locations);

        }



        // Filter by date range

        if ($start_date && $end_date) {

            $query->whereBetween('BillDate', [$start_date, $end_date]);

        }



        // Filter by pay mode

        if ($pay_mode) {

            $query->where('PayMode', $pay_mode);

        }



        // Order the results

        $customerStatements = $query->orderBy('BillDate', 'DESC')

            ->orderBy('SNo', 'DESC')

            ->get();



        // If there are no transactions found, return a response

        if ($customerStatements->isEmpty()) {

            return response()->json([

                'data' => [],

                'message' => 'No Transaction found.',

                'status' => false,

            ]);

        }



        // Get the user info (assuming you have a User model and relationship with CustomerStatement)

        $user = auth()->user();



        // Prepare HTML content

        $message = '<html><body><style>#tblheader td { width: 160px; text-align: center; vertical-align: middle; } #tblmemberinfo tr { height: 25px; } hr { height: 2px; background-color: #000000; border: none; } #tblcontent th { border: thin solid black; background-color: #c3c3c3; } #tblcontent td {border: thin solid black;}</style>';

        $message .= '<title>Prepaid Ledger - ' . strtoupper($pay_mode) . '</title>';

        $message .= '<table id="tblheader" cellspacing="0" cellpadding="0" width="100%" style="background-color: #ffffff; filter: alpha(opacity=40);opacity: 0.95;border:1px black solid;border-spacing:10px;">';

        $message .= '<tbody><tr><td rowspan="5" width="110"><img width="100" height="100" src="	https://www.lgc.in/wp-content/uploads/2026/01/lgclogo.png" alt="background image"/></td>';

        $message .= '<td><b>Lucknow Golf Club</b><br>GVI Club<br>1, Kalidas Marg, Lucknow - 226001</td></tr></tbody></table>';

        $message .= '<tr><td></td></tr><tr><td><p><b>Phone: 83030-92001</b></p></td></tr></tbody></table>';

        $message .= '<p style="text-align: center;"><b>PREPAID LEDGER - ' . strtoupper($pay_mode) . '</b></p><hr></hr></br>';

        $message .= '<table id="tblmemberinfo" cellspacing="0" cellpadding="0" style="border: none;"><tbody>';

        $message .= '<tr><td width="150"><b>Membership No :</b></td><td>' . $user->MemberID . '/' . $user->SC_ID . '</td></tr>';

        $message .= '<tr><td><p><b>Name :</b></p></td><td>' . $user->DisplayName . '</td></tr>';

        $message .= '<tr><td><p><b>Email :</b></p></td><td>' . $user->Email . '</td></tr>';

        $message .= '<tr><td><p><b>From :</b></p></td><td>' . $start_date . '</td></tr>';

        $message .= '<tr><td><p><b>To :</b></p></td><td>' . $end_date . '</td></tr></tbody></table></br></br>';



        // Add transaction table header

        $message .= '<table id="tblcontent" cellspacing="0"><thead><tr>';

        $message .= '<th>Txn.#</th><th>Txn.Dt</th><th>Location</th><th>PayMode</th><th>CrAmt</th><th>DrAmt</th><th>Balance</th></tr></thead><tbody>';



        // Add transaction rows

        foreach ($customerStatements as $transaction) {

            $CrAmt = $transaction->Amount >= 0 ? $transaction->Amount : 0;

            $DrAmt = $transaction->Amount < 0 ? abs($transaction->Amount) : 0;

            $message .= '<tr>';

            $message .= '<td>' . $transaction->BillNo . '</td>';

            $message .= '<td>' . $transaction->BillDate . '</td>';

            $message .= '<td>' . $transaction->LocationName . '</td>';

            $message .= '<td>' . $transaction->PayMode . '</td>';

            $message .= '<td>' . number_format($CrAmt, 2) . '</td>';

            $message .= '<td>' . number_format($DrAmt, 2) . '</td>';

            $message .= '<td>' . $transaction->Balance . '</td>';

            $message .= '</tr>';

        }



        $message .= '</tbody></table></body></html>';



        // Return the HTML response

        return response()->json([

            'data' => $message,

            'message' => '',

            'status' => true,

        ]);

    }





    

    // public function getTransactionFilter(Request $request)

    // {

    //     $pay_mode = $request->pay_mode;

    //     $locations = $request->locations;

    //     $start_date = $request->start_date;

    //     $end_date = $request->end_date;

    //     if($pay_mode != null && $locations != null && $start_date != null && $end_date != null) {

    //         $customerStatements = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')

    //             ->where('MemberId', auth()->user()->SC_ID)

    //             // ->where('PayMode', $pay_mode)

    //             ->where(function ($query) use ($locations, $start_date, $end_date) {

    //                 $query->whereIn('LocationName', $locations)

    //                     ->orWhereBetween('BillDate', [$start_date, $end_date]);

    //             })

    //             ->orderBy('BillDate', 'DESC')

    //             ->orderBy('SNo', 'DESC')

    //             ->get();

    //         $return_data['data'] = $customerStatements;

    //         $return_data['message'] = '';

    //         $return_data['status'] = true;

    //     } else {

    //         $return_data['data'] = [];

    //         $return_data['message'] = 'No Transaction found.';

    //         $return_data['status'] = true;

    //     }

    //     return response()->json($return_data);

    // }

    

    public function getDocuments()

    {

        $documents = [

            "about_us" => "https://mbclublucknow.org/mbclub/about-us/",

            "privacy_policy" => "https://mbclublucknow.org/mbclub/privacy-policy/",

            "terms_and_condition" => "https://mbclublucknow.org/mbclub/terms-and-conditions/",

            "disclaimer" => "https://mbclublucknow.org/mbclub/disclaimer/",

            "cancellation_policy" => "https://mbclublucknow.org/mbclub/cancellation-refund-policy/",

            "contact_us" => "https://mbclublucknow.org/mbclub/contact-us/"

            ];

        $return_data['data'] = $documents;

        $return_data['message'] = '';

        $return_data['status'] = true;

        return response()->json($return_data);

    }

      private function updateCardClosingBalance($memberId, $rechargeAmount, $receiptDate)
{
    \Log::info($memberId);
    $existing = DB::table('cardclosingbalance')->where('MemberID', $memberId)->first();

    if (!$existing) {
        DB::table('cardclosingbalance')->insert([
    
            'MemberID' => $memberId,
            'CardBalance' => $rechargeAmount,
            'ClosingDate' => $receiptDate,
        ]);
        Log::info("Created new cardclosingbalance for MemberID {$memberId}");
    } else {
        DB::table('cardclosingbalance')
            ->where('MemberID', $memberId)
            ->update([
                'CardBalance' => DB::raw("CardBalance + $rechargeAmount"),
                'ClosingDate' => $receiptDate,
            ]);
        Log::info("Updated CardClosingBalance for MemberID {$memberId}");
    }
}

//   public function getMenus()

//     {
        
//             $setting = AdminSetting::first();

//         $options = [

           

//             [
//                 'id'=> 1,
                
//                 'name' => 'News & Circulars',
                
//                 'subTitle'=> 'Stay updated with the latest announcements.',

//                 'icon' => 'news.png',

//                 'navigate' => 'Notification'

//             ],

//               [
//          'id'=> 2,
//                 'name' => 'Availability',
//                 'icon' => 'room.png',
//                 'subTitle'=> 'Check available rooms and venues instantly.',
//                 'navigate' => 'Rooms'
            

//             ],
         

//         ];



        

//         $return_data['data'] =$options;

//         $return_data['message'] = '';

//         $return_data['status'] = true;

//         return response()->json($return_data);

//     }

 public function getMenus(Request $request)
{
    $modules = DB::table('app_modules')
        ->where('is_active', 1)
        ->orderBy('position')
        ->get();

    $options = [];

    foreach ($modules as $module) {

        // 🔹 Special Logic: Event Single Event
        if ($module->module_key === 'event') {

            $activeEvents = Event::where('status', 'active')->get();

            if ($activeEvents->count() === 1) {
                $options[] = [
                    'id' => $module->id,
                    'name' => $module->name,
                    'subTitle' => $module->subtitle,
                    'icon' => $module->icon,
                    'navigate' => 'Event',
                    'data' => $activeEvents->first()->id,
                    'status' => 'active',
                ];
                continue;
            }
        }

        // 🔹 My Bookings dynamic tabs
        if ($module->module_key === 'my_bookings') {

            $data = ['Room','Banquet','Event','Activity','Tee','Table'];

            $options[] = [
                'id' => $module->id,
                'name' => $module->name,
                'subTitle' => $module->subtitle,
                'icon' => $module->icon,
                'navigate' => $module->navigate,
                'data' => $data,
                'status' => 'active',
            ];
            continue;
        }

        // 🔹 Default modules
        $options[] = [
            'id' => $module->id,
            'name' => $module->name,
            'subTitle' => $module->subtitle,
            'icon' => $module->icon,
            'navigate' => $module->navigate,
            'status' => 'active',
        ];
    }

    return response()->json([
        'status' => true,
        'data' => $options,
    ]);
}


public function getAffilatedClubs()
{
    $clubs = AffilatedClubs::orderBy('name', 'ASC')->get();

    $result = $clubs->map(function ($club) {

        $address = trim(implode(', ', array_filter([
            $club->address,
            $club->city,
        ])));

        return [
            'club_id' => $club->id,
            'city' => $address,
            'city_name' => $club->city,
            'name' => $club->name,
            'address' => $club->address,
            'code' => $club->code,
            'email' => $club->email,
            'website' => $club->website,

            // ❌ phones removed
            'phone_numbers' => [],
            'phone_numbers_text' => null,

            'has_contact_details' => !empty($club->email) || !empty($club->website),

            'search_index' => strtolower(trim(implode(' ', array_filter([
                $club->name,
                $club->city,
                $club->code,
                $club->address,
            ])))),
        ];
    })->values();

    $return_data['data'] = $result;
    $return_data['meta'] = [
        'total_clubs' => $result->count(),
        'cities_covered' => $result->pluck('city_name')->filter()->unique()->count(),
        'clubs_with_contact' => $result->where('has_contact_details', true)->count(),
    ];
    $return_data['message'] = '';
    $return_data['status'] = true;

    return response()->json($return_data);
}

    // public function getAffilatedClubs()

    // {
    //     $clubs = AffilatedClubs::orderBy('name', 'ASC')->get();
    //     $phonesByClub = DB::table('AffilatedClubsPhones')
    //         ->select('club_id', 'phone')
    //         ->whereNotNull('phone')
    //         ->get()
    //         ->groupBy('club_id');

    //     $result = $clubs->map(function ($club) use ($phonesByClub) {
    //         $phones = collect($phonesByClub->get($club->id, []))
    //             ->pluck('phone')
    //             ->filter()
    //             ->map(fn ($phone) => trim((string) $phone))
    //             ->values();

    //         $address = trim(implode(', ', array_filter([
    //             $club->address,
    //             $club->city,
    //         ])));

    //         return [
    //             'club_id' => $club->id,
    //             'city' => $address,
    //             'city_name' => $club->city,
    //             'name' => $club->name,
    //             'address' => $club->address,
    //             'code' => $club->code,
    //             'email' => $club->email,
    //             'website' => $club->website,
    //             'phone_numbers' => $phones,
    //             'phone_numbers_text' => $phones->implode(', '),
    //             'has_contact_details' => $phones->isNotEmpty() || !empty($club->email) || !empty($club->website),
    //             'search_index' => strtolower(trim(implode(' ', array_filter([
    //                 $club->name,
    //                 $club->city,
    //                 $club->code,
    //                 $club->address,
    //                 $phones->implode(' '),
    //             ])))),
    //         ];
    //     })->values();

    //     $return_data['data'] = $result;
    //     $return_data['meta'] = [
    //         'total_clubs' => $result->count(),
    //         'cities_covered' => $result->pluck('city_name')->filter()->unique()->count(),
    //         'clubs_with_contact' => $result->where('has_contact_details', true)->count(),
    //     ];
    //     $return_data['message'] = '';
    //     $return_data['status'] = true;

    //     return response()->json($return_data);

    // }

    

    public function getConfig()

    {

        $data = [

            "current_app_version" => "1.2.0",

            "hard_update" => true,

            "current_ios_app_version" => "1.3.0",

            "ios_hard_update" => true,

            "play_store_link" => "https://play.google.com/store/apps/details?id=com.technyk.mbclub",

            "app_store_link" => "https://apps.apple.com/app/mb-club-lucknow/id6711336083",

            "and_alert_line" => "A new update is available. Would you like to update?",

            "ios_alert_line" => "A new update is available. Would you like to update?"

        ];

        $return_data['data'] = $data;

        $return_data['message'] = '';

        $return_data['status'] = true;

        return response()->json($return_data);

    }



    public function getStatement()

    {
        $user=auth()->user()->id;
        // if($user){



            $member = Member::where("memberprofile.id",auth()->user()->id)

                            ->leftJoin('categorymaster', 'categorymaster.Catg_Code', '=', 'memberprofile.CategoryCode')->first();



            $receipt = DB::table('memberreceipts')->where('Mem_Id', auth()->user()->SC_ID)->first();
            Log::info($receipt);

            if (isset($receipt)){

                $BillAmt=$receipt->BillAmt;

                $PaymentReceived=$receipt->PaymentReceived;

                $user['bill_no'] = $receipt->BillNo;

                $user['bill_month_year'] = $receipt->BillMonthYear;

                $user['bill_amount'] = $receipt->BillAmt;



                $received_date=$receipt->ReceivingDate;

                // $date = new DateTime($received_date);

                // $received_date= $date->format('d/m/Y');

                $amount_payable=$BillAmt-$PaymentReceived;



                $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);

                // $db->UpdtTxnID($txnid,$user['SC_ID']);


                $user['pdf'] = url('public/Bills/'.auth()->user()->SC_ID.'-'.str_replace(', ', '', $receipt->BillMonthYear)).'.pdf';

                $user['txn_id'] = $txnid;

                $user['pay_status'] = $receipt->PayStatus;

                $user['amount_payable'] = $amount_payable;

                $user['received_date'] = $received_date;    

                $user['DisplayName'] = $member->DisplayName;    

                $user['MemberID'] = $member->MemberID;    

                $user['Mobile'] = $member->Mobile;    

                $user['Email'] = $member->Email;    

                $user['Status'] = $member->Status;

                $user['DOB'] = $member->DOB;    

                $user['SC_ID'] = $member->SC_ID;    

                $user['Catg_Name'] = $member->Catg_Name;

                if ($receipt->PayStatus == 'SUCCESS') {

                    $user['message'] = 'Your bill for this month is already paid.';

                }



                return response()->json($user , 200);

            } else {

                return response()->json('Invalid User' , 400);

            }



        // } else {

        //     return response()->json('Invalid User' , 400);

        // }

       

    }
 public function getteansationSummery(Request $request){
        $SC_ID = auth()->user()->SC_ID;
        $result = $this->getMemberAccountSummary($SC_ID);
        Log::info($SC_ID);
         $currDate = Carbon::now()->startOfMonth()->toDateString(); // e.g. "2025-09-01"
         
         Log::info($result);
    $baseQuery = MemberAccountLedger::
    where('member_id', auth()->user()->SC_ID)
    ->where('particulars', 'Receipt')
    ->where('voucher_date', '>=', $currDate);

// ✅ For sum
$totalBillAmt = (clone $baseQuery)->sum('credit_amt');
$debitBillAmt = (clone $baseQuery)->sum('debit_amt');
// ✅ For fetching records
$records = (clone $baseQuery)->orderBy('id', 'desc')->get();
        if($result){
            
            $memberReceipts = MemberReceipt::where('Mem_Id', auth()->user()->SC_ID)->first();
          
          
          $bill_no=$memberReceipts->BillNo;


               $pdf= url('public/Bills/'.auth()->user()->SC_ID.'-'.str_replace(', ', '', $memberReceipts->BillMonthYear)).'.pdf';
        \Log::info($pdf);
          $totalCredit = $result['total_credit'];

            $totalDebit = $result['total_debit'];

            $billAmount = $result['bill_amount'];

            $outstandingAmount = $billAmount - $totalCredit + $totalDebit;
            
            

             $bill_amount = ($memberReceipts->BillAmt - $memberReceipts->PaymentReceived) - $totalBillAmt;
             
              $user = Member::where("id",auth()->user()->id)->first();
            $data = [

                'total_credit' => $totalCredit,

                'total_debit' => $totalDebit,

                'bill_amt' => $billAmount,

                'outstanding_amt' => $outstandingAmount,
                
                'amount_payable'=>$memberReceipts->BillAmt,
                
                'bill_amount'=>$bill_amount,
                
                'bill_month_year'=>$memberReceipts->BillMonthYear,
                
                'pdf'=>$pdf,
                
                'bill_no'=>$bill_no,
                
                'Reciepts'=>$records,
                
                 
                'credit_limit'=>$user->credit_limit,
                
                'avaiable_limit'=>$user->credit_limit-$outstandingAmount,
                

            ];
        return response()->json([

                'status' => true,

                'message' => '',

                'data' => $data,

            ], 200);
    }
        
        
    }


public function getNotifications(Request $request)
{
    $perPage = $request->per_page ?? 10;

    $notifications = DB::table('notifications')
        ->where('active_status', true)
        ->orderBy('date', 'desc')
        ->paginate($perPage);

    // modify image URL
    $notifications->getCollection()->transform(function ($notification) {
        $notification->image = $notification->image
            ? url('teebooking/get-notification-image/' . $notification->image)
            : null;
        return $notification;
    });

    return response()->json($notifications);
}

    public function getNotificationById($id)
{
    $notification = DB::table('notifications')
        ->where('id', $id)
        ->where('active_status', true)
        ->first();

    if (!$notification) {
        return response()->json([
            'status' => false,
            'message' => 'Notification not found'
        ], 404);
    }

    $notification->image = $notification->image
        ? url('teebooking/get-notification-image/' . $notification->image)
        : null;

    return response()->json([
        'status' => true,
        'data' => $notification
    ]);
}

    public function change_password(Request $request)

    {

        $request->validate(

            [

                'old_password' => 'required',

                'password' => 'required',

                'conf_password' => 'required',

            ]

        );



        $old_password = $request->old_password;

        $password = $request->password;

        $conf_password = $request->conf_password;

        

        $user = auth()->user(); //Member::where("id",auth()->user()->id)->first();

        if (!$user) {

            $return_data = [

                'status' => false,

                'message' => 'Member not found',

                'data' => []

            ];

            return response()->json($return_data , 400);

        }



        if(!hash('sha256', $old_password) === $user['Password']){

    	    $return_data['status'] = false;

            $return_data['message'] = "Current Password is incorrect.";

            $return_data['data'] = '';

    	}elseif($password !== $conf_password){

    		$return_data['status'] = false;

            $return_data['message'] = "Password and confirm password should be same.";

            $return_data['data'] = '';

    	}elseif($old_password === $password){

    		$return_data['status'] = false;

            $return_data['message'] = "Old Password and New password should not be same.";

            $return_data['data'] = '';

    	}else{

    	    $user->update(['Password' =>  $password]);

    		$return_data['data'] = '';

    	    $return_data['message'] = 'Password updated Successfully';

    	    $return_data['status'] = true;

    	}

    	return response()->json($return_data , 200);

    }

    

    public function member_transactions(){

        return view('website.pages.transactions');

    }

    public function member_subscription(){

        return view('website.pages.subscription');

    }

    public function member_otp(){

        return view('website.pages.otp');

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
    
   public function deleteAccount($id = null)
{
    if (empty($id)) {
        return response()->json([
            'status' => false,
            'message' => 'ID is missing',
        ], 400);
    }

    // Here you would typically delete the account based on ID
    // Example: User::destroy($id);

    return response()->json([
        'status' => true,
        'message' => 'Account deleted successfully',
    ], 200);
}
       public static function getAccessToken()
{
    // $serviceAccountPath = env('FIREBASE_CREDENTIALS_PATH'); // load from .env
$serviceAccountPath = storage_path('app/firebase/holidayclub-service-account.json');

    if (!file_exists($serviceAccountPath)) {
        throw new \Exception("Firebase credentials file not found at: {$serviceAccountPath}");
    }

    $serviceAccountData = json_decode(file_get_contents($serviceAccountPath), true);

    if (!$serviceAccountData || !isset($serviceAccountData['client_email'], $serviceAccountData['private_key'])) {
        throw new \Exception("Invalid Firebase credentials JSON.");
    }

    // JWT Header
    $jwtHeader = rtrim(strtr(base64_encode(json_encode([
        'alg' => 'RS256',
        'typ' => 'JWT'
    ])), '+/', '-_'), '=');

    $now = time();

    // JWT Payload
    $jwtPayload = rtrim(strtr(base64_encode(json_encode([
        'iss'   => $serviceAccountData['client_email'],
        'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
        'aud'   => 'https://oauth2.googleapis.com/token',
        'exp'   => $now + 3600,
        'iat'   => $now
    ])), '+/', '-_'), '=');

    $dataToSign = $jwtHeader . '.' . $jwtPayload;

    // Sign with private key
    $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
    openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
    $jwtSignature = rtrim(strtr(base64_encode($jwtSignature), '+/', '-_'), '=');

    $jwt = $dataToSign . '.' . $jwtSignature;

    // Exchange JWT for access token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion'  => $jwt
    ]));

    $response = curl_exec($ch);
    curl_close($ch);

    $response = json_decode($response, true);

    if (isset($response['access_token'])) {
        return $response['access_token'];
    }

    throw new \Exception("Failed to fetch Firebase access token: " . json_encode($response));
}

   private function sendFCMMessage($notification, $fcmTokens) {
        $url = 'https://fcm.googleapis.com/v1/projects/gvi-club/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => 'https://gvicc.in/wp-content/uploads/2025/11/gviclogo.png'
                ],
                "data" => [
                    "type" => "Notification"
                ]
            ]
        ];

        $encodedData = json_encode($data);
    
        $headers = [
            'Authorization: Bearer ' . $serverKey,
            'Content-Type: application/json; UTF-8',
        ];
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        return $response;
    }




public function createPayOrder(Request $request)
{
    $member = auth()->user();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'User not found'
        ]);
    }

    $amount = (float) $request->amount;

    try {
        $payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
            $member,
            $amount,
            \App\Support\Payments\PaymentModule::CARD_RECHARGE,
            null,
            [
                'type' => 'Card Recharge',
                'prefix' => 'MCR',
            ]
        );

        try {
            DB::table('CardRecharge')->updateOrInsert(
                ['TxnRefrenceNo' => $payment['merchant_order_id']],
                [
                    'Card_ID' => $member->SC_ID,
                    'RechargeAmt' => $amount,
                    'RechargeDate' => now(),
                    'PayStatus' => 'Pending',
                    'BankRefrenceNo' => '',
                    'TransactionID' => $payment['merchant_order_id'],
                    'ImportStatus' => strtolower((string) data_get($payment, 'gateway.environment')) === 'live' ? 0 : 1,
                    'PaymentResponse' => '',
                    'OrderResponse' => json_encode($payment['checkout'] ?? []),
                    'TransactionType' => 'Card Recharge',
                    'PayMode' => 'mobile',
                    'WebhookResponse' => '',
                ]
            );
        } catch (\Throwable $syncException) {
            \Log::warning('Card recharge shadow write failed', [
                'reference' => $payment['merchant_order_id'],
                'error' => $syncException->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $payment['order_id'],
                'amount' => $amount,
                'merchant_order_id' => $payment['merchant_order_id'],
                'status_reference' => $payment['status_reference'] ?? $payment['merchant_order_id'],
                'status_endpoint' => $payment['status_endpoint'] ?? null,
                'gateway' => $payment['gateway'] ?? null,
                'checkout' => $payment['checkout'] ?? null,
                'payment_url' => $payment['payment_url'] ?? null,
                'access_key' => $payment['access_key'] ?? null,
                'razorpayKey' => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
                'end_point' => 'member/card_recharge_response'
            ]
        ]);

    } catch (\Exception $e) {

        // \Log::error('Card recharge order error', [
        //     'error' => $e->getMessage()
        // ]);
\Log::error('Card recharge order error', [
    'message' => $e->getMessage(),
    'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
]);
        return response()->json([
            'status' => false,
            'message' => 'Unable to initiate payment'
        ]);
    }
}

public function processPayment(Request $request, FCMService $fcm)
{
    try {
        $member = auth()->user();
        $reference = $request->merchant_order_id
            ?? $request->status_reference
            ?? $request->transaction_id
            ?? $request->gateway_order_id
            ?? $request->razorpay_order_id
            ?? $request->order_id;

        $transaction = DB::table('transactions')
            ->where(function ($query) use ($reference) {
                $query->where('order_id', $reference)
                    ->orWhere('transID', $reference)
                    ->orWhere('gateway_order_id', $reference);
            })
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            throw new \Exception('Transaction missing');
        }

        $isCentralized = !empty($transaction->gateway_slug) || !empty($transaction->payment_status_code);

        if ($isCentralized) {
            $payload = $request->all();
            $hasVerificationPayload = $request->filled('razorpay_payment_id')
                || $request->filled('gateway_transaction_id')
                || $request->filled('payment_id');

            if (
                !$transaction->payment_status_code
                || !\App\Support\Payments\PaymentStatus::isSuccessful($transaction->payment_status_code)
            ) {
                if ($hasVerificationPayload) {
                    $result = app(\App\Services\Payments\PaymentTransactionService::class)
                        ->verify($member, $payload);
                } else {
                    $result = [
                        'success' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0,
                        'data' => [
                            'MemberName' => $member->DisplayName ?? '',
                            'MemberID' => $member->MemberID ?? '',
                            'MemberSCID' => $member->SC_ID ?? '',
                            'paid_amount' => (float) $transaction->amount,
                            'reference_number' => $transaction->gateway_transaction_id
                                ?? $transaction->bank_refrance_no
                                ?? $transaction->transID,
                            'orderId' => $transaction->gateway_order_id
                                ?? $transaction->transID
                                ?? $transaction->order_id,
                            'Status' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0 ? 'Success' : 'Failed',
                        ],
                    ];
                }
            } else {
                $result = [
                    'success' => true,
                    'data' => [
                        'MemberName' => $member->DisplayName ?? '',
                        'MemberID' => $member->MemberID ?? '',
                        'MemberSCID' => $member->SC_ID ?? '',
                        'paid_amount' => (float) $transaction->amount,
                        'reference_number' => $transaction->gateway_transaction_id
                            ?? $transaction->bank_refrance_no
                            ?? $transaction->transID,
                        'orderId' => $transaction->gateway_order_id
                            ?? $transaction->transID
                            ?? $transaction->order_id,
                        'Status' => 'Success',
                    ],
                ];
            }
        } else {
            $result = \App\Helpers\PaymentHelper::verifyPayment($request);
        }

        if (!$isCentralized) {
            DB::table('CardRecharge')
                ->where('TxnRefrenceNo', $request->razorpay_order_id)
                ->update([
                    'PayStatus' => $result['success'] ? 'Success' : 'Failed',
                    'BankRefrenceNo' => $request->razorpay_payment_id ?? '',
                    'PaymentResponse' => json_encode($request->all())
                ]);

            if ($result['success']) {
                $this->updateCardClosingBalance(
                    $member->SC_ID,
                    $transaction->amount,
                    now()
                );
            }
        }

       
       if ($member->device_id) {
            $fcm->sendNotification(
                $member->device_id,
                'Card Recharge',
                 $result['success']
                ? 'Your card is recharged by Rs. ' . number_format($transaction->amount, 2)
                : 'Your card recharge payment failed.'
                
            );
        }

        return response()->json($result);

    } catch (\Exception $e) {
        \Log::error('Card recharge verify error', [
            'error' => $e->getMessage(),
              'file' => $e->getFile(),
    'line' => $e->getLine(),
    'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment processing failed'
        ]);
    }
}


// public function createPayOrder(Request $request)
// {
//     $member = auth()->user();

//     if (!$member) {
//         return response()->json([
//             'status' => false,
//             'message' => 'User not found'
//         ]);
//     }

//     $amount = (float) $request->amount;

//     DB::beginTransaction();

//     try {

//         // 1️⃣ Create Razorpay order using helper
//         $order = \App\Helpers\PaymentHelper::createOrder(
//             $member,
//             $amount,
//             'Card Recharge',
//             0, // no module id needed,
//             'MCR'
//         );

//         // 2️⃣ Insert CardRecharge record
//         // DB::table('CardRecharge')->insert([
//         //     'Card_ID' => $member->SC_ID,
//         //     'RechargeAmt' => $amount,
//         //     'RechargeDate' => now(),
//         //     'PayStatus' => 'Pending',
//         //     'TxnRefrenceNo' => $order['order_id'],
//         //     'BankRefrenceNo' => '',
//         //     'TransactionID' => $order['order_id'],
//         //     'ImportStatus' => 0,
//         //     'PaymentResponse' => '',
//         //     'OrderResponse' => '',
//         //     'TransactionType' => 'Card Recharge',
//         //     'PayMode' => 'mobile',
//         //     'WebhookResponse' => ''
//         // ]);

//         DB::commit();

//         return response()->json([
//             'status' => true,
//             'data' => [
//                 'order_id' => $order['order_id'],
//                 'amount' => $amount,
//                 'payment_url' => $order['redirect_url'],
//                 'end_point' => 'member/card_recharge_response'
//             ]
//         ]);

//     } catch (\Exception $e) {

//         DB::rollBack();

//         \Log::error('Card recharge order error', [
//             'error' => $e->getMessage()
//         ]);

//         return response()->json([
//             'status' => false,
//             'message' => 'Unable to initiate payment'
//         ]);
//     }
// }

// public function processPayment(Request $request, FCMService $fcm)
// {
//     DB::beginTransaction();

//     try {

//         // 1️⃣ Verify payment via helper
//         $result = \App\Helpers\PaymentHelper::verifyPayment($request);

//         $transaction = DB::table('transactions')
//             ->where('transID', $request->razorpay_order_id)
//             ->first();

//         if (!$transaction) {
//             throw new \Exception('Transaction missing');
//         }

//         $member = auth()->user();

//         // 2️⃣ Update CardRecharge table
//         // DB::table('CardRecharge')
//         //     ->where('TxnRefrenceNo', $request->razorpay_order_id)
//         //     ->update([
//         //         'PayStatus' => $result['success'] ? 'Success' : 'Failed',
//         //         'BankRefrenceNo' => $request->razorpay_payment_id ?? '',
//         //         'PaymentResponse' => json_encode($request->all())
//         //     ]);

//         // 3️⃣ If paid → update card balance
//         if ($result['success']) {

//             $this->updateCardClosingBalance(
//                 $member->SC_ID,
//                 $transaction->amount,
//                 now()
//             );
//         }

       
//       if ($member->device_id) {
//             $fcm->sendNotification(
//                 $member->device_id,
//                 'Card Recharge',
//                  $result['success']
//                 ? 'Your card is recharged by Rs. ' . number_format($transaction->amount, 2)
//                 : 'Your card recharge payment failed.'
                
//             );
//         }

//         DB::commit();

//         return response()->json($result);

//     } catch (\Exception $e) {

//         DB::rollBack();

//         \Log::error('Card recharge verify error', [
//             'error' => $e->getMessage()
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Payment processing failed'
//         ]);
//     }
// }



public function createBillPayOrder(Request $request)
{
    $member = auth()->user();

    if (!$member) {
        return response()->json([
            'status' => false,
            'message' => 'Member not found'
        ]);
    }

    $amount = (float) $request->amount;

    try {

        // 1️⃣ Check receipt
        $receipt = DB::table('memberreceipts')
            // ->where('Mem_Id', 'HL-0001')
                 ->where('Mem_Id', $member->SC_ID)
            ->first();

        if (!$receipt) {
            return response()->json([
                'status' => false,
                'message' => 'No receipt found'
            ], 404);
        }

        $payment = app(\App\Services\Payments\PaymentTransactionService::class)->initiate(
            $member,
            $amount,
            \App\Support\Payments\PaymentModule::BILL_PAYMENT,
            null,
            [
                'type' => 'Bill Payment',
                'prefix' => 'MMR',
            ]
        );

        // 3️⃣ Update MemberReceipts
        try {
            DB::table('memberreceipts')
                ->where('Mem_Id', $member->SC_ID)
                ->whereIn('PayStatus', ['pending','FAILED','FAILURE','Failed'])
                ->update([
                    'TxnRefrenceNo' => $payment['merchant_order_id'],
                    'TransactionID' => $payment['merchant_order_id'],
                    'BankRefrenceNo' => '',
                    'AdditionalAmt' => 0,
                    'PaidFrom' => 'Mobile',
                    'PaymentResponse' => '',
                    'ReceivingDate' => now()
                ]);
        } catch (\Throwable $syncException) {
            \Log::warning('Bill payment shadow write failed', [
                'reference' => $payment['merchant_order_id'],
                'error' => $syncException->getMessage(),
            ]);
        }

        return response()->json([
            'status' => true,
            'data' => [
                'order_id' => $payment['order_id'],
                'amount' => $amount,
                'merchant_order_id' => $payment['merchant_order_id'],
                'status_reference' => $payment['status_reference'] ?? $payment['merchant_order_id'],
                'status_endpoint' => $payment['status_endpoint'] ?? null,
                'gateway' => $payment['gateway'] ?? null,
                'checkout' => $payment['checkout'] ?? null,
                'payment_url' => $payment['payment_url'] ?? null,
                'access_key' => $payment['access_key'] ?? null,
                'razorpayKey' => $payment['razorpayKey'] ?? data_get($payment, 'checkout.key'),
                'end_point' => 'member/payment_response'
            ]
        ]);

    } catch (\Exception $e) {

        \Log::error('Bill payment order error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'status' => false,
            'message' => 'Unable to initiate payment'
        ]);
    }
}

//   public function invoicePaymentResponse(Request $request)
// {
//     $request->validate([
//         'razorpay_order_id'   => 'required|string',
//         'razorpay_payment_id'=> 'nullable|string',
//         'razorpay_response'  => 'required|string',
//     ]);

//     $member = auth()->user();

//     // 1️⃣ FIND TRANSACTION
//     $transaction = DB::table('transactions')
//         ->where('transID', $request->razorpay_order_id)
//         ->latest()
//         ->first();

//     if (!$transaction) {
//         return response()->json([
//             'success' => false,
//             'message' => 'Transaction not found',
//         ], 404);
//     }

//     // 2️⃣ VERIFY PAYMENT FROM RAZORPAY (SOURCE OF TRUTH)
//     $isPaid = $this->verifyPaymentFromRazorpay(
//         $request->razorpay_payment_id,
//         $request->razorpay_order_id
//     );

//     $paymentStatus = $isPaid ? 'Paid' : 'Failed';

//     // 3️⃣ BANK RESPONSE (STORE EVERYTHING)
//     $bankResponse = json_encode([
//         'razorpay_response'    => $request->razorpay_response,
//         'razorpay_payment_id' => $request->razorpay_payment_id,
//         'razorpay_order_id'   => $request->razorpay_order_id,
//         'verified'             => $isPaid,
//     ]);

//     try {

//         // 4️⃣ UPDATE MEMBER RECEIPTS
//         $memberReceipt = DB::table('MemberReceipts')
//             ->where('Mem_Id', $member->SC_ID)
//             ->first();

//         if ($memberReceipt && $isPaid) {
//             DB::table('MemberReceipts')
//                 ->where('Mem_Id', $member->SC_ID)
//                 ->update([
//                     'PayStatus'        => 'Success',
//                     'BankRefrenceNo'   => $request->razorpay_payment_id,
//                     'PaymentResponse' => $bankResponse,
//                     'PaymentReceived' => ((float)$memberReceipt->PaymentReceived + (float)$transaction->amount),
//                 ]);
//         }

//         // 5️⃣ UPDATE TRANSACTIONS TABLE
//         DB::table('transactions')
//             ->where('id', $transaction->id)
//             ->update([
//                 'payment_status'   => $paymentStatus,
//                 'bank_refrance_no' => $isPaid ? $request->razorpay_payment_id : null,
//                 'bank_response'    => $bankResponse,
//                 'transaction_date' => now(),
//                 'transID'          => $request->razorpay_payment_id ?? null,
//             ]);

//         // 6️⃣ SEND FCM NOTIFICATION
//         $notification = [
//             'title' => 'Bill Payment',
//             'short_descriptions' => $isPaid
//                 ? 'Your bill payment is done successfully.'
//                 : 'Your bill payment failed or is pending verification.',
//         ];

//         $this->sendFCMMessage($notification, $member->device_id);

//         // 7️⃣ RESPONSE
//         return response()->json([
//             'success' => $isPaid,
//             'message' => $isPaid ? 'Payment Successful!' : 'Bill Payment Failed!',
//             'data' => [
//                 'MemberID'        => $member->MemberID,
//                 'MemberName'      => $member->DisplayName,
//                 'MemberSCID'      => $member->SC_ID,
//                 'TransactionID'   => $request->razorpay_payment_id ?? 'N/A',
//                 'Status'          => $isPaid ? 'Success' : 'Failed',
//                 'paid_amount'     => $transaction->amount,
//                 'reference_number'=> $request->razorpay_payment_id ?? 'N/A',
//             ]
//         ], 200);

//     } catch (\Exception $e) {

//         \Log::error('Invoice payment failed', [
//             'error' => $e->getMessage(),
//             'order_id' => $request->razorpay_order_id
//         ]);

//         return response()->json([
//             'success' => false,
//             'message' => 'Payment processing failed',
//         ], 500);
//     }
// }

public function invoicePaymentResponse(Request $request, FCMService $fcm)
{
    try {
        $member = auth()->user();
        $reference = $request->merchant_order_id
            ?? $request->status_reference
            ?? $request->transaction_id
            ?? $request->gateway_order_id
            ?? $request->razorpay_order_id
            ?? $request->order_id;

        $transaction = DB::table('transactions')
            ->where(function ($query) use ($reference) {
                $query->where('order_id', $reference)
                    ->orWhere('transID', $reference)
                    ->orWhere('gateway_order_id', $reference);
            })
            ->lockForUpdate()
            ->first();

        if (!$transaction) {
            throw new \Exception('Transaction missing');
        }

        $isCentralized = !empty($transaction->gateway_slug) || !empty($transaction->payment_status_code);

        if ($isCentralized) {
            $hasVerificationPayload = $request->filled('razorpay_payment_id')
                || $request->filled('gateway_transaction_id')
                || $request->filled('payment_id');

            if (
                !$transaction->payment_status_code
                || !\App\Support\Payments\PaymentStatus::isSuccessful($transaction->payment_status_code)
            ) {
                $result = $hasVerificationPayload
                    ? app(\App\Services\Payments\PaymentTransactionService::class)->verify($member, $request->all())
                    : [
                        'success' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0,
                        'data' => [
                            'MemberName' => $member->DisplayName ?? '',
                            'MemberID' => $member->MemberID ?? '',
                            'MemberSCID' => $member->SC_ID ?? '',
                            'paid_amount' => (float) $transaction->amount,
                            'reference_number' => $transaction->gateway_transaction_id
                                ?? $transaction->bank_refrance_no
                                ?? $transaction->transID,
                            'orderId' => $transaction->gateway_order_id
                                ?? $transaction->transID
                                ?? $transaction->order_id,
                            'Status' => strcasecmp((string) $transaction->payment_status, 'Paid') === 0 ? 'Success' : 'Failed',
                        ],
                    ];
            } else {
                $result = [
                    'success' => true,
                    'data' => [
                        'MemberName' => $member->DisplayName ?? '',
                        'MemberID' => $member->MemberID ?? '',
                        'MemberSCID' => $member->SC_ID ?? '',
                        'paid_amount' => (float) $transaction->amount,
                        'reference_number' => $transaction->gateway_transaction_id
                            ?? $transaction->bank_refrance_no
                            ?? $transaction->transID,
                        'orderId' => $transaction->gateway_order_id
                            ?? $transaction->transID
                            ?? $transaction->order_id,
                        'Status' => 'Success',
                    ],
                ];
            }
        } else {
            $result = \App\Helpers\PaymentHelper::verifyPayment($request);
        }

        if (!$isCentralized) {
            $receipt = DB::table('memberreceipts')
                ->where('Mem_Id', $member->SC_ID)
                ->first();

            if ($receipt) {
                DB::table('memberreceipts')
                    ->where('Mem_Id', $member->SC_ID)
                    ->update([
                        'PayStatus' => $result['success'] ? 'Success' : 'Failed',
                        'BankRefrenceNo' => $request->razorpay_payment_id ?? '',
                        'PaymentResponse' => json_encode($request->all()),
                        'PaymentReceived' => $result['success']
                            ? ((float)$receipt->PaymentReceived + (float)$transaction->amount)
                            : $receipt->PaymentReceived
                    ]);
            }
        }

        // 3️⃣ Send Notification
        
          if ($member->device_id) {
            $fcm->sendNotification(
                $member->device_id,
                'Bill Payment',
                 $result['success']
                ? 'Your bill payment is done successfully.'
                : 'Your bill payment failed.'
                
            );
        }
       

        return response()->json($result);

    } catch (\Exception $e) {
        \Log::error('Invoice payment error', [
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Payment processing failed'
        ]);
    }
}

public function sendFeedbackOnMail(Request $request)
{
    // Validate request
    $validator = Validator::make($request->all(), [
        'name'    => 'required|string|max:100',
        'email'   => 'required|email|max:150',
        'phone'   => 'required|string|max:20',
        'subject' => 'required|string|max:200',
        'message' => 'required|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors'  => $validator->errors()
        ], 422);
    }

    // Get validated values
    $data = $validator->validated();

    // Prepare email data for the Blade view
    $mailData = [
        'name'    => $data['name'],
        'email'   => $data['email'],
        'phone'   => $data['phone'],
        'subject' => $data['subject'],
        'user_message' => $data['message'],

    ];

    try {

        Mail::send('emails.feedback', $mailData, function ($message) use ($data) {
            $message->to('rajrishisharma12125@gmail.com')
                    ->subject('New App Feedback: ' . $data['subject'])
                    ->from($data['email'], $data['name']); // Optional
        });

        return response()->json([
            'success' => true,
            'message' => 'Feedback sent successfully'
        ]);

    } catch (\Exception $e) {

        \Log::error("Mail Error: " . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Could not send mail',
            'error' => $e->getMessage()
        ], 500);
    }
}
public function clubInfo()
{
    $data = DB::table('club_info')
        ->where('is_active', 1)
        ->get()
        ->pluck('info_value', 'info_key');

    return response()->json([
        'success' => true,
        'data' => $data
    ]);
}
public function verifyMember($id)
{
    if (empty($id)) {
        return response()->json([
            'success' => false,
            'message' => 'Member id is required'
        ], 400);
    }

    $member = Member::select('Mobile', 'MemberID', 'SC_ID','role','id','DisplayName')->where('MemberID', $id)->first();

    if (!$member) {
        return response()->json([
            'success' => false,
            'message' => 'Member not found'
        ], 404);
    }

    \Log::info($member);

    return response()->json([
        'success' => true,
        'message' => 'Member found',
        'data' => $member
    ], 200);
}

}



?>
