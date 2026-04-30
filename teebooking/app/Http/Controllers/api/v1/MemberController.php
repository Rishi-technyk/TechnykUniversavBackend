<?php
namespace App\Http\Controllers\api\v1;

use App\CPU\Helpers;
use App\Http\Controllers\Controller;
use App\Models\AffilatedClubs;
use App\Models\AdminSetting;
use App\Models\CardClosingBalance;
use App\Models\CardRecharge;
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
use Razorpay\Api\Api;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MemberController extends Controller
{
   public function member_profile_get(){
     
       $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        return response()->json(array('status'=> true,'data'=>$member) , 200);
    }
    
    public function getMemberReceipts()
    {
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
            
            $user['pdf'] = url('storage/app/public/Bills/'.$user->SC_ID.'-'.str_replace(', ', '', $memberReceipts->BillMonthYear).'.pdf');
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
        OtpModel::where('MemberId', auth()->user()->MemberID)->where('Verified', 0)->delete();
        
        // Insert the new OTP
        $newOTP = new OtpModel();
        $newOTP->MemberId = auth()->user()->MemberID;
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

    	$data = array('balance' => $amount, 
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
                'total_credit' => $totalCredit,
                'total_debit' => $totalDebit,
                'bill_amt' => $billAmount,
                'outstanding_amt' => $outstandingAmount,
                // 'outstanding_amt'=>0,
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
        ], 400);
    }

    
    public function getTransactions(Request $request)
    {
        $pay_mode = $request->pay_mode;
        if($pay_mode != null || $pay_mode != '') {
            $customerStatements = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')
                ->where('MemberId', auth()->user()->SC_ID)
                ->where('PayMode', $pay_mode)
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
            $query->whereIn('LocationName', $locations);
            
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

    public function transactionDownload(Request $request)
    {
        // Get the request parameters
        $sc_id = auth()->user()->SC_ID;
        $locations = $request->input('locations', []);
        $pay_mode = $request->input('pay_mode');
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');

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
        $message .= '<tbody><tr><td rowspan="5" width="110"><img width="100" height="100" src="https://aepta.in/wp-content/uploads/2023/08/aptalogo.png" alt="background image"/></td>';
        $message .= '<td><p><b>AEPTA,</b></p></td><td></td></tr><tr><td><p><b>H5V5+GCP, Delhi Cantonment,</b></p></td><td></td></tr>';
        $message .= '<tr><td><p><b>New Delhi, Delhi 110010</b></p></td></tr><tr><td><p><b>Phone: 011-25693830</b></p></td></tr></tbody></table>';
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
    
    public function getDocuments()
    {
        $documents = [
            "about_us" => "https://aepta.in/",
            "privacy_policy" => "https://aepta.in/privacy-policy/",
            "terms_and_condition" => "https://aepta.in/terms-and-conditons/",
            "disclaimer" => "https://aepta.in/disclaimer/",
            "cancellation_policy" => "https://aepta.in/cancellation-refund-policy/",
            "contact_us" => "https://aepta.in/contact-us/"
            ];
        $return_data['data'] = $documents;
        $return_data['message'] = '';
        $return_data['status'] = true;
        return response()->json($return_data);
    }
    public function getMenus(Request $request)
{
    $isNew = filter_var(
        $request->query('isNew', false),
        FILTER_VALIDATE_BOOLEAN
    );

    if ($isNew) {

        $setting = AdminSetting::first();

        $options = [];

        // ----------------------
        // NEWS
        // ----------------------
        $options[] = [
            'id' => 1,
            'name' => 'News & Circulars',
            'subTitle' => 'Stay updated with the latest announcements.',
            'icon' => 'news.png',
            'navigate' => 'Notification',
            'status' => 'active',
        ];

        // ----------------------
        // ROOM BOOKING
        // ----------------------
        if ($setting->room_booking_module === 'Active') {
            $options[] = [
                'id' => 2,
                'name' => 'Rooms Booking',
                'subTitle' => 'Reserve your personal space for stay',
                'icon' => 'room.png',
                'navigate' => 'BookRoom',
                'status' => 'active',
            ];
        }

        // ----------------------
        // BANQUET BOOKING
        // ----------------------
        if ($setting->banquest_booking_module === 'Active') {
            $options[] = [
                'id' => 3,
                'name' => 'Banquet Booking',
                'subTitle' => 'Book banquet halls for events.',
                'icon' => '4365782.png',
                'navigate' => 'BookBanquet',
                'status' => 'active',
            ];
        }

        // ----------------------
        // MY BOOKINGS
        // ----------------------
        if (
            $setting->banquest_booking_module === 'Active' ||
            $setting->room_booking_module === 'Active'
        ) {
            $data = [];

            if ($setting->banquest_booking_module === 'Active') {
                $data[] = 'Banquet';
            }

            if ($setting->room_booking_module === 'Active') {
                $data[] = 'Room';
            }

            $options[] = [
                'id' => 4,
                'name' => 'My Bookings',
                'subTitle' => 'View your all bookings',
                'icon' => '9383334.webp',
                'navigate' => 'MyBookings',
                'data' => $data,
                'status' => 'active',
            ];
        }
if ($setting->kot_module === 'Active') {
            $options[] = [
                'id' => 5,
                'name' => 'Food Order',
                'subTitle' => 'Delicious meals delivered to your door.',
                'icon' => 'food.png',
                'navigate' => 'FoodOrder',
                'status' => 'active',
            ];
        }
        return response()->json([
            'status' => true,
            'settings' => $setting,
            'data' => $options,
        ]);
    }

    // ==============================
    // OLD (NON-NEW) MENU
    // ==============================
    return response()->json([
        'status' => true,
        'data' => [
            'currentOptions' => [
                ['name' => 'Invoice', 'icon' => 'Statement.svg', 'navigate' => 'Invoice'],
                ['name' => 'Recharge', 'icon' => 'Recharge.svg', 'navigate' => 'Recharge'],
                ['name' => 'OTP', 'icon' => 'Otp.svg', 'navigate' => 'OTP'],
                ['name' => 'Transactions', 'icon' => 'transaction.svg', 'navigate' => 'Transactions'],
                ['name' => 'News & Circulars', 'icon' => 'news.svg', 'navigate' => 'Notification'],
                ['name' => 'Affilated Clubs', 'icon' => 'AffilatedClubs.svg', 'navigate' => 'AffilatedClub'],
            ],
            'upcomingOptions' => [
                ['name' => 'Swimming Pool', 'icon' => 'swimmingpool.svg'],
                ['name' => 'Feedback & FAQ', 'icon' => 'faq.svg'],
                ['name' => 'Venue', 'icon' => 'Banquet.svg'],
                ['name' => 'Events', 'icon' => 'Events.svg'],
                ['name' => 'Gym', 'icon' => 'gym.svg'],
                ['name' => 'Tennis Court', 'icon' => 'tennis.svg'],
            ],
        ],
    ]);
}
    
//     public function getMenus(Request $request)
//     {
//       $isNew = $request->query('isNew');
//       if($isNew){
//             $setting = AdminSetting::first();

//     $options = [];

//     // ----------------------
//     // ALWAYS ENABLED OPTION
//     // ----------------------
//     $options[] = [
//         'id' => 1,
//         'name' => 'News & Circulars',
//         'subTitle' => 'Stay updated with the latest announcements.',
//         'icon' => 'news.png',
//         'navigate' => 'Notification',
//         'status' => 'active',
//     ];

//     // ----------------------
//     // ROOM BOOKING MODULE
//     // ----------------------
//     if ($setting->room_booking_module === 'Active') {
//         $options[] = [
//             'id' => 2,
//             'name' => 'Rooms Booking',
//             'subTitle' => 'Reserve your personal space for stay',
//             'icon' => 'room.png',
//             'navigate' => 'BookRoom',
//             'status' => 'active',
//         ];
//     } 
//     // else {
//     //     $options[] = [
//     //         'id' => 2,
//     //         'name' => 'Availability',
//     //         'subTitle' => 'Check available rooms and venues instantly.',
//     //         'icon' => 'room.png',
//     //         'navigate' => null,
//     //         'status' => 'inactive',
//     //     ];
//     // }

//     // ----------------------
//     // BANQUET BOOKING MODULE
//     // ----------------------
//     if ($setting->banquest_booking_module === 'Active') {
//         $options[] = [
//             'id' => 3,
//             'name' => 'Banquet Booking',
//             'subTitle' => 'Book banquet halls for events.',
//             'icon' => '4365782.png',
//             'navigate' => 'BookBanquet',
//             'status' => 'active',
//         ];
//     } 
    
//   if ($setting->banquest_booking_module === 'Active' || $setting->room_booking_module === 'Active') {

//     $data = [];

//     if ($setting->banquest_booking_module === 'Active') {
//         $data[] = 'Banquet';
//     }

//     if ($setting->room_booking_module === 'Active') {
//         $data[] = 'Room';
//     }

//     $options[] = [
//         'id' => 4,
//         'name' => 'My Bookings',
//         'subTitle' => 'View your all bookings',
//         'icon' => '9383334.webp',
//         'navigate' => 'MyBookings',
//         'data' => $data,   // <-- final result
//         'status' => 'active',
//     ];
// }

// // $options[] = [
// //       'id'=> 6,
// //       'name'=> 'Feedback',
// //       'subTitle'=> 'Share your experience, suggestions, or concerns.',
// //       'icon'=> 'feedback.png',
// //       'navigate'=> 'FeedBack',
      
// //     ];
//     // else {
//     //     $options[] = [
//     //         'id' => 3,
//     //         'name' => 'Banquet Booking',
//     //         'subTitle' => 'Book banquet halls for events.',
//     //         'icon' => 'banquet.png',
//     //         'navigate' => null,
//     //         'status' => 'inactive',
//     //     ];
//     // }

//     return response()->json([
//         'status' => true,
//         'settings' => $setting,
//         'data' => $options,
//     ]);
//       }else{
//         $options = [
//             [
//                 'name' => 'Invoice',
//                 'icon' => 'Statement.svg',
//                 'navigate' => 'Invoice'
//             ],
//             [
//                 'name' => 'Recharge',
//                 'icon' => 'Recharge.svg',
//                 'navigate' => 'Recharge'
//             ],
//             [
//                 'name' => 'OTP',
//                 'icon' => 'Otp.svg',
//                 'navigate' => 'OTP'
//             ],
//             [
//                 'name' => 'Transactions',
//                 'icon' => 'transaction.svg',
//                 'navigate' => 'Transactions'
//             ],
//             [
//                 'name' => 'News & Circulars',
//                 'icon' => 'news.svg',
//                 'navigate' => 'Notification'
//             ],
//             [
//                 'name' => 'Affilated Clubs',
//                 'icon' => 'AffilatedClubs.svg',
//                 'navigate' => 'AffilatedClub'
//             ]
//         ];

//         // Define the upcoming features
//         $upcomingOptions = [
//             [
//                 'name' => 'Swimming Pool',
//                 'icon' => 'swimmingpool.svg',
//                 'navigate' => ''
//             ],
//             [
//                 'name' => 'Feedback & FAQ',
//                 'icon' => 'faq.svg',
//                 'navigate' => ''
//             ],
//             [
//                 'name' => 'Venue',
//                 'icon' => 'Banquet.svg',
//                 'navigate' => ''
//             ],
//             [
//                 'name' => 'Events',
//                 'icon' => 'Events.svg',
//                 'navigate' => ''
//             ],
//             [
//                 'name' => 'Gym',
//                 'icon' => 'gym.svg',
//                 'navigate' => ''
//             ],
//             [
//                 'name' => 'Tennis Court',
//                 'icon' => 'tennis.svg',
//                 'navigate' => ''
//             ]
//         ];

        
//         $return_data['data'] = [
//             'currentOptions' => $options,
//             'upcomingOptions' => $upcomingOptions
//         ];
//         $return_data['message'] = '';
//         $return_data['status'] = true;
//         return response()->json($return_data);
//       }
//     }
   
    public function getAffilatedClubs()
    {
        $clubs = AffilatedClubs::with('phones') // Eager load phones relationship
            ->orderBy('name', 'ASC')           // Order by club name
            ->get();   
        $result = $clubs->map(function($club) {
            return [
                'club_id'       => $club->id,
                'city'          => $club->city,
                'name'          => $club->name,
                'address'       => $club->address,
                'code'          => $club->code,
                'email'         => $club->email,
                'website'       => $club->website,
                // Concatenate phone numbers from the related phones model
                'phone_numbers' => $club->phones->pluck('phone')->implode(', '),
            ];
        });
        $return_data['data'] = $result;
        $return_data['message'] = '';
        $return_data['status'] = true;
        return response()->json($return_data);
    }
    
    public function getConfig()
    {
        $data = [
            "current_app_version" => "1.7.2",
            "hard_update" => true,
            "current_ios_app_version" => "3.5.4",
            "ios_hard_update" => true,
            "play_store_link" => "https://play.google.com/store/apps/details?id=com.technyk.aepta",
            "app_store_link" => "https://apps.apple.com/in/app/club26/id6478484971",
            "and_alert_line" => "A new update is available. Would you like to update?",
            "ios_alert_line" => "A new update is available. Would you like to update?"
        ];
        $return_data['data'] = $data;
        $return_data['message'] = '';
        $return_data['status'] = true;
        return response()->json($return_data);
    }

    // public function getStatement()
    // {
    //     if(auth()->user()){

    //         $member = Member::where("memberprofile.id",auth()->user()->id)
    //                         ->leftJoin('categorymaster', 'categorymaster.Catg_Code', '=', 'memberprofile.CategoryCode')->first();

    //         $receipt = DB::table('MemberReceipts')->where('Mem_Id', auth()->user()->SC_ID)->first();
    //         if (isset($receipt)){
    //             $BillAmt=$receipt->BillAmt;
    //             $PaymentReceived=$receipt->PaymentReceived;
    //             $user['bill_no'] = $receipt->BillNo;
    //             $user['bill_month_year'] = $receipt->BillMonthYear;
    //             $user['bill_amount'] = $receipt->BillAmt;

    //             $received_date=$receipt->ReceivingDate;
    //             // $date = new DateTime($received_date);
    //             // $received_date= $date->format('d/m/Y');
    //             $amount_payable=$BillAmt-$PaymentReceived;

    //             $txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
    //             // $db->UpdtTxnID($txnid,$user['SC_ID']);

    //             $user['pdf'] = 'https://api.dsoigurgaon.in/storage/app/public/Bills/'.auth()->user()->SC_ID.'-'.str_replace(', ', '', $receipt->BillMonthYear).'.pdf';
    //             $user['txn_id'] = $txnid;
    //             $user['pay_status'] = $receipt->PayStatus;
    //             $user['amount_payable'] = $amount_payable;
    //             $user['received_date'] = $received_date;    
    //             $user['DisplayName'] = $member->DisplayName;    
    //             $user['MemberID'] = $member->MemberID;    
    //             $user['Mobile'] = $member->Mobile;    
    //             $user['Email'] = $member->Email;    
    //             $user['Status'] = $member->Status;
    //             $user['DOB'] = $member->DOB;    
    //             $user['SC_ID'] = $member->SC_ID;    
    //             $user['Category'] = $member->Catg_Name;
    //             if ($receipt->PayStatus == 'SUCCESS') {
    //                 $user['message'] = 'Your bill for this month is already paid.';
    //             }

    //             return response()->json($user , 200);
    //         } else {
    //             $user['bill_no'] = '';
    //             $user['bill_month_year'] = '';
    //             $user['bill_amount'] = '';
    //             $user['pdf'] = '';
    //             $user['txn_id'] = '';
    //             $user['pay_status'] = '';
    //             $user['amount_payable'] = '';
    //             $user['received_date'] = '';    
    //             $user['DisplayName'] = $member->DisplayName;    
    //             $user['MemberID'] = $member->MemberID;    
    //             $user['Mobile'] = $member->Mobile;    
    //             $user['Email'] = $member->Email;    
    //             $user['Status'] = $member->Status;
    //             $user['DOB'] = $member->DOB;    
    //             $user['SC_ID'] = $member->SC_ID;    
    //             $user['Category'] = $member->Catg_Name;
    //             $user['message'] = 'Your bill for this month is not generated.';
    //             $user['status'] = false;
    //             // $return_data = [
    //             //     'status' => false,
    //             //     'message' => 'No Record Found',
    //             //     'data' => []
    //             // ];
    //             return response()->json($user, 400);
    //         }

    //     } else {
    //         $return_data = [
    //             'status' => false,
    //             'message' => 'Member not found',
    //             'data' => []
    //         ];
    //         return response()->json($return_data , 400);
    //     }
       
    // }
    public function getStatement()
    {
        if(auth()->user()){

            $member = Member::where("memberprofile.id",auth()->user()->id)->first();

            $receipt = DB::table('MemberReceipts')->where('Mem_Id', auth()->user()->SC_ID)->first();
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

                // $user['pdf'] = 'teebooking.aepta.in/storage/app/public/Bills/'.auth()->user()->SC_ID.'-'.str_replace(', ', '', $receipt->BillMonthYear).'.pdf';
                $user['pdf'] = url('/public/Bills/'.auth()->user()->SC_ID.'-'.str_replace(', ', '', $receipt->BillMonthYear).'.pdf');
                $user['txn_id'] = $txnid;
                $user['pay_status'] = $receipt->PayStatus;
                $user['amount_payable'] = $amount_payable;
                $user['received_date'] = $received_date;    
                if ($receipt->PayStatus == 'SUCCESS') {
                    $user['message'] = 'Your bill for this month is already paid.';
                }

                return response()->json($user , 200);
            } else {
                return response()->json('Invalid User' , 400);
            }

        } else {
            return response()->json('Invalid User' , 400);
        }
       
    }
public function uploadProfile(Request $request){
     $user = auth()->user(); // Or use MemberID from request

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'User not authenticated',
        ], 401);
    }
 $request->validate([
        'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
    ]);
       if ($request->hasFile('profile_image')) {
        $image = $request->file('profile_image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        // Store in public/uploads/profile_pictures/
        $image->move(public_path('profile_pictures'), $imageName);

        // Update DB
        DB::table('memberprofile')
            ->where('MemberID', $user->MemberID)
            ->update([
                'profile_image' => $imageName,
                'updated_at' => now()
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Profile picture updated successfully',
            'image_url' =>  $imageName,
        ], 200);
    }

    return response()->json([
        'status' => false,
        'message' => 'Image not uploaded',
    ], 400);
    
}
    public function deleteAccount()
    {
        // dd($this->getAccessToken());
        if(auth()->user()){

            $member = Member::where("memberprofile.id",auth()->user()->id)->first();
            if (isset($member)){
                $return_data['status'] = true;
                $return_data['message'] = "Dear member your account has been deleted.";
                $return_data['data'] = '';
                return response()->json($return_data , 200);
            } else {
                return response()->json('Invalid User' , 400);
            }

        } else {
            return response()->json('Invalid User' , 400);
        }
    }

    public function getNotifications()
    {
        $notifications = DB::table('notifications')->where('active_status', true)->get()->map(function ($item) {
            $item->image = $item->image 
                ? 'https://teebooking.aepta.in/get-notification-image/' . $item->image 
                : null;
            return $item;
        });
        return response()->json($notifications , 200);   
    }

    public function getMemberAccountSummary(Request $request)
    {
        $sc_id = auth()->user()->SC_ID;
        // Query to get bill amount and bill date
        $memberReceipt = DB::table('MemberReceipts')
            ->select('BillAmt', DB::raw("CONCAT(IF(BillMonth = 12, BillYear + 1, BillYear), '-', LPAD(IF(BillMonth = 12, 1, BillMonth + 1), 2, '0'), '-01') AS bill_date"))
            ->where('Mem_Id', $sc_id)
            ->first();

        if (!$memberReceipt) {
            return response()->json(['status' => false, 'message' => 'Invalid Member ID.', 'data' => null], 404);
        }

        $curr_date = $memberReceipt->bill_date;
        $BillAmt = $memberReceipt->BillAmt;

        // Get total credit amount
        $total_credit = DB::table('MemberAccountLedger')
            ->where('member_id', $sc_id)
            ->where('voucher_date', '>=', $curr_date)
            ->sum('credit_amt');

        // Get total debit amount
        $total_debit = DB::table('MemberAccountLedger')
            ->where('member_id', $sc_id)
            ->where('voucher_date', '>=', $curr_date)
            ->sum('debit_amt');

        // Prepare the data to return
        $data = [
            'total_credit' => $total_credit,
            'total_debit' => $total_debit,
            'bill_amt' => $BillAmt,
            'outstanding_amt'=>0,
            // 'outstanding_amt' => $BillAmt - $total_credit + $total_debit
        ];

        // Return the response in JSON format
        return response()->json(['status' => true, 'message' => '', 'data' => $data], 200);
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

        if(!Hash::check($old_password, $user['Password'])){
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
    	    $user->update(['Password' => Hash::make($password)]);
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
    
    public function create_recharge_pay_order(Request $request) {
        $user = Member::where("id",auth()->user()->id)->first();
        if ($user) {            
            $SC_ID = $user->SC_ID;
            $RechargeAmount = $request->amount;
            $txnid = 'MCR'.substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $PayStatus ="Pending";
            // Extract user data
            $MemID = $user->MemberID;
          
            $MemberName = $user->DisplayName;
            $MobileNo = $user->Mobile;
            $Email = $user->Email;
            $Category = $user->CategoryTypeCode;
            $Status = $user->Status;

          Log::info('Checking SC_ID', ['SC_ID' => $SC_ID]);
              $user = Member::where("id",auth()->user()->id)->first();
                $key = config('services.razorpay.key');
$secret = config('services.razorpay.secret');


$api = new Api($key, $secret);

        // $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $notes = [
    'member_id'      => $user->MemberID,
    'sc_id'          => $SC_ID,
    'type'   => 'Card Recharge',
    'member_name'    => $user->DisplayName,
    'member_email'   => $user->Email,
    'member_mobile'  =>$MobileNo,
    'transaction_id'     => $txnid,
    'total_amount'   =>$request->amount,
];
            $order = $api->order->create([
                'receipt' => $txnid,
                'amount' => $request->amount * 100, // Amount in paise (100 paise = 1 INR)
                'currency' => 'INR',
                'notes' =>$notes
        //         'notes' => [
        // 'notes_1' => $user->MemberID,
        // 'notes_2' => $SC_ID,
        // 'notes_3' => 'Card Recharge',
        // 'notes_4' => '',
        // 'notes_5' => $user->DisplayName,
        //         ]
            ]);

            // Delete pending recharge records for the user
            DB::table('CardRecharge')->where('Card_ID', $SC_ID)->where('PayStatus', 'pending')->delete();
    
            // Insert new recharge record
            $rechargeID=DB::table('CardRecharge')->insertGetId([
                'Card_ID' => $SC_ID,
                'RechargeAmt' => $RechargeAmount,
                'RechargeDate' => now(),
                'PayStatus' => $PayStatus,
                'TxnRefrenceNo' => $order['id'],
                'BankRefrenceNo' => '',
                'TransactionID' => $txnid,
                'ImportStatus' => 0,
                'PaymentResponse' => '',
                'OrderResponse' => json_encode($order->toArray()),
                'TransactionType'=>'MCR',
                'PayMode'=>'mobile',
                'WebhookResponse'=>''
            ]);
             $trans_number = now()->format('dmY') . '-' . random_int(10000, 99999);
             DB::table('transactions')->insert([
            'member_id' => $SC_ID,
            'amount'=>$RechargeAmount,
               'order_id'=>$txnid,
               'payment_status'=> 'Not Paid',
               'type'=>'Card Recharge',
               	'transaction_date'=>now(),
               	'card_recharge_id'=>$rechargeID,
               	'razorpay_order_id'=>$order['id'],
               	'transID'=>$trans_number

        ]);
            $data = [
                        'orderId' => $order['id'], 
                        'amount' => $request->amount, 
                        'razorpayKey' => config('services.razorpay.key')
                    ];
        	$return_data['data'] = $data;
            $return_data['message'] = '';
            $return_data['status'] = true;
        } else {
            $return_data['data'] = '';
            $return_data['message'] = 'User not found';
            $return_data['status'] = false;
        }
        return response()->json($return_data , 200);
    }

  private function updateCardClosingBalance($memberId, $rechargeAmount, $receiptDate)
{
    $existing = DB::table('CardClosingBalance')->where('MemberID', $memberId)->first();

    if (!$existing) {
        DB::table('CardClosingBalance')->insert([
           
            'MemberID' => $memberId,
            'CardBalance' => $rechargeAmount,
            'ClosingDate' => $receiptDate,
        ]);
        Log::info("Created new CardClosingBalance for MemberID {$memberId}");
    } else {
        DB::table('CardClosingBalance')
            ->where('MemberID', $memberId)
            ->update([
                'CardBalance' => DB::raw("CardBalance + $rechargeAmount"),
                'ClosingDate' => $receiptDate,
            ]);
        Log::info("Updated CardClosingBalance for MemberID {$memberId}");
    }
}
    public function processPayment(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_response' => 'required|string',
            'status' => 'required|bool'
        ]);
                $user = Member::where("id",auth()->user()->id)->first();
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $attributes = [
            'razorpay_response' => $request->razorpay_response,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
            'status' => $request->status,
        ];

        $payment_response = json_encode($attributes);
        $cardRecharge = CardRecharge::where('TxnRefrenceNo', $request->razorpay_order_id)->first();

        try {
            // // Verify the payment signature
            // $api->utility->verifyPaymentSignature($attributes);            

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = $request->status == true ? 'Success' : 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            
              DB::table('transactions')
         ->where('razorpay_order_id', $request->razorpay_order_id)
         ->update([
               'payment_status'=>$request->status == true ? 'Paid' : 'Failed',
               	'razorpay_payment_id'=>$request->razorpay_payment_id?$request->razorpay_payment_id :null,
               	'transaction_date'=>now(),
        ]);
      
          $transaction = DB::table('transactions')
    ->where('razorpay_order_id', $request->razorpay_order_id)
    ->first();
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => $request->status == true ? 'Your card is recharged by Rs. ' . number_format($transaction->amount, 2): 'Your card recharge payment has failed.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);
   if ($request->status == true) {
          
               $this->updateCardClosingBalance($user->SC_ID, $transaction->amount, $transaction->transaction_date);
        }
            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => $request->status == true ? 'Success' : 'Failed',
                'paid_amount'=>$transaction->amount,
                'reference_number'=>$transaction->order_id
            ];

            // Payment successful, return success response
            return response()->json(['success' => true, 'message' => 'Payment Successful!', 'data' => $data], 200);
        } catch (\Exception $e) {

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => 'Your card recharge payment has failed due to unknown error.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);

            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => 'Failed',
            ];

            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => $data], 400);
        }
    }
       public function processBanquetBooking(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_response' => 'required|string',
            'status' => 'required|bool'
        ]);
                $user = Member::where("id",auth()->user()->id)->first();
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $attributes = [
            'razorpay_response' => $request->razorpay_response,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
            'status' => $request->status,
        ];

        $payment_response = json_encode($attributes);
        $cardRecharge = CardRecharge::where('TxnRefrenceNo', $request->razorpay_order_id)->first();

        try {
            // // Verify the payment signature
            // $api->utility->verifyPaymentSignature($attributes);            

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = $request->status == true ? 'Success' : 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            
              DB::table('transactions')
         ->where('razorpay_order_id', $request->razorpay_order_id)
         ->update([
               'payment_status'=>$request->status == true ? 'Paid' : 'Failed',
               	'razorpay_payment_id'=>$request->razorpay_payment_id?$request->razorpay_payment_id :null,
               	'transaction_date'=>now(),
        ]);
      
          $transaction = DB::table('transactions')
    ->where('razorpay_order_id', $request->razorpay_order_id)
    ->first();
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => $request->status == true ? 'Your card is recharged by Rs. ' . number_format($transaction->amount, 2): 'Your card recharge payment has failed.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);
   if ($request->status == true) {
          
               $this->updateCardClosingBalance($user->SC_ID, $transaction->amount, $transaction->transaction_date);
        }
            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => $request->status == true ? 'Success' : 'Failed',
                'paid_amount'=>$transaction->amount,
                'reference_number'=>$transaction->order_id
            ];

            // Payment successful, return success response
            return response()->json(['success' => true, 'message' => 'Payment Successful!', 'data' => $data], 200);
        } catch (\Exception $e) {

            if (isset($cardRecharge)) {
                $cardRecharge->PayStatus = 'Failed';
                $cardRecharge->BankRefrenceNo = $request->razorpay_payment_id;
                $cardRecharge->PaymentResponse = $payment_response;
                $cardRecharge->save(); // Now save() will work
            }
            $notification = [
                'title' => 'Card Recharge',
                'short_descriptions' => 'Your card recharge payment has failed due to unknown error.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);

            $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id,
                'Status' => 'Failed',
            ];

            return response()->json(['success' => false, 'message' => $e->getMessage(), 'data' => $data], 400);
        }
    }
    
    public function create_invoice_pay_order(Request $request) {
        $user = Member::where("id",auth()->user()->id)->first();
        if ($user) {
            $SC_ID = $user->SC_ID;
            $txnid = 'MMR'.substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            $PayStatus ="Pending";
            // Extract user data
            $MemID = $user->MemberID;
          
            $MemberName = $user->DisplayName;
            $MobileNo = $user->Mobile;
            $Email = $user->Email;
            $Category = $user->CategoryTypeCode;
            $Status = $user->Status;
            
            // Delete pending recharge records for the user
            $obj_memberReceipt = DB::table('MemberReceipts')->where('Mem_Id', $SC_ID)->first();
            // dd($obj_memberReceipt);
            
            $BillAmt=$obj_memberReceipt->BillAmt;
            $BillAmt=number_format($BillAmt, 2, '.', '');
            $BalanceAmt=$obj_memberReceipt->BalanceAmt;
            $PaymentReceived=$obj_memberReceipt->PaymentReceived;
            $PayStatus=$obj_memberReceipt->PayStatus;
            $ReceivingDate= Carbon::parse($obj_memberReceipt->ReceivingDate)->format('d/m/Y');
            $AdditionalAmount=$request->amount;
            $AdditionalAmount=number_format($AdditionalAmount, 2, '.', '');
            if($AdditionalAmount=="")
            {
            $AdditionalAmount="0.00";
            }
            $BalAmt = $BalanceAmt;
            if (isset($request->payment_type)) {
                if($request->payment_type == "Bill to Bill") {
                    if ($BillAmt < 0) {
                        $AmountPayable = 0;
                    } else {
                        $AmountPayable=$BillAmt;
                    }
                } else if($request->payment_type == "Less than Bill") {
                    if (isset($request->less_than_amount)) {
                        if ($BillAmt < 0) {
                            $AmountPayable = 0;
                        } else {
                            $AmountPayable=number_format($request->less_than_amount, 2, '.', '');
                        }
                    } else {
                        $AmountPayable = 0;
                    }
                } else {
                    if ($BillAmt < 0) {
                        $AmountPayable=$AdditionalAmount-$PaymentReceived;
                        $BalAmt = $BalanceAmt - $AdditionalAmount-$PaymentReceived;
                    } else {
                        $AmountPayable=$AdditionalAmount+$BillAmt-$PaymentReceived;
                        $BalAmt=$AdditionalAmount+$BillAmt-$PaymentReceived;
                    }
                }
            } else {
                if ($BillAmt < 0) {
                    $AmountPayable=$AdditionalAmount-$PaymentReceived;
                    $BalAmt = $BalanceAmt - $AdditionalAmount-$PaymentReceived;
                } else {
                    $AmountPayable=$AdditionalAmount+$BillAmt-$PaymentReceived;
                    $BalAmt=$AdditionalAmount+$BillAmt-$PaymentReceived;
                }
            }
            // if ($BillAmt < 0) {
            //     $AmountPayable=$AdditionalAmount-$PaymentReceived;
            //     $BalAmt = $BalanceAmt - $AdditionalAmount-$PaymentReceived;
            // } else {
            //     $AmountPayable=$AdditionalAmount+$BillAmt-$PaymentReceived;
            //     $BalAmt=$AdditionalAmount+$BillAmt-$PaymentReceived;
            // }
            // dd($AmountPayable, $BalAmt, $AdditionalAmount);

            $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
            $order = $api->order->create([
                'receipt' => $txnid,
                'amount' => $AmountPayable * 100, // Amount in paise (100 paise = 1 INR)
                'currency' => 'INR',
                'notes' => [
        'notes_1' => $user->MemberID,
        'notes_2' => $SC_ID,
        'notes_3' => 'Bill Payment',
        'notes_4' => '',
        'notes_5' => $user->DisplayName,
                ]
            ]);
 $trans_number = now()->format('dmY') . '-' . random_int(10000, 99999);
             DB::table('transactions')->insert([
            'member_id' => $SC_ID,
            'amount'=>$AmountPayable,
               'order_id'=>$txnid,
               'payment_status'=> 'Not Paid',
               'type'=>'Bill Payment',
               	'transaction_date'=>now(),
               	'member_receipt_id'=>$obj_memberReceipt->id,
               	'razorpay_order_id'=>$order['id'],
               	'transID'=>$trans_number

        ]);
            if(isset($request->payment_type) && $request->payment_type == "Less than Bill") {
                DB::table('MemberReceipts')
                ->where('Mem_Id', $SC_ID)
                ->whereIn('PayStatus', ['pending', 'FAILED', 'FAILURE', 'Failed'])
                ->update([
                    'TxnRefrenceNo' => $order['id'],
                    'TransactionID' => $txnid,
                    'BankRefrenceNo' => '',
                    'BalanceAmt' => $BalAmt,
                    'amount_paid' => $AmountPayable,
                    'PaymentType' => $request->payment_type,
                    'LessThanBillAmt' => $request->less_than_amount,
                    'PaidFrom' => 'Mobile',
                    'OrderResponse' => json_encode($order->toArray()),
                    'PaymentResponse' => '',
                    'ReceivingDate' => now()
                ]);
            } else {
                DB::table('MemberReceipts')
                ->where('Mem_Id', $SC_ID)
                ->whereIn('PayStatus', ['pending', 'FAILED', 'FAILURE', 'Failed'])
                ->update([
                    'TxnRefrenceNo' => $order['id'],
                    'TransactionID' => $txnid,
                    'BankRefrenceNo' => '',
                    'AdditionalAmt' => $AdditionalAmount,
                    'BalanceAmt' => $BalAmt,
                    'amount_paid' => $AmountPayable,
                    'PaymentType' => $request->payment_type,
                    'LessThanBillAmt' => $request->less_than_amount,
                    'PaidFrom' => 'Mobile',
                    'OrderResponse' => json_encode($order->toArray()),
                    'PaymentResponse' => '',
                    'ReceivingDate' => now()
                ]);
            }
        	
        	
            $data = [
                        'orderId' => $order['id'], 
                        'amount' => $request->amount, 
                        'razorpayKey' => config('services.razorpay.key')
                    ];

        	
        	$return_data['data'] = $data;
            $return_data['message'] = '';
            $return_data['status'] = true;
        } else {
            $return_data['data'] = '';
            $return_data['message'] = 'User not found';
            $return_data['status'] = false;
        }
        return response()->json($return_data , 200);
    }

    public function processInvoicePayment(Request $request)
    {
        // Validate request parameters
        $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'nullable|string',
            'razorpay_signature' => 'required|string',
        ]);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $attributes = [
            'razorpay_signature' => $request->razorpay_signature,
            'razorpay_payment_id' => $request->razorpay_payment_id,
            'razorpay_order_id' => $request->razorpay_order_id,
        ];

        $payment_response = json_encode($attributes);
        $memberReceipt = MemberReceipt::where('TxnRefrenceNo', $request->razorpay_order_id)->first();

        try {
            // Verify the payment signature
            $api->utility->verifyPaymentSignature($attributes);  

            if (isset($memberReceipt)) {
                $memberReceipt->PayStatus = 'Success';
                $memberReceipt->BankRefrenceNo = $request->razorpay_payment_id;
                $memberReceipt->PaymentResponse = $payment_response;
                $memberReceipt->save(); // Now save() will work
                $notification = [
                    'title' => 'Bill Payment',
                    'short_descriptions' => $request->status == true ? 'Your bill payment is done.' : 'Your bill payment is failed.',
                ];    
                $this->sendFCMMessage($notification, auth()->user()->device_id);                
            }
               Log::info('Member receipt updated.', [
        'PayStatus' => 'Success',
        'BankRefrenceNo' => $request->razorpay_payment_id,
        'PaymentResponse' => $payment_response
    ]);
  
             DB::table('transactions')
         ->where('razorpay_order_id', $request->razorpay_order_id)
         ->update([
               'payment_status'=>$request->status == true ? 'Paid' : 'Failed',
               	'razorpay_payment_id'=>$request->razorpay_payment_id?$request->razorpay_payment_id :null,
               	'transaction_date'=>now(),
        ]);
      
     
          $transaction = DB::table('transactions')
    ->where('razorpay_order_id', $request->razorpay_order_id)
    ->first();
    
    $data = [
                'MemberID' => auth()->user()->MemberID,
                'MemberName' => auth()->user()->DisplayName,
                'MemberSCID' => auth()->user()->SC_ID,
                'TransactionID' => $request->razorpay_payment_id ||"N/A",
                'Status' => $request->status == true ? 'Success' : 'Failed',
                'paid_amount'=>$transaction->amount,
                'reference_number'=>$transaction->order_id,
            ];
           
            // Payment successful, return success response
            return response()->json(['success' => true, 'message' => 'Payment Successful!','data'=>$data], 200);
        } catch (\Exception $e) {
             Log::error('Payment processing failed.', [
        'error_message' => $e->getMessage(),
        'stack_trace' => $e->getTraceAsString(),
        'request_data' => $request->all(),
    ]);

            if (isset($memberReceipt)) {
                $memberReceipt->PayStatus = 'Failed';
                $memberReceipt->BankRefrenceNo = $request->razorpay_payment_id;
                $memberReceipt->PaymentResponse = $payment_response;
                $memberReceipt->save(); // Now save() will work
            }
             DB::table('transactions')
         ->where('razorpay_order_id', $request->razorpay_order_id)
         ->update([
               'payment_status'=> 'Failed',
               	'razorpay_payment_id'=>$request->razorpay_payment_id?$request->razorpay_payment_id :null,
               	'transaction_date'=>now(),
        ]);
            $notification = [
                'title' => 'Bill Payment',
                'short_descriptions' => 'Your bill payment is failed due to unknown error.',
            ];    
            $this->sendFCMMessage($notification, auth()->user()->device_id);  

            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    private function getAccessToken() {
        $serviceAccountData = json_decode(file_get_contents('https://teebooking.aepta.in/AEPTAServiceAccountKey.json'), true);
    
        $jwtHeader = base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT'
        ]));
    
        $now = time();
        $jwtPayload = base64_encode(json_encode([
            'iss' => $serviceAccountData['client_email'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));
    
        $dataToSign = $jwtHeader . '.' . $jwtPayload;
    
        $privateKey = openssl_pkey_get_private($serviceAccountData['private_key']);
        openssl_sign($dataToSign, $jwtSignature, $privateKey, 'SHA256');
        $jwtSignature = base64_encode($jwtSignature);
    
        $jwt = $dataToSign . '.' . $jwtSignature;
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt
        ]));
    
        $response = curl_exec($ch);
        curl_close($ch);
    
        $response = json_decode($response, true);
        return $response['access_token'];
    }

    private function sendFCMMessage($notification, $fcmTokens) {
        $url = 'https://fcm.googleapis.com/v1/projects/aepta-edc61/messages:send';
        $serverKey = $this->getAccessToken();
        $data = [
            "message" => [
                "token" => $fcmTokens,
                "notification" => [
                    "title" => $notification['title'],
                    "body" => $notification['short_descriptions'],
                    "image" => 'https://aepta.in/wp-content/uploads/2023/08/aptalogo.png'
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
    
    public function MemberReceiptResponse(Request $request){

        // dd($request);
      
        $workingKey = 'D7E32D391ACF0365D8CB9B5593D1BB01'; 
        $encResponse = $request->encResp;
        $rcvdString = Helpers::decrypt($encResponse, $workingKey); // Crypto Decryption used as per the specified working key.

       
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        // dd($decryptValues);
        $dataSize=sizeof($decryptValues);

        $order_id = $tracking_id = $bank_ref_no = $ReceivedAmount = $Mem_No = $DisplayName = $trans_date = "";

        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);

            switch ($i) {
                case 0:
                    $order_id = $information[1];
                    break;
                case 1:
                    $tracking_id = $information[1];
                    break;
                case 2:
                    $bank_ref_no = $information[1];
                    break;
                case 3:
                    $order_status = $information[1];
                    break;
                case 10:
                    $ReceivedAmount = $information[1];
                    break;
                case 26:
                    $Mem_No = $information[1];
                    break;
                case 27:
                    $DisplayName = $information[1];
                    break;
                case 40:
                    $trans_date = $information[1];
                    break;
            }
        }

        $merchantRefNo = $order_id;

        // Check the CardRecharge record
        $cardRecharge = DB::table('MemberReceipts')
            ->where('TransactionID', $merchantRefNo)
            ->first();

        if ($cardRecharge) {
            $SentTransactionID = $cardRecharge->TransactionID;
            $BillAmt = $cardRecharge->BillAmt;
            $SC_ID = $cardRecharge->Mem_Id;
        }

        if ($order_status === "Success") {
            $PaymentStatus = "Success";
        } elseif ($order_status === "Aborted" || $order_status === "Failure") {
            $PaymentStatus = "Failure";
            $ReceivedAmount = 0;
        } else {
            $PaymentStatus = "Security Error. Illegal access detected";
            $ReceivedAmount = 0;
        }

        if ($SentTransactionID == $merchantRefNo && $BillAmt == $ReceivedAmount && $order_status === "Success") {
            $PaymentStatus = 'SUCCESS';
        } else {
            $PaymentStatus = 'FAILURE';
        }

        DB::table('MemberReceipts')
        ->where('TransactionID', $merchantRefNo)
        ->where('Mem_Id', $SC_ID)
        ->where('BalanceAmt', $ReceivedAmount)
        ->where('PayStatus', 'pending')
        ->update([
            'PayStatus' => $PaymentStatus,
            'TxnRefrenceNo' => $tracking_id,
            'BankRefrenceNo' => $bank_ref_no,
            'PaymentResponse' => $rcvdString,
            'ReceivingDate' => now()
        ]);
              
    
            
        $member = Member::where("memberprofile.SC_ID",$SC_ID)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
        // dd($member);
     
        // Display the message and decrypted values in a view
        return view('website.pages.subscription_mobile_response', [
            "member"=> $member ,
            'message' => $PaymentStatus,
            'order_id' => $order_id,
            'tracking_id' => $tracking_id,
            'bank_ref_no' => $bank_ref_no,
            'order_status' => $order_status,
            'ReceivedAmount' => $ReceivedAmount,
        ]);
    }
    
    public function StatusAPICardRecharge(){

        $working_key='D7E32D391ACF0365D8CB9B5593D1BB01';//Shared by CCAVENUES
        $access_code='AVPT37KL59AJ56TPJA';//Shared by CCAVENUES
        
        $cardRecharge = DB::table('CardRecharge')->select('TransactionID')
            ->where('PayStatus', 'Pending')
            ->where('RechargeDate', '>', '2024-01-01 00:00:00')
            ->get();
        foreach ($cardRecharge as $recharge) {
            // echo $recharge->TransactionID;
            
            $merchant_json_data =
            array(
            	'order_no' => $recharge->TransactionID //'SCR5a208c8d365943c1ad0b'
            );
            $merchant_data = json_encode($merchant_json_data);
            $encrypted_data=Helpers::encrypt($merchant_data,$working_key);
            $final_data = 'enc_request='.$encrypted_data.'&access_code='.$access_code.'&command=orderStatusTracker&request_type=JSON&response_type=JSON';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://apitest.ccavenue.com/apis/servlet/DoWebTrans");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            // curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/json')) ;
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
            // Get server response ...
            $result = curl_exec($ch);
            curl_close($ch);
            $status = '';
            $information = explode('&', $result);
            
            $dataSize = sizeof($information);
            for ($i = 0; $i < $dataSize; $i++) {
            	$info_value = explode('=', $information[$i]);
            	if ($info_value[0] == 'enc_response') {
            		$status = Helpers::decrypt(trim($info_value[1]), $working_key);
            		echo $status; echo '------';
            	}
            }
            // die;
            // echo 'Status revert is: ' . $status.'<pre>';
            $obj = json_decode($status, true);
            print_r($obj['Order_Status_Result']['order_status']);
            print_r('-----');
            print_r($obj['Order_Status_Result']['order_no']);
            // die;
            $order_status = $obj['Order_Status_Result']['order_status'];
                    
            // echo 'order_status ---' .$order_status;
            
            if ($order_status == 'Aborted' || $order_status == 'Unsuccessful') {
                // print_r('Enterrrrrr');
                $current_status = 'FAILED';
            } else if ($order_status == 'Shipped') {
                $current_status = 'SUCCESS';
            } else {
                $current_status = 'Pending';
            }
        }
        dd($cardRecharge);
        $encResponse = $request->encResp;
        $rcvdString = Helpers::decrypt($encResponse, $working_key); // Crypto Decryption used as per the specified working key.

       
        $order_status="";
        $decryptValues=explode('&', $rcvdString);
        $dataSize=sizeof($decryptValues);

        $order_id = $tracking_id = $bank_ref_no = $ReceivedAmount = $Mem_No = $DisplayName = $trans_date = "";

        for ($i = 0; $i < $dataSize; $i++) {
            $information = explode('=', $decryptValues[$i]);

            switch ($i) {
                case 0:
                    $order_id = $information[1];
                    break;
                case 1:
                    $tracking_id = $information[1];
                    break;
                case 2:
                    $bank_ref_no = $information[1];
                    break;
                case 3:
                    $order_status = $information[1];
                    break;
                case 10:
                    $ReceivedAmount = $information[1];
                    break;
                case 26:
                    $Mem_No = $information[1];
                    break;
                case 27:
                    $DisplayName = $information[1];
                    break;
                case 40:
                    $trans_date = $information[1];
                    break;
            }
        }

        $merchantRefNo = $order_id;

        // Check the CardRecharge record
        $cardRecharge = DB::table('CardRecharge')
            ->where('TransactionID', $merchantRefNo)
            ->first();

        if ($cardRecharge) {
            $SentTransactionID = $cardRecharge->TransactionID;
            $BillAmt = $cardRecharge->RechargeAmt;
            $C_ID = $cardRecharge->Card_ID;
        }

        if ($order_status === "Success") {
            $PaymentStatus = "Success";
        } elseif ($order_status === "Aborted" || $order_status === "Failure") {
            $PaymentStatus = "Failure";
            $ReceivedAmount = 0;
        } else {
            $PaymentStatus = "Security Error. Illegal access detected";
            $ReceivedAmount = 0;
        }

        if ($SentTransactionID == $merchantRefNo && $BillAmt == $ReceivedAmount && $order_status === "Success") {
            $PaymentStatus = 'SUCCESS';
        } else {
            $PaymentStatus = 'FAILURE';
        }

        // Retrieve C_ID from the session or redirect to index.php
        $dataReader = auth()->user();

        $PayStatus ="Pending";

        // Extract user data
        $MemID = $dataReader->MemberID;
      
        $SC_ID = $dataReader->SC_ID;
        $C_ID =$dataReader->SC_ID;

        if ($order_status === "Failure") {
            DB::table('CardRecharge')
                ->where('TransactionID', $merchantRefNo)
                ->where('Card_ID', $C_ID)
                ->where('PayStatus', 'pending')
                ->update([
                    'PayStatus' => $PaymentStatus,
                    'TxnRefrenceNo' => $tracking_id,
                    'BankRefrenceNo' => $bank_ref_no,
                    'PaymentResponse' => $request->input('encResp'),
                    //'RechargeDate' => $trans_date,
                ]);
        } else {
            DB::table('CardRecharge')
                ->where('TransactionID', $merchantRefNo)
                ->where('Card_ID', $C_ID)
                ->where('RechargeAmt', $ReceivedAmount)
                ->where('PayStatus', 'pending')
                ->update([
                    'PayStatus' => $PaymentStatus,
                    'TxnRefrenceNo' => $tracking_id,
                    'BankRefrenceNo' => $bank_ref_no,
                    'PaymentResponse' => $request->input('encResp'),
                    //'RechargeDate' => $trans_date,
                ]);

            $cardClosingBalance = DB::table('CardClosingBalance')
                ->where('MemberID', $C_ID)
                ->first();

            $ReceiptDate = now();

            if (!$cardClosingBalance) {
                DB::table('CardClosingBalance')
                    ->insert([
                        'MemberID' => $C_ID,
                        'CardBalance' => $ReceivedAmount,
                        'ClosingDate' => $ReceiptDate,
                        'is_updated' => true,
                    ]);
            } elseif (!$cardClosingBalance->is_updated) {
             
                DB::table('CardClosingBalance')
                    ->where('MemberID', $C_ID)
                    ->update([
                        'CardBalance' => DB::raw('CardBalance + ' . $ReceivedAmount),
                        'ClosingDate' => $ReceiptDate,
                        'is_updated' => true,
                    ]);
            }
        }
        $member = Member::where("memberprofile.id",auth()->user()->id)
        ->leftJoin('categorytypes', 'categorytypes.code', '=', 'memberprofile.CategoryTypeCode')->first();
     
        // Display the message and decrypted values in a view
        return view('website.pages.card_recharge_response', [
            "member"=> $member ,
            'message' => $PaymentStatus,
            'order_id' => $order_id,
            'tracking_id' => $tracking_id,
            'bank_ref_no' => $bank_ref_no,
            'order_status' => $order_status,
            'ReceivedAmount' => $ReceivedAmount,
        ]);
    }
    
    private function encrypt($plainText, $key)
    {
        $key = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $openMode = openssl_encrypt($plainText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        $encryptedText = bin2hex($openMode);
        return $encryptedText;
    }

    private function decrypt($encryptedText, $key)
    {
        $key = $this->hextobin(md5($key));
        $initVector = pack("C*", 0x00, 0x01, 0x02, 0x03, 0x04, 0x05, 0x06, 0x07, 0x08, 0x09, 0x0a, 0x0b, 0x0c, 0x0d, 0x0e, 0x0f);
        $encryptedText = $this->hextobin($encryptedText);
        $decryptedText = openssl_decrypt($encryptedText, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $initVector);
        return $decryptedText;
    }

    private function hextobin($hexString)
    {
        $length = strlen($hexString);
        $binString = "";
        $count = 0;
        while ($count < $length) {
            $subString = substr($hexString, $count, 2);
            $packedString = pack("H*", $subString);
            $binString .= $packedString;
            $count += 2;
        }
        return $binString;
    }

    public function StatusAPICardRecharge_OLD()
    {
        $working_key='D7E32D391ACF0365D8CB9B5593D1BB01';//Shared by CCAVENUES
        $access_code='AVPT37KL59AJ56TPJA';
        
        $cardRecharge = DB::table('CardRecharge')->select('TransactionID')
            ->where('PayStatus', 'Pending')
            ->where('RechargeDate', '>', '2024-01-01 00:00:00')
            ->get();
        foreach ($cardRecharge as $recharge) {
            $merchant_json_data = [
                'order_no' => $recharge->TransactionID,
                'reference_no' => ''
            ];
    
            $merchant_data = json_encode($merchant_json_data);
            $encrypted_data = $this->encrypt($merchant_data, $working_key);
            $final_data = 'enc_request=' . $encrypted_data . '&access_code=' . $access_code . '&command=orderStatusTracker&request_type=JSON&response_type=JSON';
    
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, "https://apitest.ccavenue.com/apis/servlet/DoWebTrans");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 1);
            // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $final_data);
            // Get server response ...
            $result = curl_exec($ch);
            curl_close($ch);
    
            if ($result === false) {
                return response()->json(['error' => 'CURL Error: ' . curl_error($ch)], 500);
            }
    
            $status = '';
            $information = explode('&', $result);
    
            foreach ($information as $info) {
                $info_value = explode('=', $info);
                if ($info_value[0] == 'enc_response') {
                    $status = $this->decrypt(trim($info_value[1]), $working_key);
                }
            }
    
            $obj = json_decode($status);
        }

        

        return response()->json($obj);
    }
    public function uploadeFeedback(){
         $return_data = [
            'success' => true,
            'message' => '',
    ];
        
        return response()->json($return_data);
    }
}

?>