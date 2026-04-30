<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FB_KOTHead extends Model
{
    use HasFactory;

    protected $table = 'FB_KOTHead'; // exact same name

    protected $fillable = [
        'KOTNo',
        'KOTDate',
        'MemberID',
        'IssueNo',
        'WaiterCode',
        'TableCode',
        'PAX',
        'RefNo',
        'ModeOfPayment',
        'OpeningBalance',
        'ClosingBalance',
        'CreationDate',
        'ModificationDate',
        'UserCode',
        'LocationCode',
        'YearCode',
    ];

    public $timestamps = true;
public static function getNextKOTNo($locationCode, $yearCode)
{
    return DB::transaction(function () use ($locationCode, $yearCode) {
        // Lock only the relevant rows for this location & year
        $maxKOT = self::where('LocationCode', $locationCode)
            ->where('YearCode', $yearCode)
            ->lockForUpdate()
            ->max(DB::raw('CAST(KOTNo AS INT)'));

        // Increment safely
        $nextKOT = ($maxKOT ?? 0) + 1;

        return $nextKOT;
    });
}
    protected $casts = [
        'KOTDate' => 'datetime',
        'CreationDate' => 'datetime',
        'ModificationDate' => 'datetime',
        'OpeningBalance' => 'decimal:2',
        'ClosingBalance' => 'decimal:2',
    ];
    public function kotItems()
{
    return $this->hasMany(FB_KOTBody::class, 'KOTNo', 'KOTNo');
}

}
