<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FB_LocationGroupLink extends Model
{
    // Table name
    protected $table = 'FB_LocationGroupLink';

    // No auto-incrementing id (composite keys instead)
    public $incrementing = false;

    // If composite key, Eloquent doesn’t support directly — we’ll handle it manually
    protected $primaryKey = null;

    // Key type
    protected $keyType = 'string';

    // Disable timestamps since you have custom date fields
    public $timestamps = false;

    // Fillable columns
    protected $fillable = [
        'MainGroupCode',
        'GroupCode',
        'LocationCode',
        'CreationDate',
        'ModificationDate',
        'UserCode',
    ];

    // Cast dates to Carbon
    protected $casts = [
        'CreationDate'     => 'datetime',
        'ModificationDate' => 'datetime',
    ];
    
     public function groupMaster()
    {
        return $this->belongsTo(IM_GroupMaster::class, 'GroupCode', 'Code');
    }
   public static function getGroupsByLocation($locationCode)
{
    $groups = self::query()
        ->select(
            'FB_LocationGroupLink.MainGroupCode',
            'FB_LocationGroupLink.LocationCode',
            'FB_LocationGroupLink.GroupCode'
        )
        ->join('IM_GroupMaster', function ($join) {
            $join->on('FB_LocationGroupLink.GroupCode', '=', 'IM_GroupMaster.Code')
                 ->on('FB_LocationGroupLink.MainGroupCode', '=', 'IM_GroupMaster.MainGroupCode');
        })
        ->where('FB_LocationGroupLink.LocationCode', $locationCode)
        ->with(['groupMaster.subGroups' => function ($q) {
            $q->select('Code', 'GroupCode', 'SubgroupDisplyas', 'GSTTaxCode');
        }])
        ->get();

    // Transform for JSON structure
    $data = $groups->map(function ($link) {
        if (!$link->groupMaster) {
            return null;
        }

        $subGroups = $link->groupMaster->subGroups->map(function ($sub) {
            // Count only active items for each subgroup
            $itemCount = \App\Models\IM_ItemMaster::where('ItemSubGroup', $sub->Code)
                ->where('Status', 'Active')
                ->count();

            // Return subgroup only if it has active items
            if ($itemCount > 0) {
                return [
                    'Code' => $sub->Code,
                    'GroupCode' => $sub->GroupCode,
                    'SubgroupDisplyas' => $sub->SubgroupDisplyas,
                    'GSTTaxCode' => $sub->GSTTaxCode,
                    'item_count' => $itemCount,
                ];
            }

            return null;
        })
        ->filter() // Remove subgroups with 0 items
        ->values();

        // Skip group if no valid subgroups remain
        if ($subGroups->isEmpty()) {
            return null;
        }

        return [
            'MainGroupCode' => $link->MainGroupCode,
            'LocationCode' => $link->LocationCode,
            'group_master' => [
                'Code' => $link->groupMaster->Code,
                'GroupDisplyas' => $link->groupMaster->GroupDisplyas,
                'sub_groups' => $subGroups,
            ],
        ];
    })
    ->filter() // Remove null groups
    ->values();

    return $data;
}


    
    // public static function getGroupsByLocation($locationCode)
    // {
    //     $groups = self::query()
    //         ->select('FB_LocationGroupLink.MainGroupCode', 'FB_LocationGroupLink.LocationCode', 'FB_LocationGroupLink.GroupCode')
    //         ->join('IM_GroupMaster', function($join) {
    //             $join->on('FB_LocationGroupLink.GroupCode', '=', 'IM_GroupMaster.Code')
    //                  ->on('FB_LocationGroupLink.MainGroupCode', '=', 'IM_GroupMaster.MainGroupCode');
    //         })
    //         ->where('FB_LocationGroupLink.LocationCode', $locationCode)
    //         ->with(['groupMaster.subGroups' => function($q) {
    //             $q->select('Code', 'GroupCode', 'SubgroupDisplyas', 'GSTTaxCode');
    //         }])
    //         ->get();

    //     // Transform for JSON structure
    //     return $groups->map(function($link) {
    //         return [
    //             'MainGroupCode' => $link->MainGroupCode,
    //             'LocationCode' => $link->LocationCode,
    //             'group_master' => [
    //                 'Code' => $link->groupMaster->Code,
    //                 'GroupDisplyas' => $link->groupMaster->GroupDisplyas,
    //                 'sub_groups' => $link->groupMaster->subGroups->map(function($sub) {
    //                     return [
    //                         'Code' => $sub->Code,
    //                         'GroupCode' => $sub->GroupCode,
    //                         'SubgroupDisplyas' => $sub->SubgroupDisplyas,
    //                         'GSTTaxCode' => $sub->GSTTaxCode,
    //                     ];
    //                 }),
    //             ],
    //         ];
    //     });
    // }
}
