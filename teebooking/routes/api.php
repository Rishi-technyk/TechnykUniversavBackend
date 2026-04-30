<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryTypeController;
use Laravel\Passport\Passport;
use App\Http\Controllers\api\v1\MemberController;
use App\Http\Controllers\api\v1\MemberAccountController;
use App\Http\Controllers\api\v1\WebhookController;
use App\Http\Controllers\api\v1\RoomBookingController;
use App\Http\Controllers\api\v1\BanquetBookingController;
use App\Http\Controllers\api\v1\auth\WaiterLoginController;
use App\Http\Controllers\api\v1\TableController;
use App\Http\Controllers\api\v1\StaffController;
use App\Http\Controllers\api\v1\OrderController;
use App\Http\Middleware\TokenAuthenticate;
use Illuminate\Support\Facades\Storage;
use App\Models\FB_KOTModifier;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/get-modifier', function () {
    $modifiers = FB_KOTModifier::pluck('DisplayAs')->toArray();

    return response()->json([
        'status' => true,
        'data' => $modifiers
    ]);
});
Route::get('/profile_pictures/{filename}', function ($filename) {
    $path = public_path('profile_pictures/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
Route::get('/icon/{filename}', function ($filename) {
    // Full path in storage/app/public/icons
    $path = storage_path('app/public/icons/' . $filename);

    if (!File::exists($path)) {
        abort(404, 'File not found.');
    }

    // Return the file as a response
    return response()->file($path);
});
Route::get('/category-types', [CategoryTypeController::class, 'show']);
Route::get('/policies', [CategoryTypeController::class, 'policies']);

Route::get('/locations', [CategoryTypeController::class, 'getAllLocations']);
Route::get('validate/table-qr', [TableController::class, 'getValidateQR']);

Route::group(['namespace' => 'App\Http\Controllers\api\v1'], function () {
    // Route::post('/login', 'LoginController@login')->name('login');
    Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
        Route::post('/login', 'LoginController@login')->name('login');
        Route::post('/send_login_otp', 'LoginController@send_login_otp')->name('send_login_otp');
        Route::post('/verify_login_otp', 'LoginController@verify_login_otp')->name('verify_login_otp');
        Route::post('/forgot-password', 'LoginController@send_otp')->name('send_otp');
        Route::post('/verify-otp', 'LoginController@verify_otp')->name('verify_otp');
    });
    Route::get('/documents',[MemberController::class, 'getDocuments']);
    Route::get('/menus',[MemberController::class, 'getMenus']);
    Route::get('/affilated_clubs',[MemberController::class, 'getAffilatedClubs']);
    Route::get('/config',[MemberController::class, 'getconfig']);
    Route::get('/notifications',[MemberController::class, 'getNotifications']);
Route::post('/razorpay/webhook', [WebhookController::class, 'handleWebhook']);
    
    Route::group(['prefix' => 'member', 'middleware' => 'auth:api'], function () {
        Route::get('/profile',[MemberController::class, 'member_profile_get']);
        Route::get('/member-receipt',[MemberController::class, 'getMemberReceipts']);
        
        //feedback
         Route::post('/feedback',[MemberController::class, 'uploadeFeedback']);
        //profile
        Route::post('/upload-profile',[MemberController::class, 'uploadProfile']);
        Route::get('/otp',[MemberController::class, 'getOTP']);
        Route::get('/card_balance',[MemberController::class, 'getCardBalance']);
        Route::get('/account_summary',[MemberController::class, 'getMemberAccountSummary']);
        Route::post('/delete_account',[MemberController::class, 'deleteAccount']);
        Route::post('/statement',[MemberController::class, 'getStatement']);
        Route::post('/invoice_transaction',[MemberAccountController::class, 'getMemberAccountDetails']);
        Route::post('/invoice_transaction_filter',[MemberAccountController::class, 'filterInvoiceTransaction']);
        Route::post('/invoice_transaction_download',[MemberAccountController::class, 'downloadMemberAccountLedger']);
        Route::post('/transaction',[MemberController::class, 'getTransactions']);
        Route::post('/transaction_filter',[MemberController::class, 'getTransactionFilter']);
        Route::post('/transaction_download',[MemberController::class, 'transactionDownload']);
        Route::post('/change_password', [MemberController::class, 'change_password']);
        Route::post('/create_recharge_pay_order', [MemberController::class, 'create_recharge_pay_order']);
        Route::post('/process_payment', [MemberController::class, 'processPayment'])->name('process_payment');
        Route::post('/create_invoice_pay_order', [MemberController::class, 'create_invoice_pay_order']);
        Route::post('/process_invoice_payment', [MemberController::class, 'processInvoicePayment'])->name('process_invoice_payment');
        Route::get('/config', function () {
    $data = [
        "current_app_version" => "1.7.4",
        "hard_update" => false,
        "current_ios_app_version" => "3.5.3",
        "ios_hard_update" => false,
        "play_store_link" => "https://play.google.com/store/apps/details?id=com.technyk.aepta",
        "app_store_link" => "https://apps.apple.com/in/app/aepta/id6478807985",
        "and_alert_line" => "A new update is available. Would you like to update?",
        "ios_alert_line" => "A new update is available. Would you like to update?"
    ];

    return response()->json([
        'status' => true,
        'message' => 'Configuration fetched succefully.',
        'data' => $data
    ]);
});

Route::get('/room/transaction', [RoomBookingController::class, 'room_traction']); 
        Route::get('/room/transaction/details/{id}', [RoomBookingController::class, 'room_traction_details']); 
        Route::post('/get/rooms', [RoomBookingController::class, 'get_rooms']); 
        Route::get('/get/occupant', [RoomBookingController::class, 'get_occupant']); 
        Route::get('/get/SOP', [RoomBookingController::class, 'get_SOP']);
        
        Route::get('/get/setting', [RoomBookingController::class, 'get_setting']); 
        Route::post('/get/room/charge', [RoomBookingController::class, 'get_room_charge']); 
        Route::post('/store/in/summary', [RoomBookingController::class, 'store_in_summary']); 
        Route::get('/get/summary', [RoomBookingController::class, 'get_summary']); 
        Route::get('/booking/checkout/{id}', [RoomBookingController::class, 'booking_checkout']); 
        Route::get('/room/booking/cancel/{id}', [RoomBookingController::class, 'room_cancel']); 
        Route::get('/room/card/empty/{id}', [RoomBookingController::class, 'empty_card']); 
        Route::get('/get/setting', [RoomBookingController::class, 'get_setting']); 
        Route::get('/room/card/item/remove/{id}', [RoomBookingController::class, 'cancel_room_item']); 
        Route::post('/update/room/payment', [RoomBookingController::class, 'update_payment']); 

        Route::get('/get/banquest/SOP', [BanquetBookingController::class, 'get_SOP']);
        Route::post('/get/venue-menu', [BanquetBookingController::class, 'get_menuVenue']);
        Route::get('/get/venue', [BanquetBookingController::class, 'get_venue']);
        Route::get('/get/session', [BanquetBookingController::class, 'get_session']);
        Route::get('/get/function/master', [BanquetBookingController::class, 'get_function_master']);
        Route::post('/banquet/transactions', [BanquetBookingController::class, 'banquet_traction']);
        Route::get('/banquet/transaction/{id}', [BanquetBookingController::class, 'details']);
        Route::post('/banquet/store', [BanquetBookingController::class, 'banquet_store']);
        Route::post('/get/venue/by/session', [BanquetBookingController::class, 'get_venue_by_session']);
        Route::post('/banquet/cancel', [BanquetBookingController::class, 'cancelVenue']);
        Route::post('/update/banquet/payment', [BanquetBookingController::class, 'update_payment']); 
        
          //food order
        Route::get('/food_groups', [StaffController::class, 'foodGroups']); 
        Route::get('/group/items', [StaffController::class, 'getGroupItems']);
        Route::post('/place-order',[OrderController::class, 'MemberPlaceOrder']);
        Route::get('/orders', [OrderController::class, 'getMemberOrder']);
        Route::get('/get-reciept/{billNo}/{Location}/{Year}', [OrderController::class, 'getReciept'])->where('billNo', '[0-9]+');
        Route::post('/orders/cancel', [OrderController::class, 'cancelMemberOrder']);
    });
});


Route::group(['prefix' => 'waiter'], function () {
    Route::post('/login', [WaiterLoginController::class, 'login'])->name('login');
    Route::post('/verify-otp', [WaiterLoginController::class, 'verifyOTP'])->name('verifyOTP');
    Route::post('/resend-otp', [WaiterLoginController::class, 'resendOTP'])->name('resendOTP');
});

   Route::prefix('staff')->middleware(TokenAuthenticate::class)->group(function () {
        Route::get('/profile', [StaffController::class, 'member_profile_get']);
        Route::get('/home/menu/{type}', [StaffController::class, 'getHomeMenu']);
        Route::get('/home/details/{type}', [StaffController::class, 'getHomeDetails']);
        Route::get('staff/table-qr', [StaffController::class, 'getStaffQR']);
        Route::post('/verify/member', [StaffController::class, 'verifyMember']);
         Route::post('/verify/nfc-card', [StaffController::class, 'verifyNFCCard']);
        Route::post('/verify/running-member', [StaffController::class, 'verifyRunningMember']);
        Route::get('/group/items', [StaffController::class, 'getGroupItems']);
        Route::get('/group/items/search', [StaffController::class, 'searchItems']);
        Route::post('/place-order', [OrderController::class, 'placeOrder'])->name('order.place');
        Route::post('/place-order/{billNo}', [OrderController::class, 'placeOrder'])->where('billNo', '[0-9]+')->name('order.reorder');
        Route::get('/get-reciept/{billNo}/{Location}/{Year}', [OrderController::class, 'getReciept'])->where('billNo', '[0-9]+');
        Route::get('/complete-order/{billNo}/{Location}/{Year}', [OrderController::class, 'completeOrder'])->where('billNo', '[0-9]+');
        Route::get('/kot-remarks', [OrderController::class, 'KOTRemarks']);
        Route::post('/modify-kot', [OrderController::class, 'modifyKOT']);
        Route::post('/get-active-orders', [OrderController::class, 'getActiveOrdersByWaiter']);
        Route::get('/tables', [StaffController::class, 'getTables']);
        Route::get('/get-cart-data', [StaffController::class, 'getWaiters']);
        
         
         
    });
//   Route::prefix('staff')->middleware(TokenAuthenticate::class)->group(function () {
//         Route::get('/profile', [StaffController::class, 'member_profile_get']);
//         Route::get('/home/menu/{type}', [StaffController::class, 'getHomeMenu']);
//         Route::get('staff/table-qr', [StaffController::class, 'getStaffQR']);
//          Route::get('/tables', [StaffController::class, 'getTables']);
//         Route::post('/verify/member', [StaffController::class, 'verifyMember']);
       
//         Route::get('/group/items', [StaffController::class, 'getGroupItems']);
//         Route::get('/group/items/search', [StaffController::class, 'searchItems']);
//           Route::post('/place-order', [OrderController::class, 'placeOrder'])->name('order.place');
//          Route::post('/get-active-orders', [OrderController::class, 'getActiveOrdersByWaiter']);
//           Route::get('/get-cart-data', [StaffController::class, 'getWaiters']);
//             Route::get('/tables', [StaffController::class, 'getTables']);
         
//     });
