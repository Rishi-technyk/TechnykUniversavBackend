<?php

namespace App\Http\Controllers;

use DB;
use Carbon\Carbon;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\MemberReceipt;
use App\Models\CardClosingBalance;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{    
    function student_dashboard(Request $request)
    {
        $data['member'] = Auth::guard('student')->user();
        return view('frontend.dashboard', $data);
    } 

    function bill_payments(Request $request)
    {
        $member             = Auth::guard('student')->user();
        $receipts           = MemberReceipt::where('Mem_Id', $member->SC_ID)->first();
        
        $data['member']     = $member;
        $data['receipts']   = $receipts;
      
        if ($receipts != null) {
            $BillAmt = $receipts->BillAmt;
            $PaymentReceived = $receipts->PaymentReceived;
            $BillMonthYear = $receipts->BillMonthYear;
            $PayStatus = $receipts->PayStatus;
            $ReceivingDate = Carbon::parse($receipts->ReceivingDate)->format('d/m/Y');
            
            $AmountPayable = $BillAmt - $PaymentReceived;
            Session::put('amt', $AmountPayable);
        
            // Generate random transaction id
            $txnid = 'MBP' . substr(hash('sha256', mt_rand() . microtime()), 0, 20);
            Session::put('refno', $txnid);
        } else {
            // Handle case when MemberReceipts data is not found
            $AmountPayable = '0';
        }
       
        $data['AmountPayable'] = $AmountPayable;
        return view('frontend.bill_payments', $data);
    }
    
    function recharge()
    {
        $member             = Auth::guard('student')->user();
        $data['member']     = $member;
        $data['recharges']  = CardClosingBalance::where('MemberID', $member->SC_ID)->first();
        return view('frontend.recharge', $data);
    }

    function pay_card_recharge_bill_payments(Request $request)
    {
        $member                 = Auth::guard('student')->user();
        if(!$member){
            return redirect()->route('student.login');
        }

        if($request->amount <= 0 && $request->additional_amount <= 0){
            return redirect()->back()->with('error', 'Invalid Amount!');
        }   

        if($request->type != 'Card Recharge' && $request->type != 'Bill Payment') {
            return redirect()->back()->with('error', 'Invalid Payment Type!');
        }

        if($request->type == 'Bill Payment'){

            $receipts = MemberReceipt::where('Mem_Id', $member->SC_ID)->first();

            if(!$receipts){
                return redirect()->back()->with('error', 'No Bill Found for Payment!');
            }
            
            $AmountPayable = $receipts->BillAmt - $receipts->PaymentReceived;
            
            if($request->amount > $AmountPayable){
                return redirect()->back()->with('error', 'Payment Amount exceeds the Payable Amount!');
            }
            
            if($request->additional_amount>'0'){
                $params['AdditionalAmt'] = $request->additional_amount;
                
                MemberReceipt::where('Mem_Id', $member->SC_ID)->update($params);
            }
            
        }

        if($request->type == 'Card Recharge'){
            $order_id           = "CR-".uniqid();
        } else{
            $order_id           = "BP-".uniqid();
        }
        
        if($request->type=='Subscription'){

            DB::table('memberreceipts')->where('Mem_Id', $member->SC_ID)->update(['TransactionID' => $order_id, 'ReceivingDate' => date('Y-m-d H:i:s')]);

        } else {

            $recharge['Card_ID']        = $member?$member->SC_ID:'';
            $recharge['RechargeAmt']    = $request->amount; 
            $recharge['TransactionType']= 'Card Recharge'; 
            $recharge['PayMode']        = 'Web'; 
            $recharge['TxnRefrenceNo']  = $order_id; 
            $recharge['BankRefrenceNo'] = $order_id; 
            $recharge['TransactionID']  = $order_id; 
            $recharge['PaymentResponse']= '';
            $recharge['WebhookResponse']= '';
             
            DB::table('CardRecharge')->insert($recharge);

        }
        
        $transaction                    = new Transaction();
        $transaction->order_id          = $order_id;
        $transaction->transID           = $order_id;
        $transaction->amount            = $request->amount + $request->additional_amount ?? '0';
        $transaction->additionalAmt     = $request->additional_amount ?? '0';
        $transaction->type              = $request->type;
        $transaction->member_id         = $member?$member->SC_ID:'';
        $transaction->payment_status    = 'Not Paid';
        $transaction->payment_type      = 'ICICI';
        $transaction->entry_come        = 'Web';
        $transaction->save();   
        // Redirect to payment gateway or process payment here
        return redirect()->route('billdesk.pay', ['order_id' => encrypt($order_id)]);
        
    }

    function student_profile()
    {
        $data['member']     = Auth::guard('student')->user();
        return view('frontend.profile', $data);
    }

    function student_change_password()
    {
        return view('frontend.change_password');
    }

    function student_update_profile(Request $request)
    {
        $member = Auth::guard('student')->user();

        $member->DisplayName = $request->name;
        $member->Email = $request->email;
        $member->Phone = $request->phone;
        $member->Address = $request->address;
        $member->save();

        return redirect()->back()->with('success', 'Profile updated successfully!');
    }

    function student_update_password(Request $request)
    {
        $member = Auth::guard('student')->user();

        if (!password_verify($request->old_password, $member->Password)) {
            return redirect()->back()->with('error', 'Old password is incorrect!');
        }

        $member->password = $request->new_password;
        $member->save();

        return redirect()->back()->with('success', 'Password updated successfully!');
    }
    
    function create_permission(Request $request)
    {
        if($request && $request->permission){

            $permiss = ['manage','create','edit','delete','status'];

            $permiss = \DB::table('permissions_table')->get();
            
            foreach ($permiss as $key => $permis) {

                if(\DB::table('permissions')->where('name', $permis->name)->exists()){
                    

                } else {
         
                    $params['name'] = $permis->name;
                    $params['guard_name'] = 'web';
                    $params['created_at'] = date('Y-m-d H:i:s');
                    $params['updated_at'] = date('Y-m-d H:i:s');
                    \DB::table('permissions')->insert($params);

                }
                
            }
           return view('frontend.permission');
        } else {
            return view('frontend.permission');
        }
    }
}
