<?php
namespace App\Http\Controllers\api\v1;

use App\Models\Member;
use App\Http\Controllers\Controller;
use App\Models\MemberAccountLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MemberAccountController extends Controller
{
    public function getMemberAccountDetails(Request $request)
    {
        $SC_ID = auth()->user()->SC_ID;
  Log::info('Checking SC_ID', ['SC_ID' => $SC_ID]);
        // Fetch ledger history, opening balance, and closing balance
        $history = MemberAccountLedger::where('member_id', $SC_ID)
                    ->orderBy('voucher_date', 'desc')
                    ->get();
                    
    Log::info('member axcoint', [
    'razorpay_key' => $history,
]);
        $opening_balance = $this->getMemberAccountOpeningBalance($SC_ID);
        $closing_balance = $this->getMemberAccountClosingBalance($SC_ID, $opening_balance);

        // Format response
        $response = [
            'opening_balance' => $opening_balance,
            'closing_balance' => $closing_balance,
            'data' => $history,
            'message' => count($history) > 0 ? '' : 'No transaction found.',
            'status' => true
        ];

        return response()->json($response);
    }

    public function getMemberAccountOpeningBalance($sc_id, $start_date = null)
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

    public function getMemberAccountClosingBalance($sc_id, $opening_balance, $start_date = null)
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

    public function filterInvoiceTransaction(Request $request)
    {
        // Validate request input
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if (!auth()->user()) {
            return response()->json(['message' => 'Member not found', 'status' => false]);
        }

        $sc_id = auth()->user()->SC_ID;

        // Get filtered transactions and opening balance
        $ledgerData = $this->getMemberAccountLedgerFilter($sc_id, $request->input('start_date'), $request->input('end_date'));
        $openingBalance = $this->getMemberAccountOpeningBalance($sc_id, $request->input('start_date'));

        // Calculate total credit, debit, and closing balance
        $totalCredit = $ledgerData['total_credit'];
        $totalDebit = $ledgerData['total_debit'];

        if (strpos($openingBalance, 'Cr') !== false) {
            $totalCredit += floatval($openingBalance);
        } elseif (strpos($openingBalance, 'Dr') !== false) {
            $totalDebit += floatval($openingBalance);
        }

        $closingBalance = $totalCredit - $totalDebit;
        $closingBalance = number_format($closingBalance, 2);
        $formattedClosingBalance = $closingBalance > 0 ? $closingBalance . ' Cr' : abs($closingBalance) . ' Dr';

        // Prepare response data
        $responseData = [
            'opening_balance' => $openingBalance,
            'closing_balance' => $formattedClosingBalance,
            'data' => $ledgerData['transactions'],
            'message' => count($ledgerData['transactions']) > 0 ? '' : 'No Transaction found.',
            'status' => true,
        ];

        return response()->json($responseData);
    }

    private function getMemberAccountLedgerFilter($scId, $startDate, $endDate)
    {
        // Query for filtered transactions
        $transactions = MemberAccountLedger::where('member_id', $scId)
            ->whereBetween('voucher_date', [$startDate, $endDate])
            ->orderByDesc('voucher_date')
            ->get();

        // Calculate total credit and debit
        $totalCredit = $transactions->sum('credit_amt');
        $totalDebit = $transactions->sum('debit_amt');

        return [
            'transactions' => $transactions,
            'total_credit' => $totalCredit,
            'total_debit' => $totalDebit,
        ];
    }
    

    public function downloadMemberAccountLedger(Request $request)
    {
        // dd('asd');
        // Validate the incoming request data
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $user = auth()->user();
        $member_id = $user->SC_ID;

        $start_date = $validated['start_date'];
        $end_date = $validated['end_date'];    

        // Fetch member transactions
        $history = $this->getMemberAccountLedger($member_id, $start_date, $end_date);
        $transactions = $history['transactions'];
        $total_credit = $history['total_credit'];
        $total_debit = $history['total_debit'];

        // Get the opening balance
        $opening_balance = $this->getMemberAccountOpeningBalance($member_id, $start_date);

        // Calculate closing balance based on credit and debit
        $closing_balance = $this->calculateClosingBalance($opening_balance, $total_credit, $total_debit);

        // Generate the HTML content
        $htmlContent = $this->generateLedgerHtml($user, $opening_balance, $closing_balance, $total_credit, $total_debit, $transactions, $start_date, $end_date);

        // Return the response
        if (!empty($transactions)) {
            return response()->json([
                'status' => true,
                'data' => $htmlContent,
                'message' => 'Ledger generated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'data' => [],
                'message' => 'No transactions found.'
            ]);
        }
    }

    private function getMemberAccountLedger($member_id, $start_date, $end_date)
    {
        // Build the query
        $query = MemberAccountLedger::where('member_id', $member_id)
            ->whereBetween('voucher_date', [$start_date, $end_date])
            ->orderBy('voucher_date', 'desc');

        $transactions = $query->get();
        
        // Calculate totals
        $total_credit = $transactions->sum('credit_amt');
        $total_debit = $transactions->sum('debit_amt');

        return [
            'transactions' => $transactions,
            'total_credit' => $total_credit,
            'total_debit' => $total_debit
        ];
    }

    // private function getMemberAccountOpeningBalance($member_id, $start_date)
    // {
    //     // Assuming you have an opening balance stored in the database
    //     $opening_balance_record = MemberAccountOpeningBalance::where('member_id', $member_id)
    //         ->where('date', '<', $start_date)
    //         ->orderBy('date', 'desc')
    //         ->first();

    //     return $opening_balance_record ? $opening_balance_record->balance : '0';
    // }

    private function calculateClosingBalance($opening_balance, $total_credit, $total_debit)
    {
        $closing_balance = 0;

        if (strpos($opening_balance, 'Cr') !== false) {
            $total_credit_closing = $total_credit + floatval($opening_balance);
            $closing_balance = $total_credit_closing - $total_debit;
        } elseif (strpos($opening_balance, 'Dr') !== false) {
            $total_debit_closing = $total_debit + floatval($opening_balance);
            $closing_balance = $total_credit - $total_debit_closing;
        }

        if ($closing_balance > 0) {
            return number_format($closing_balance, 2) . ' Cr';
        } elseif ($closing_balance < 0) {
            return number_format(abs($closing_balance), 2) . ' Dr';
        }

        return '0';
    }

    private function generateLedgerHtml($user, $opening_balance, $closing_balance, $total_credit, $total_debit, $transactions, $start_date, $end_date)
    {
        $html = '<html><body>';
        $html .= '<style>#tblheader td { width: 160px; text-align: center; vertical-align: middle; } 
                  #tblmemberinfo tr { height: 25px; } 
                  hr { height: 2px; background-color: #000000; border: none; } 
                  #tblcontent th { border: thin solid black; background-color: #c3c3c3; }
                  #tblcontent td {border: thin solid black;}</style>';
        
        $html .= '<table id="tblheader" cellspacing="0" cellpadding="0" width="100%">';
        $html .= '<tbody><tr><td><img src="https://aepta.in/wp-content/uploads/2023/08/aptalogo.png" width="100" height="100"/></td>';
        $html .= '<td><p><b>AEPTA,</b></p></td><td></td></tr><tr><td><p><b>H5V5+GCP, Delhi Cantonment,</b></p></td><td></td></tr>';
        $html .= '<tr><td><p><b>New Delhi, Delhi 110010</b></p></td></tr><tr><td><p><b>Phone: 011-25693830</b></p></td></tr></tbody></table>';
        
        $html .= '<p style="text-align: center;"><b>POSTPAID LEDGER</b></p><hr></br>';
        $html .= '<table id="tblmemberinfo"><tbody>';
        $html .= '<tr><td><b>Membership No :</b></td><td>'.$user->MemberID.'/'.$user->SC_ID.'</td></tr>';
        $html .= '<tr><td><b>Name :</b></td><td>'.$user->DisplayName.'</td></tr>';
        $html .= '<tr><td><b>From :</b></td><td>'.$start_date.'</td><td><b>To :</b></td><td>'.$end_date.'</td></tr>';
        $html .= '</tbody></table><br>';
        
        $html .= '<table id="tblcontent"><thead><tr>';
        $html .= '<th>Voucher#</th><th>Voucher Date</th><th>Particulars</th><th>CrAmt</th><th>DrAmt</th><th>Narrations</th></tr></thead><tbody>';

        foreach ($transactions as $transaction) {
            $html .= '<tr>';
            $html .= '<td>'.$transaction->voucher_no.'</td>';
            $html .= '<td>'.date('d-m-Y', strtotime($transaction->voucher_date)).'</td>';
            $html .= '<td>'.$transaction->particulars.'</td>';
            $html .= '<td>'.number_format($transaction->credit_amt, 2).'</td>';
            $html .= '<td>'.number_format($transaction->debit_amt, 2).'</td>';
            $html .= '<td>'.$transaction->narrations.'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody></table>';
        $html .= '</body></html>';

        return $html;
    }

}
