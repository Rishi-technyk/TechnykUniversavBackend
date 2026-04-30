<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Card;
use App\Models\CardItem;
use App\Models\Transaction;
use App\Models\CustomerStatement;
use App\Models\MemberAccountLedger;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    function index()
    {
        $member  = Auth::guard('student')->user();

        $data['tt'] = Transaction::where('member_id', $member?$member->SC_ID:'')->whereNotIn('type', ['Room Booking','Banquet Booking','Activity Booking'])->orderBy('id', 'DESC')->paginate(25);

        $data['member'] = $member;

        return view('frontend.transaction.index',$data);
    }

    function prepaid(Request $request)
    {
        $member  = Auth::guard('student')->user();

        // Create a base query for customer statements
        $query = CustomerStatement::select('BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo')
                    ->where('MemberId', $member->SC_ID);

        // Apply location filter if provided
        if ($request->location) {
            $query->where('LocationName', $request->location);
        }

        // Apply paymode filter if provided
        if ($request->paymode) {
            $query->where('PayMode', $request->paymode);
        }

        // Apply date range filters if provided
        if ($request->start_date) {
            $query->where('BillDate', '>=', $request->start_date);
        }
        if ($request->end_date) {
            $query->where('BillDate', '<=', $request->end_date);
        }

        // Fetch customer statements
        $customerStatements = $query->orderBy('BillDate', 'DESC')
                                    ->orderBy('SNo', 'DESC')
                                    ->paginate(25);

        // Fetch distinct LocationName and PayMode in a single query
        $distinctData = CustomerStatement::selectRaw('DISTINCT LocationName, PayMode')
                        ->where('MemberId', $member->SC_ID)
                        ->paginate(25);

        // Prepare arrays for location and paymode
        $member['location'] = $distinctData->pluck('LocationName')->unique()->toArray();
        $member['paymode'] = $distinctData->pluck('PayMode')->unique()->toArray();

        $data['member'] = $member;

        return view('frontend.transaction.prepaid', [
            'history' => $customerStatements,
            'member' => $member,
        ]);
        
    }

    function postpaid(Request $request)
    {
        $member  = Auth::guard('student')->user();

        $query = MemberAccountLedger::where('member_id', $member->SC_ID);

        // Apply filters based on the request
        if ($request->start_date) {
            $query->where('voucher_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('voucher_date', '<=', $request->end_date);
        }

        // Fetch the filtered ledger history
        $history = $query->orderBy('voucher_date', 'desc')->paginate(25);
        $total_credit = $query->sum('credit_amt');
        $total_debit = $query->sum('debit_amt');

        // Get opening and closing balances
        if ($request->start_date) {
            $opening_balance = $this->getMemberAccountOpeningBalance($member->SC_ID, $request->start_date);
            $closing_balance = $this->getMemberAccountClosingBalance($member->SC_ID, $opening_balance, $request->start_date);
        } else {
            $opening_balance = $this->getMemberAccountOpeningBalance($member->SC_ID);
            $closing_balance = $this->getMemberAccountClosingBalance($member->SC_ID, $opening_balance);
        }
        
        // Prepare data for the view
        return view('frontend.transaction.postpaid', [
            'history' => $history,
            'member' => $member,
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'total_credit' => $total_credit,
            'total_debit' => $total_debit,
            'member' => $member,
        ]);

    }

    function getMemberAccountOpeningBalance($sc_id, $start_date = null)
    {
        // Calculate the opening balance
        $query = MemberAccountLedger::where('member_id', $sc_id);

        if (!empty($start_date)) {
            $query->where('voucher_date', '<', $start_date);
        }

        $credit = $query->sum('credit_amt');
        $debit = $query->sum('debit_amt');

        $opening_balance = $credit - $debit;

        if ($opening_balance > 0) {
            return number_format($opening_balance, 2) . ' Cr';
        } elseif ($opening_balance < 0) {
            return number_format(abs($opening_balance), 2) . ' Dr';
        } else {
            return '0.00';
        }
    }

    function getMemberAccountClosingBalance($sc_id, $opening_balance, $start_date = null)
    {
        $query = MemberAccountLedger::where('member_id', $sc_id);

        if (!empty($start_date)) {
            $query->where('voucher_date', '<=', $start_date);
        }

        $total_credit = $query->sum('credit_amt');
        $total_debit = $query->sum('debit_amt');

        // Calculate closing balance based on opening balance type
        if (strpos($opening_balance, 'Cr') !== false) {
            $closing_balance = floatval($opening_balance) + $total_credit - $total_debit;
        } elseif (strpos($opening_balance, 'Dr') !== false) {
            $closing_balance = -1 * floatval($opening_balance) + $total_debit - $total_credit;
        } else {
            $closing_balance = $total_credit - $total_debit;
        }

        // Format the closing balance

        if ($closing_balance > 0) {
            return number_format($closing_balance, 2) . ' Cr';
        } elseif ($closing_balance < 0) {
            return number_format(abs($closing_balance), 2) . ' Dr';
        } else {
            return '0.00';
        }
    }
}
