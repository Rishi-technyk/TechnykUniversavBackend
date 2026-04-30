<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\FB_KOTHead;
use App\Models\FB_KOTBody;
use App\Models\FB_BillHead;
use App\Models\FB_BillBody;
use App\Models\Member;
use App\Models\IM_ItemMaster;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\KOTSetting;
use App\Models\AC_FinancialYear;
use App\Models\AC_ModeOfPayment;
use App\Models\IM_LocationMaster;
use App\Models\CompanyInfoVendor;
use App\Models\AC_UserMaster;
use Illuminate\Support\Str;
use App\Models\AC_BillHead;

class MiscController extends Controller
{
    
    public function getAllMisc(Request $request){
        
        $All= AC_BillHead::where('Status','=','Active')->get();
        
          return response()->json([
        'status'  => true,
        'data'=>$All,
        'message' => 'This table is already occupied by another member. Please close the current bill before placing a new order.',
    ], 200);
}
}