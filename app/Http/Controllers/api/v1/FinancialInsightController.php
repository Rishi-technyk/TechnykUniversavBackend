<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\CardClosingBalance;
use App\Models\CustomerStatement;
use App\Models\MemberAccountLedger;
use App\Models\MemberReceipt;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FinancialInsightController extends Controller
{
    public function statementDashboard(Request $request)
    {
        $memberId = auth()->user()->SC_ID;
        $filters = $this->normalizeStatementFilters($request);

        $ledgerQuery = MemberAccountLedger::query()
            ->where('member_id', $memberId);

        if ($filters['start_date'] && $filters['end_date']) {
            $ledgerQuery->whereBetween('voucher_date', [$filters['start_date'], $filters['end_date']]);
        }

        if ($filters['transaction_type'] === 'credit') {
            $ledgerQuery->where('credit_amt', '>', 0);
        } elseif ($filters['transaction_type'] === 'debit') {
            $ledgerQuery->where('debit_amt', '>', 0);
        }

        if ($filters['min_amount'] !== null) {
            $ledgerQuery->whereRaw(
                'GREATEST(COALESCE(credit_amt, 0), COALESCE(debit_amt, 0)) >= ?',
                [$filters['min_amount']]
            );
        }

        if ($filters['max_amount'] !== null) {
            $ledgerQuery->whereRaw(
                'GREATEST(COALESCE(credit_amt, 0), COALESCE(debit_amt, 0)) <= ?',
                [$filters['max_amount']]
            );
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $ledgerQuery->where(function ($query) use ($search) {
                $query->where('voucher_no', 'like', "%{$search}%")
                    ->orWhere('particulars', 'like', "%{$search}%")
                    ->orWhere('narrations', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['voucher_date', 'voucher_no', 'credit_amt', 'debit_amt'];
        $sortKey = in_array($filters['sort_key'], $allowedSorts, true) ? $filters['sort_key'] : 'voucher_date';
        $sortDir = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';

        $transactions = $ledgerQuery
            ->orderBy($sortKey, $sortDir)
            ->limit($filters['limit'])
            ->get();

        $summaryBase = $this->statementSummaryBase($memberId);
        $metrics = $this->buildStatementMetrics($transactions, $summaryBase, $filters);

        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'filters' => $filters,
                'summary' => $summaryBase,
                'analytics' => $metrics,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function memberHomeDashboard()
    {
        $memberId = auth()->user()->SC_ID;
        $summaryBase = $this->statementSummaryBase($memberId);
        $currentBalance = (float) (CardClosingBalance::query()
            ->where('MemberID', $memberId)
            ->value('CardBalance') ?? 0);

        $windowStart = Carbon::now()->subMonthsNoOverflow(5)->startOfMonth()->toDateString();
        $recentWindowStart = Carbon::now()->subDays(30)->toDateString();

        $statementRows = CustomerStatement::query()
            ->select(['BillNo', 'BillDate', 'Amount', 'LocationName', 'PayMode', 'Balance', 'SNo'])
            ->where('MemberId', $memberId)
            ->where('BillDate', '>=', $windowStart)
            ->orderBy('BillDate', 'desc')
            ->orderBy('SNo', 'desc')
            ->get();

        $ledgerRows = MemberAccountLedger::query()
            ->select(['voucher_date', 'voucher_no', 'credit_amt', 'debit_amt', 'particulars', 'narrations'])
            ->where('member_id', $memberId)
            ->where('voucher_date', '>=', $recentWindowStart)
            ->orderBy('voucher_date', 'desc')
            ->limit(80)
            ->get();

        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'summary' => array_merge($summaryBase, [
                    'current_balance' => round($currentBalance, 2),
                ]),
                'analytics' => $this->buildHomeMetrics(
                    $summaryBase,
                    $currentBalance,
                    $statementRows,
                    $ledgerRows
                ),
            ],
        ]);
    }

    public function rechargeDashboard(Request $request)
    {
        $memberId = auth()->user()->SC_ID;
        $filters = $this->normalizeRechargeFilters($request);

        $query = CustomerStatement::query()
            ->where('MemberId', $memberId);

        if ($filters['start_date'] && $filters['end_date']) {
            $query->whereBetween('BillDate', [$filters['start_date'], $filters['end_date']]);
        }

        if (!empty($filters['locations'])) {
            $query->whereIn('LocationName', $filters['locations']);
        }

        if (!empty($filters['pay_modes'])) {
            $query->whereIn('PayMode', $filters['pay_modes']);
        }

        if ($filters['transaction_type'] === 'credit') {
            $query->where('Amount', '>=', 0);
        } elseif ($filters['transaction_type'] === 'debit') {
            $query->where('Amount', '<', 0);
        }

        if ($filters['min_amount'] !== null) {
            $query->whereRaw('ABS(COALESCE(Amount, 0)) >= ?', [$filters['min_amount']]);
        }

        if ($filters['max_amount'] !== null) {
            $query->whereRaw('ABS(COALESCE(Amount, 0)) <= ?', [$filters['max_amount']]);
        }

        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($nested) use ($search) {
                $nested->where('BillNo', 'like', "%{$search}%")
                    ->orWhere('LocationName', 'like', "%{$search}%")
                    ->orWhere('PayMode', 'like', "%{$search}%");
            });
        }

        $allowedSorts = ['BillDate', 'BillNo', 'Amount', 'LocationName', 'PayMode'];
        $sortKey = in_array($filters['sort_key'], $allowedSorts, true) ? $filters['sort_key'] : 'BillDate';
        $sortDir = $filters['sort_dir'] === 'asc' ? 'asc' : 'desc';

        $transactions = $query
            ->orderBy($sortKey, $sortDir)
            ->orderBy('SNo', 'desc')
            ->limit($filters['limit'])
            ->get();
        $currentBalance = (float) (CardClosingBalance::query()
            ->where('MemberID', $memberId)
            ->value('CardBalance') ?? 0);

        $analytics = $this->buildRechargeMetrics($transactions, $currentBalance);

        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'filters' => $filters,
                'summary' => [
                    'current_balance' => round($currentBalance, 2),
                ],
                'analytics' => $analytics,
                'transactions' => $transactions,
            ],
        ]);
    }

    public function askAi(Request $request)
    {
        $validated = $request->validate([
            'context_type' => 'required|in:statement,recharge',
            'prompt' => 'required|string|max:1200',
            'filters' => 'nullable|array',
        ]);

        $contextType = $validated['context_type'];
        $prompt = trim($validated['prompt']);
        $filters = $validated['filters'] ?? [];

        $payload = $contextType === 'statement'
            ? $this->buildAiStatementPayload(auth()->user()->SC_ID, $filters)
            : $this->buildAiRechargePayload(auth()->user()->SC_ID, $filters);

        $fallback = $this->buildFallbackAiAnswer($contextType, $prompt, $payload);
        $aiData = $this->fetchHuggingFaceInsight($contextType, $prompt, $payload);

        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'provider' => $aiData['provider'] ?? 'local-fallback',
                'answer' => $aiData['answer'] ?? $fallback,
                'fallback_used' => empty($aiData['answer']),
                'context_snapshot' => $payload,
            ],
        ]);
    }

    private function normalizeStatementFilters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'transaction_type' => $request->input('transaction_type'),
            'min_amount' => $request->filled('min_amount') ? (float) $request->input('min_amount') : null,
            'max_amount' => $request->filled('max_amount') ? (float) $request->input('max_amount') : null,
            'search' => trim((string) $request->input('search', '')) ?: null,
            'sort_key' => $request->input('sort_key', 'voucher_date'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
            'limit' => min(max((int) $request->input('limit', 150), 10), 300),
        ];
    }

    private function normalizeRechargeFilters(Request $request): array
    {
        return [
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'locations' => $this->normalizeArrayInput($request->input('locations')),
            'pay_modes' => $this->normalizeArrayInput($request->input('pay_modes', $request->input('pay_mode'))),
            'transaction_type' => $request->input('transaction_type'),
            'min_amount' => $request->filled('min_amount') ? (float) $request->input('min_amount') : null,
            'max_amount' => $request->filled('max_amount') ? (float) $request->input('max_amount') : null,
            'search' => trim((string) $request->input('search', '')) ?: null,
            'sort_key' => $request->input('sort_key', 'BillDate'),
            'sort_dir' => $request->input('sort_dir', 'desc'),
            'limit' => min(max((int) $request->input('limit', 150), 10), 300),
        ];
    }

    private function normalizeArrayInput($value): array
    {
        if ($value === null || $value === '') {
            return [];
        }

        if (is_array($value)) {
            return array_values(array_filter(array_map('trim', $value), fn ($item) => $item !== ''));
        }

        return array_values(array_filter(array_map('trim', explode(',', (string) $value)), fn ($item) => $item !== ''));
    }

    private function statementSummaryBase(string $memberId): array
    {
        $receipt = $this->latestReceiptQuery($memberId)->first();

        $billAmount = (float) ($receipt->BillAmt ?? 0);
        $paymentReceived = (float) ($receipt->PaymentReceived ?? 0);
        $amountPayable = $billAmount - $paymentReceived;

        $creditsAfterBill = (float) MemberAccountLedger::query()
            ->where('member_id', $memberId)
            ->where('voucher_date', '>=', $this->currentBillingAnchor($receipt))
            ->sum('credit_amt');

        $debitsAfterBill = (float) MemberAccountLedger::query()
            ->where('member_id', $memberId)
            ->where('voucher_date', '>=', $this->currentBillingAnchor($receipt))
            ->sum('debit_amt');

        $creditLimit = (float) (auth()->user()->credit_limit ?? 0);
        $outstanding = $billAmount - $creditsAfterBill + $debitsAfterBill;

        return [
            'bill_no' => $receipt->BillNo ?? null,
            'bill_month_year' => $receipt->BillMonthYear ?? null,
            'bill_amount' => round($billAmount, 2),
            'payment_received' => round($paymentReceived, 2),
            'amount_payable' => round($amountPayable, 2),
            'post_bill_credit_total' => round($creditsAfterBill, 2),
            'unbilled_debit' => round($debitsAfterBill, 2),
            'credit_limit' => round($creditLimit, 2),
            'outstanding_amount' => round($outstanding, 2),
            'available_credit' => round($creditLimit - $outstanding, 2),
            'pdf' => isset($receipt->BillMonthYear)
                ? url('public/Bills/' . auth()->user()->SC_ID . '-' . str_replace(', ', '', $receipt->BillMonthYear)) . '.pdf'
                : null,
            'pay_status' => $receipt->PayStatus ?? null,
            'received_date' => $receipt->ReceivingDate ?? null,
        ];
    }

    private function currentBillingAnchor(?MemberReceipt $receipt): string
    {
        if (!$receipt || !$receipt->BillYear || !$receipt->BillMonth) {
            return Carbon::now()->startOfMonth()->toDateString();
        }

        $month = (int) $receipt->BillMonth;
        $year = (int) $receipt->BillYear;

        if ($month === 12) {
            return Carbon::create($year + 1, 1, 1)->toDateString();
        }

        return Carbon::create($year, $month + 1, 1)->toDateString();
    }

    private function latestReceiptQuery(string $memberId)
    {
        return MemberReceipt::query()
            ->where('Mem_Id', $memberId)
            ->orderByRaw('CAST(COALESCE(BillYear, 0) AS UNSIGNED) DESC')
            ->orderByRaw('CAST(COALESCE(BillMonth, 0) AS UNSIGNED) DESC')
            ->orderByDesc('BillNo');
    }

    private function buildStatementMetrics(Collection $transactions, array $summaryBase, array $filters): array
    {
        $credits = $transactions->sum(fn ($row) => (float) $row->credit_amt);
        $debits = $transactions->sum(fn ($row) => (float) $row->debit_amt);
        $activityCount = $transactions->count();
        $creditRows = $transactions->filter(fn ($row) => (float) $row->credit_amt > 0);
        $debitRows = $transactions->filter(fn ($row) => (float) $row->debit_amt > 0);

        return [
            'cards' => [
                'credit_total' => round($credits, 2),
                'debit_total' => round($debits, 2),
                'net_flow' => round($credits - $debits, 2),
                'activity_count' => $activityCount,
            ],
            'highlights' => [
                'largest_credit' => $creditRows->max('credit_amt') ? round((float) $creditRows->max('credit_amt'), 2) : 0,
                'largest_debit' => $debitRows->max('debit_amt') ? round((float) $debitRows->max('debit_amt'), 2) : 0,
                'average_credit' => $creditRows->count() ? round($creditRows->avg('credit_amt'), 2) : 0,
                'average_debit' => $debitRows->count() ? round($debitRows->avg('debit_amt'), 2) : 0,
                'selected_period' => [
                    'from' => $filters['start_date'],
                    'to' => $filters['end_date'],
                ],
            ],
            'monthly_trend' => $transactions
                ->groupBy(fn ($row) => Carbon::parse($row->voucher_date)->format('Y-m'))
                ->map(function (Collection $rows, string $month) {
                    return [
                        'month' => $month,
                        'credit_total' => round($rows->sum(fn ($row) => (float) $row->credit_amt), 2),
                        'debit_total' => round($rows->sum(fn ($row) => (float) $row->debit_amt), 2),
                    ];
                })
                ->values(),
            'quick_insights' => [
                'outstanding_health' => $summaryBase['outstanding_amount'] > 0 ? 'attention' : 'healthy',
                'payment_status' => $summaryBase['pay_status'] ?? 'UNKNOWN',
            ],
        ];
    }

    private function buildRechargeMetrics(Collection $transactions, float $currentBalance): array
    {
        $credits = $transactions->filter(fn ($row) => (float) $row->Amount >= 0);
        $debits = $transactions->filter(fn ($row) => (float) $row->Amount < 0);

        return [
            'cards' => [
                'current_balance' => round($currentBalance, 2),
                'credit_total' => round($credits->sum(fn ($row) => (float) $row->Amount), 2),
                'debit_total' => round(abs($debits->sum(fn ($row) => (float) $row->Amount)), 2),
                'transaction_count' => $transactions->count(),
            ],
            'highlights' => [
                'largest_recharge' => $credits->max('Amount') ? round((float) $credits->max('Amount'), 2) : 0,
                'largest_spend' => $debits->min('Amount') ? round(abs((float) $debits->min('Amount')), 2) : 0,
                'average_recharge' => $credits->count() ? round($credits->avg('Amount'), 2) : 0,
                'average_spend' => $debits->count() ? round(abs($debits->avg('Amount')), 2) : 0,
            ],
            'payment_mode_breakdown' => $transactions
                ->groupBy('PayMode')
                ->map(fn (Collection $rows, string $key) => [
                    'label' => $key,
                    'count' => $rows->count(),
                    'amount' => round($rows->sum(fn ($row) => abs((float) $row->Amount)), 2),
                ])
                ->values(),
            'location_breakdown' => $transactions
                ->groupBy('LocationName')
                ->map(fn (Collection $rows, string $key) => [
                    'label' => $key,
                    'count' => $rows->count(),
                    'amount' => round($rows->sum(fn ($row) => abs((float) $row->Amount)), 2),
                ])
                ->values(),
            'daily_trend' => $transactions
                ->groupBy(fn ($row) => Carbon::parse($row->BillDate)->format('Y-m-d'))
                ->map(fn (Collection $rows, string $day) => [
                    'day' => $day,
                    'total' => round($rows->sum(fn ($row) => (float) $row->Amount), 2),
                ])
                ->values(),
        ];
    }

    private function buildHomeMetrics(
        array $summaryBase,
        float $currentBalance,
        Collection $statementRows,
        Collection $ledgerRows
    ): array {
        $recentStatements = $statementRows->filter(
            fn ($row) => Carbon::parse($row->BillDate)->gte(Carbon::now()->subDays(30))
        );
        $recentCredits = $recentStatements->filter(fn ($row) => (float) $row->Amount >= 0);
        $recentDebits = $recentStatements->filter(fn ($row) => (float) $row->Amount < 0);

        $creditLimit = max((float) ($summaryBase['credit_limit'] ?? 0), 0);
        $outstandingAmount = max((float) ($summaryBase['outstanding_amount'] ?? 0), 0);
        $utilizationPercent = $creditLimit > 0
            ? round(($outstandingAmount / $creditLimit) * 100, 1)
            : 0;

        $locationBreakdown = $statementRows
            ->groupBy(fn ($row) => trim((string) ($row->LocationName ?? 'Unknown')) ?: 'Unknown')
            ->map(fn (Collection $rows, string $label) => [
                'label' => $label,
                'count' => $rows->count(),
                'amount' => round($rows->sum(fn ($row) => abs((float) $row->Amount)), 2),
            ])
            ->sortByDesc('amount')
            ->values()
            ->take(4)
            ->values();

        $payModeBreakdown = $statementRows
            ->groupBy(fn ($row) => trim((string) ($row->PayMode ?? 'Unknown')) ?: 'Unknown')
            ->map(fn (Collection $rows, string $label) => [
                'label' => $label,
                'count' => $rows->count(),
                'amount' => round($rows->sum(fn ($row) => abs((float) $row->Amount)), 2),
            ])
            ->sortByDesc('count')
            ->values()
            ->take(4)
            ->values();

        $trend = $statementRows
            ->groupBy(fn ($row) => Carbon::parse($row->BillDate)->format('Y-m'))
            ->sortKeys()
            ->map(function (Collection $rows, string $monthKey) {
                $month = Carbon::createFromFormat('Y-m', $monthKey);
                $credits = $rows->filter(fn ($row) => (float) $row->Amount >= 0);
                $debits = $rows->filter(fn ($row) => (float) $row->Amount < 0);

                return [
                    'month_key' => $monthKey,
                    'month_label' => $month->format('M Y'),
                    'recharge_total' => round($credits->sum(fn ($row) => (float) $row->Amount), 2),
                    'spend_total' => round(abs($debits->sum(fn ($row) => (float) $row->Amount)), 2),
                    'activity_count' => $rows->count(),
                ];
            })
            ->values();

        $activityFeed = collect()
            ->merge($statementRows->take(4)->map(function ($row) {
                return [
                    'type' => 'card',
                    'title' => (float) $row->Amount >= 0 ? 'Card recharge recorded' : 'Card spend recorded',
                    'subtitle' => trim((string) ($row->LocationName ?? $row->PayMode ?? 'Card activity')) ?: 'Card activity',
                    'reference' => $row->BillNo,
                    'amount' => round((float) $row->Amount, 2),
                    'date' => $row->BillDate,
                    'date_sort' => Carbon::parse($row->BillDate)->timestamp,
                ];
            }))
            ->merge($ledgerRows->take(4)->map(function ($row) {
                $credit = (float) $row->credit_amt;
                $debit = (float) $row->debit_amt;

                return [
                    'type' => 'account',
                    'title' => $credit > 0 ? 'Ledger credit posted' : 'Ledger debit posted',
                    'subtitle' => trim((string) ($row->particulars ?? $row->narrations ?? 'Account movement')) ?: 'Account movement',
                    'reference' => $row->voucher_no,
                    'amount' => round($credit > 0 ? $credit : -$debit, 2),
                    'date' => $row->voucher_date,
                    'date_sort' => Carbon::parse($row->voucher_date)->timestamp,
                ];
            }))
            ->sortByDesc('date_sort')
            ->take(6)
            ->values()
            ->map(fn ($row) => collect($row)->except('date_sort')->all());

        $topLocation = $locationBreakdown->first();
        $topPayMode = $payModeBreakdown->first();
        $latestActivityDate = $activityFeed->first()['date'] ?? null;

        return [
            'cards' => [
                [
                    'label' => '30D Spend',
                    'value' => round(abs($recentDebits->sum(fn ($row) => (float) $row->Amount)), 2),
                    'prefix' => '₹',
                    'tone' => 'danger',
                    'helper' => 'Recent card usage',
                ],
                [
                    'label' => '30D Recharge',
                    'value' => round($recentCredits->sum(fn ($row) => (float) $row->Amount), 2),
                    'prefix' => '₹',
                    'tone' => 'success',
                    'helper' => 'Credits added recently',
                ],
                [
                    'label' => '30D Activity',
                    'value' => $recentStatements->count(),
                    'suffix' => 'txns',
                    'tone' => 'primary',
                    'helper' => 'Card-side movement',
                ],
                [
                    'label' => 'Credit Utilisation',
                    'value' => $utilizationPercent,
                    'suffix' => '%',
                    'tone' => $utilizationPercent >= 75 ? 'warning' : 'secondary',
                    'helper' => 'Outstanding vs limit',
                ],
            ],
            'highlights' => [
                'payment_status' => $summaryBase['pay_status'] ?? 'UNKNOWN',
                'latest_bill_cycle' => $summaryBase['bill_month_year'] ?? null,
                'latest_activity_date' => $latestActivityDate,
                'top_spend_location' => $topLocation['label'] ?? null,
                'preferred_pay_mode' => $topPayMode['label'] ?? null,
            ],
            'insights' => [
                [
                    'title' => 'Available credit',
                    'value' => round((float) ($summaryBase['available_credit'] ?? 0), 2),
                    'prefix' => '₹',
                    'tone' => ((float) ($summaryBase['available_credit'] ?? 0)) < 0 ? 'danger' : 'success',
                    'description' => 'How much room is left before the current limit is exhausted.',
                ],
                [
                    'title' => 'Amount payable',
                    'value' => round((float) ($summaryBase['amount_payable'] ?? 0), 2),
                    'prefix' => '₹',
                    'tone' => ((float) ($summaryBase['amount_payable'] ?? 0)) > 0 ? 'warning' : 'success',
                    'description' => 'Outstanding amount on the latest generated member bill.',
                ],
                [
                    'title' => 'Top spend location',
                    'value' => $topLocation['label'] ?? 'No recent spend',
                    'tone' => 'secondary',
                    'description' => $topLocation
                        ? '₹' . number_format((float) $topLocation['amount'], 2) . ' across ' . $topLocation['count'] . ' transactions.'
                        : 'No location pattern available yet.',
                ],
            ],
            'monthly_trend' => $trend,
            'location_breakdown' => $locationBreakdown,
            'payment_mode_breakdown' => $payModeBreakdown,
            'recent_activity' => $activityFeed,
        ];
    }

    private function buildAiStatementPayload(string $memberId, array $filters): array
    {
        $request = new Request($filters);
        $normalized = $this->normalizeStatementFilters($request);

        $query = MemberAccountLedger::query()->where('member_id', $memberId);

        if ($normalized['start_date'] && $normalized['end_date']) {
            $query->whereBetween('voucher_date', [$normalized['start_date'], $normalized['end_date']]);
        }

        $rows = $query->orderByDesc('voucher_date')->limit(40)->get();

        return [
            'summary' => $this->statementSummaryBase($memberId),
            'totals' => [
                'credit_total' => round($rows->sum(fn ($row) => (float) $row->credit_amt), 2),
                'debit_total' => round($rows->sum(fn ($row) => (float) $row->debit_amt), 2),
                'activity_count' => $rows->count(),
            ],
            'recent_transactions' => $rows->map(fn ($row) => [
                'date' => $row->voucher_date,
                'voucher_no' => $row->voucher_no,
                'particulars' => $row->particulars,
                'credit_amt' => (float) $row->credit_amt,
                'debit_amt' => (float) $row->debit_amt,
                'narrations' => $row->narrations,
            ])->values(),
        ];
    }

    private function buildAiRechargePayload(string $memberId, array $filters): array
    {
        $request = new Request($filters);
        $normalized = $this->normalizeRechargeFilters($request);

        $query = CustomerStatement::query()->where('MemberId', $memberId);

        if ($normalized['start_date'] && $normalized['end_date']) {
            $query->whereBetween('BillDate', [$normalized['start_date'], $normalized['end_date']]);
        }

        if (!empty($normalized['locations'])) {
            $query->whereIn('LocationName', $normalized['locations']);
        }

        if (!empty($normalized['pay_modes'])) {
            $query->whereIn('PayMode', $normalized['pay_modes']);
        }

        $rows = $query->orderByDesc('BillDate')->limit(40)->get();

        return [
            'summary' => [
                'current_balance' => round((float) (CardClosingBalance::query()->where('MemberID', $memberId)->value('CardBalance') ?? 0), 2),
                'transaction_count' => $rows->count(),
                'credit_total' => round($rows->filter(fn ($row) => (float) $row->Amount >= 0)->sum('Amount'), 2),
                'debit_total' => round(abs($rows->filter(fn ($row) => (float) $row->Amount < 0)->sum('Amount')), 2),
            ],
            'recent_transactions' => $rows->map(fn ($row) => [
                'date' => $row->BillDate,
                'bill_no' => $row->BillNo,
                'location' => $row->LocationName,
                'pay_mode' => $row->PayMode,
                'amount' => (float) $row->Amount,
                'balance' => $row->Balance,
            ])->values(),
        ];
    }

    private function fetchHuggingFaceInsight(string $contextType, string $prompt, array $payload): array
    {
        $token = config('services.huggingface.token');
        $model = config('services.huggingface.model', 'openai/gpt-oss-120b:cerebras');

        if (!$token) {
            return [];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(30)
                ->post(rtrim(config('services.huggingface.base_url'), '/') . '/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'You are a concise financial assistant inside a club app. Reply with short actionable insights, simple bullet points, and avoid hallucinating unknown facts.',
                        ],
                        [
                            'role' => 'user',
                            'content' => json_encode([
                                'context_type' => $contextType,
                                'user_prompt' => $prompt,
                                'financial_data' => $payload,
                            ], JSON_PRETTY_PRINT),
                        ],
                    ],
                    'temperature' => 0.3,
                    'max_tokens' => 350,
                ]);

            if (!$response->successful()) {
                Log::warning('HuggingFace AI request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return [];
            }

            $body = $response->json();
            $answer = $body['choices'][0]['message']['content'] ?? null;

            return [
                'provider' => 'huggingface',
                'answer' => $answer,
            ];
        } catch (\Throwable $exception) {
            Log::warning('HuggingFace AI exception', [
                'message' => $exception->getMessage(),
            ]);

            return [];
        }
    }

    private function buildFallbackAiAnswer(string $contextType, string $prompt, array $payload): string
    {
        if ($contextType === 'statement') {
            $summary = $payload['summary'];
            $totals = $payload['totals'];

            return implode("\n", [
                "Prompt: {$prompt}",
                "Outstanding amount is ₹" . number_format((float) $summary['outstanding_amount'], 2) . ".",
                "Available credit is ₹" . number_format((float) $summary['available_credit'], 2) . ".",
                "Filtered credits total ₹" . number_format((float) $totals['credit_total'], 2) . " and debits total ₹" . number_format((float) $totals['debit_total'], 2) . ".",
                "Use the filters to narrow by date or amount if you want a sharper explanation.",
            ]);
        }

        $summary = $payload['summary'];

        return implode("\n", [
            "Prompt: {$prompt}",
            "Current card balance is ₹" . number_format((float) $summary['current_balance'], 2) . ".",
            "Filtered recharge-side credits total ₹" . number_format((float) $summary['credit_total'], 2) . ".",
            "Filtered spends total ₹" . number_format((float) $summary['debit_total'], 2) . ".",
            "Transaction count in this view is " . (int) $summary['transaction_count'] . ".",
        ]);
    }
}
