<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class FB_BillHead extends Model
{
    use HasFactory;

    // Table name (non-standard)
    protected $table = 'FB_BillHead';

    // Primary key is BillNo and it's a string (not auto-incrementing)
    protected $primaryKey = 'BillNo';
    public $incrementing = false;
    protected $keyType = 'string';

    // The table uses CreationDate / ModificationDate instead of created_at/updated_at
    const CREATED_AT = 'CreationDate';
    const UPDATED_AT = 'ModificationDate';

    // If your database does not actually maintain timestamps automatically,
    // you can set $timestamps = false and manage them manually.
    public $timestamps = true;

    // Mass assignable attributes (tweak as needed)
    protected $fillable = [
        'BillNo',
        'BillDate',
        'MemberID',
        'MemberName',
        'WaiterCode',
        'TableCode',
        'BookingNo',
        'RoomCode',
        'IssueNo',
        'PAX',
        'BillStatus',
        'BillType',
        'Amount',
        'RoundOff',
        'ModeOfPayment',
        'RefNo',
        'RefDate',
        'ValidationMode',
        'Remarks',
        'OpeningBalance',
        'ClosingBalance',
        'CreationDate',
        'ModificationDate',
        'UserCode',
        'LocationCode',
        'YearCode',
    ];

    // Casts
    protected $casts = [
        'BillDate' => 'datetime',
        'RefDate' => 'datetime',
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
        'PAX' => 'integer',
        'Amount' => 'decimal:2',
        'RoundOff' => 'decimal:2',
        'OpeningBalance' => 'decimal:2',
        'ClosingBalance' => 'decimal:2',
    ];
   public static function getNextBillNo($locationCode, $yearCode)
{
    return DB::transaction(function () use ($locationCode, $yearCode) {
        // Lock only relevant rows, not the entire table
        $maxBill = self::where('LocationCode', $locationCode)
            ->where('YearCode', $yearCode)
            ->lockForUpdate()
            ->max(DB::raw('CAST(BillNo AS INT)'));

        return ($maxBill ?? 0) + 1;
    });
}
    // If your table uses different date/time format, set $dateFormat accordingly:
    // protected $dateFormat = 'Y-m-d H:i:s';

    /*
     * Example relations - uncomment/adjust if the related models exist.
     *
     * public function member()
     * {
     *     return $this->belongsTo(Member::class, 'MemberID', 'MemberID');
     * }
     *
     * public function waiter()
     * {
     *     return $this->belongsTo(Waiter::class, 'WaiterCode', 'WaiterCode');
     * }
     *
     * public function table()
     * {
     *     return $this->belongsTo(Table::class, 'TableCode', 'TableCode');
     * }
     */

    // You can add convenience accessors if needed, e.g. formatted amount:
    public function getFormattedAmountAttribute(): string
    {
        return number_format((float) $this->Amount, 2);
    }
}
