<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IM_ItemMaster extends Model
{
    use HasFactory;

    protected $table = 'IM_ItemMaster'; // Table name

    protected $primaryKey = 'ItemCode'; // Primary Key

    public $timestamps = false; // Since you’re using CreationDate & ModificationDate, not Laravel defaults

    protected $fillable = [
        'ItemCode',
        'Aliasname',
        'Maingroup',
        'ItemGroup',
        'ItemSubGroup',
        'ItemName',
        'Displayas',
        'PurchaseUnit',
        'CP',
        'SaleUnit',
        'SP',
        'Capacity',
        'Saletaxcode',
        'Purchasetaxcode',
        'MaxLevel',
        'ReorderLevel',
        'Status',
        'Scheme',
        'CheckStock',
        'ServiceCharge',
        'OpenItem',
        'EventRate',
        'ClubRate',
        'CompanyRate',
        'HSNCode',
        'MultiplierValue',
        'MultiplierUnitCode',
        'CreationDate',
        'ModificationDate',
        'ModifierAllow',
        'UserCode',
        'DepreciationRate',
        'PrinterCode',
    ];
}
