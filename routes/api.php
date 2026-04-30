<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryTypeController;
use Laravel\Passport\Passport;
use App\Http\Controllers\api\v1\MemberController;
use App\Http\Controllers\api\v1\MemberAccountController;
use App\Http\Controllers\api\v1\RoomBookingController;
use App\Http\Controllers\api\v1\BanquetBookingController;
use App\Http\Controllers\api\v1\PaymentController;
use App\Http\Controllers\api\v1\TeeBookingController;
use App\Http\Controllers\api\v1\FeedbackController;
use App\Http\Controllers\api\v1\EventController;
use App\Http\Controllers\api\v1\FacilityController;
use App\Http\Controllers\api\v1\GameTypeController;
use App\Http\Controllers\api\v1\FacilitySlotsController;
use App\Http\Controllers\api\v1\MMRRegistrationController;
use App\Http\Controllers\api\v1\TableBookingController;
use App\Http\Controllers\api\v1\AppController;
use App\Http\Controllers\api\v1\EventAdminController;
use App\Http\Controllers\api\v1\EventSeatsController;
use App\Http\Controllers\api\v1\FinancialInsightController;
use App\Http\Controllers\api\v1\CentralizedPaymentController;
use App\Http\Controllers\api\v1\PaymentGatewayAdminController;
use App\Http\Controllers\api\v1\PaymentWebhookController;
use App\Http\Middleware\EnsureAdminApiUser;
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

Route::post('/payment/response', [PaymentController::class, 'handleResponse']);
Route::get('/payment/response', [PaymentController::class, 'handleResponse']);
Route::post('/payments/webhooks/{gateway}', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook');

Route::get('/dashboard/config', [AppController::class, 'dashboardConfig']);
Route::get('/drawer/config', [AppController::class, 'geDrawerConfig']);

Route::get('/admin/login/config', [AppController::class, 'getAdminTabConfig']);

Route::post('/block-rooms', [RoomBookingController::class, 'blockRooms']);
Route::post('/block-venues', [BanquetBookingController::class, 'blockVenues']);
Route::post('/benquet/update/offline-booking', [BanquetBookingController::class, 'updateBookingNo']);
Route::get('/benquet/get/offline-booking', [BanquetBookingController::class, 'getBookingsWithZeroBookingNo']);

Route::post('/room/update/offline-booking', [RoomBookingController::class, 'updateBookingNo']);
Route::get('/room/get/offline-booking', [RoomBookingController::class, 'getBookingsWithZeroBookingNo']);

Route::get('/test-fcm', function () {
    $deviceToken = 'cectn_op8EiAlytP0LU5kI:APA91bFnEL7YMnWzBwNSRuv_kuoM_-O0WV6oe1s_rYHHy8O1dg1MWmMh4e4ZfpOOXBr8oLIU6Sh8-h-vPPS4nEYIrs8T325S98vslc7GhqHLO5SXLI4RLjM';
//  $deviceToken = 'd0NhVRleTFqupIfh7PQ-k6:APA91bHm0XGUphTROODeXGk6Kg_ey0X0t-G_npp1wZded2T6rb9HVto_f1yi7TLofN9GyAeCRak0IxAoWQPw-_bl2ha5sqUTNKimgW2lgcVWC3u76a3MlH4';
    $title = "Test Notification";
    $body  = "This is a test from Laravel FCM v1 API";

    try {
        $serverKey = \App\Http\Controllers\Admin\PushNotifications::getAccessToken();

        $notificationData = (object)[
            'title' => $title,
            'short_descriptions' => $body,
            'image' => null,
            'id' => 0
        ];

        $response = \App\Services\FCMService::sendFCMMessage(
            $notificationData,
            $deviceToken,
            $serverKey
        );

        return response()->json([
            'success' => true,
            'message' => 'Sent test notification',
            'response' => json_decode($response, true)
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
});

Route::get('/profile_pictures/{filename}', function ($filename) {
    $path = public_path('profile_pictures/' . $filename);

    if (!File::exists($path)) {
        abort(404);
    }

    return response()->file($path);
});
Route::get('/category-types', [CategoryTypeController::class, 'show']);

Route::group(['namespace' => 'App\Http\Controllers\api\v1'], function () {
    Route::group(['prefix' => 'auth', 'namespace' => 'auth'], function () {
        Route::post('/login', 'LoginController@login')->name('app_login');
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
    Route::get('/notification/{id}',[MemberController::class, 'getNotificationById']);
    Route::get('/card-recharge-response', [MemberController::class, 'CardChargeResponse']);
    
      
    Route::group(['prefix' => 'member', 'middleware' => 'auth:api'], function () {
        Route::get('/profile',[MemberController::class, 'member_profile_get']);
        Route::get('/member-receipt',[MemberController::class, 'getMemberReceipts']);
        Route::get('/otp',[MemberController::class, 'getOTP']);
        Route::post('/upload-profile',[MemberController::class, 'uploadProfile']);
           Route::get('/verify/{id}',[MemberController::class, 'verifyMember']);
        Route::get('/card_balance',[MemberController::class, 'getCardBalance']);
        Route::get('/account_summary',[MemberController::class, 'getAccountSummary']);
        Route::post('/statement',[MemberController::class, 'getStatement']);
         Route::get('/transation/summery',[MemberController::class, 'getteansationSummery']);
        Route::post('/invoice_transaction',[MemberAccountController::class, 'getMemberAccountDetails']);
        Route::post('/invoice_transaction_filter',[MemberAccountController::class, 'filterInvoiceTransaction']);
        Route::post('/invoice_transaction_download',[MemberAccountController::class, 'downloadMemberAccountLedger']);
        Route::post('/transaction',[MemberController::class, 'getTransactions']);
        Route::post('/transaction_filter',[MemberController::class, 'getTransactionFilter']);
        Route::post('/transaction/filter',[MemberController::class, 'getTransactionFilterNew']);
        Route::get('/financial/home-dashboard', [FinancialInsightController::class, 'memberHomeDashboard']);
        Route::post('/financial/statement-dashboard', [FinancialInsightController::class, 'statementDashboard']);
        Route::post('/financial/recharge-dashboard', [FinancialInsightController::class, 'rechargeDashboard']);
        Route::post('/financial/ask-ai', [FinancialInsightController::class, 'askAi']);
        Route::get('/club/info',[MemberController::class, 'clubInfo']);
        Route::post('/transaction_download',[MemberController::class, 'transactionDownload']);
        Route::post('/change_password', [MemberController::class, 'change_password']);
        Route::post('/create_pay_order', [MemberController::class, 'createPayOrder']); 
        Route::post('/create_invoice_pay_order', [MemberController::class, 'createBillPayOrder']); 
        Route::post('/card_recharge_response', [MemberController::class, 'processPayment']);
        Route::post('/payment_response', [MemberController::class, 'invoicePaymentResponse']);
        Route::post('/feedback', [MemberController::class, 'sendFeedbackOnMail']);
        Route::post('/delete-account', [MemberController::class, 'deleteAccount']);  
        Route::get('/room/transaction', [RoomBookingController::class, 'room_traction']); 
        Route::get('/room-availability', [RoomBookingController::class, 'availability']);
        
        
           Route::get('/feedback/types-with-categories', [FeedbackController::class, 'typesWithCategories']);
        Route::post('/feedback/submit', [FeedbackController::class, 'submit']);
        Route::post('/payments/initiate', [CentralizedPaymentController::class, 'initiatePayment']);
        Route::post('/payments/verify', [CentralizedPaymentController::class, 'verifyPayment']);
        Route::get('/payments/status/{reference}', [CentralizedPaymentController::class, 'transactionStatus']);
        Route::post('/payments/retry/{reference}', [CentralizedPaymentController::class, 'retryPayment']);
        
        
        Route::get('/room/transaction/details/{id}', [RoomBookingController::class, 'room_traction_details']); 
        Route::post('/get/rooms', [RoomBookingController::class, 'get_rooms']); 
        Route::get('/get/occupant', [RoomBookingController::class, 'get_occupant']); 
        Route::get('/get/SOP', [RoomBookingController::class, 'get_SOP']); 
        Route::get('/get/setting', [RoomBookingController::class, 'get_setting']); 
        Route::post('/get/room/charge', [RoomBookingController::class, 'get_room_charge']);
        Route::post('get/avaiable/rooms', [RoomBookingController::class, 'getRooms']);
         Route::post('room/payment/initiate', [RoomBookingController::class, 'initiatePayment']);
         Route::post('room/payment/update', [RoomBookingController::class, 'updatePayment']);
        Route::post('/store/in/summary', [RoomBookingController::class, 'store_in_summary']); 
        Route::get('/get/summary', [RoomBookingController::class, 'get_summary']); 
        Route::get('/booking/checkout/{id}', [RoomBookingController::class, 'booking_checkout']); 
        Route::get('/room/booking/cancel/{id}', [RoomBookingController::class, 'room_cancel']); 
        Route::get('/room/card/empty/{id}', [RoomBookingController::class, 'empty_card']); 
        Route::get('/room/card/item/remove/{id}', [RoomBookingController::class, 'cancel_room_item']); 
        Route::post('/update/room/payment', [RoomBookingController::class, 'update_payment']); 
        
         Route::get('/banquet-availability', [BanquetBookingController::class, 'availability']);
        Route::get('/get/banquest/SOP', [BanquetBookingController::class, 'get_SOP']);
   Route::post('/get/venue-menu', [BanquetBookingController::class, 'get_menuVenue']);
        Route::get('/get/venue', [BanquetBookingController::class, 'get_venue']);
        Route::get('/get/session', [BanquetBookingController::class, 'get_session']);
        Route::get('/get/function/master', [BanquetBookingController::class, 'get_function_master']);
        Route::get('/banquet/transactions', [BanquetBookingController::class, 'banquet_traction']);
        Route::get('/banquet/transaction/{id}', [BanquetBookingController::class, 'details']);
        Route::post('/banquet/store', [BanquetBookingController::class, 'banquet_store']);
        Route::post('/get/venue/by/session', [BanquetBookingController::class, 'get_venue_by_session']);
        Route::post('/banquet/cancel', [BanquetBookingController::class, 'cancelVenue']);
        Route::post('/update/banquet/payment', [BanquetBookingController::class, 'update_payment']); 
        //tee booking
        Route::get('/get/tee/group', [TeeBookingController::class, 'get_group']); 
        Route::get('/get/tee/group/favorite/{id}', [TeeBookingController::class, 'get_fevroit_group']); 
         Route::post('/create/tee/group', [TeeBookingController::class, 'store_group']); 
         Route::get('/delete/tee/group/{id}', [TeeBookingController::class, 'delete_group']); 
         Route::post('/update/tee/group/{id}', [TeeBookingController::class, 'update_group']); 
         Route::post('/get/tee/setting', [TeeBookingController::class, 'index']); 
          Route::post('/get/tee/settings', [TeeBookingController::class, 'index']); 
         Route::get('/get/sessions', [TeeBookingController::class, 'getSessions']);  
         Route::get('get/tee/bookings', [TeeBookingController::class, 'getMyBookings']);
         Route::get('tee/booking/detail/{id}', [TeeBookingController::class, 'getBookingsDetail']);
        Route::get('/cancel/tee/booking/{id}', [TeeBookingController::class, 'cancelBooking']);  
        Route::post('/book/tee/session', [TeeBookingController::class, 'storeTeeBooking']);  
        Route::post('/lock/tee', [TeeBookingController::class, 'LockTee']); 
         
         Route::post('/send/tee/notication', [TeeBookingController::class, 'sendFCM']); 
         
        Route::get('/get/tee/buddies', [TeeBookingController::class, 'get_buddies']); 
        Route::get('/get/buddies/{id}', [TeeBookingController::class, 'search_tee_buddies']); 
         Route::post('/create/tee/buddy', [TeeBookingController::class, 'store_buddy']); 
         Route::get('/delete/tee/buddy/{id}', [TeeBookingController::class, 'delete_buddy']); 
         Route::post('/update/tee/buddy/{id}', [TeeBookingController::class, 'update_buddy']); 
         
         
         Route::get('/events/{id}', [EventController::class, 'show']);
         Route::get('/banner', [EventController::class, 'getBanner']);
         Route::get('/get/events', [EventController::class, 'getEvents']);
        Route::post('/tickets/preview', [EventController::class, 'preview']);
        Route::post('/tickets/process-payment', [EventController::class, 'processPayment']);
        Route::get('/event-bookings', [EventController::class, 'eventBookings']);
        Route::get('/event-bookings/{booking}', [EventController::class, 'showBookings']);
        Route::post('/event/payment-failed', [EventController::class, 'paymentFailed']);
        
        Route::get('/events/get-event-seats/{id}', [EventSeatsController::class, 'getEventSeats']);
        //activity
        Route::post('/favorite-players', [FacilityController::class, 'getFavoritePlayers']);
        Route::get('/get-facility', [FacilityController::class, 'getFacilities']);
        Route::post('/get-facility-slots/{id}', [FacilitySlotsController::class, 'getFacilitySlots']);
        Route::get('/get/activity/sessions', [FacilitySlotsController::class, 'getSessions']);
        Route::get('/get-guest-info/{id}', [FacilitySlotsController::class, 'getGuestInfo']);
        Route::get('/get-game-type/{id}', [GameTypeController::class, 'getGameTypes']);
        Route::post('/create-activity-guest', [GameTypeController::class, 'createActivityGuest']);
          Route::post('/process_activity_payment', [FacilitySlotsController::class, 'processActivityPayment']);
        Route::post('/update-activity-guest/{id}', [GameTypeController::class, 'updateActivityGuest']);
        Route::post('/book-facilities', [FacilitySlotsController::class, 'BookSlots']);
        Route::get('/get-booking-details', [FacilitySlotsController::class,'GetBookingDetails']);
        Route::get('/booking-details/{bookingId}', [FacilitySlotsController::class,'GetBookingDetailById']);
        Route::get('/get-cancellation-amount/{id}', [FacilitySlotsController::class, 'CancelBookingAmount']);
        Route::post('/cancel-booking', [FacilitySlotsController::class, 'CancelBooking']);
        Route::post('/edit-player', [FacilityController::class, 'editFavoritePlayers']);
        Route::get('/facility-banners', [GameTypeController::class, 'getBanners']);
        
        //MMR
        
         Route::get('/get-mmr', [MMRRegistrationController::class, 'getMMRSettings']);
         Route::post('/register/mmr-member', [MMRRegistrationController::class, 'store']);
         Route::get('/mmr-history', [MMRRegistrationController::class, 'history']);
         
         //Table Booking
             Route::get('/table/meals', [TableBookingController::class, 'getMeals']);
                   Route::get('/table/avaiablity', [TableBookingController::class, 'availability']);
    Route::get('/table/times/{meal_id}', [TableBookingController::class, 'getTimes']);
    Route::get('/table/venues', [TableBookingController::class, 'getVenues']);
    Route::get('/tables/{meal_id}', [TableBookingController::class, 'getTables']);
    Route::get('/table/my-upcoming-bookings', [TableBookingController::class, 'myUpComingBookings']);
    

    Route::post('/table/check-availability', [TableBookingController::class, 'checkAvailability']);

    Route::post('/table/create', [TableBookingController::class, 'createBooking']);
    Route::put('/table/update/{id}', [TableBookingController::class, 'updateBooking']);
    Route::post('/table/cancel/{id}', [TableBookingController::class, 'cancelBooking']);
Route::get('/table/details/{id}', [TableBookingController::class, 'getBookingByID']);
    Route::get('/table-bookings', [TableBookingController::class, 'myBookings']);
        
            Route::get('/club-menues',[TableBookingController::class, 'ClubMenues']);
    });
    
        Route::group(['prefix' => 'event',  'middleware' => 'auth:api'], function () {
            
        Route::get('/settings', [EventAdminController::class, 'settings']);
            Route::post('/validate-ticket', [EventAdminController::class, 'validateTicket']);
            Route::post('/book-tickets', [EventAdminController::class, 'adminBookTickets']);
            Route::get('/payment-types', [EventAdminController::class, 'paymentTypes']);
            Route::get('/event-bookings', [EventAdminController::class, 'eventBookings']);//only Admin bookings
            Route::get('/bookings/{id}', [EventAdminController::class, 'memberBookings']);//memberBookings for admin
            Route::get('/event-summary', [EventAdminController::class, 'summary']);
             Route::get('/participant/status/{participantId}/{type}', [EventAdminController::class, 'changeParticipantStatus']);
            Route::post('/validate-qr-ticket', [EventAdminController::class, 'validateQRCode']);
            Route::get('/event-summary-pdf', [EventAdminController::class, 'getEventSummery']);
    });

    Route::group([
        'prefix' => 'admin/payment-gateways',
        'middleware' => ['auth:api', EnsureAdminApiUser::class],
    ], function () {
        Route::get('/', [PaymentGatewayAdminController::class, 'indexGateways']);
        Route::post('/', [PaymentGatewayAdminController::class, 'storeGateway']);
        Route::put('/{gateway}', [PaymentGatewayAdminController::class, 'updateGateway']);
        Route::post('/{gateway}/activate', [PaymentGatewayAdminController::class, 'activateGateway']);
        Route::get('/transactions', [PaymentGatewayAdminController::class, 'transactions']);
        Route::post('/transactions/{transaction}/retry', [PaymentGatewayAdminController::class, 'retryTransaction']);
        Route::get('/webhook-logs', [PaymentGatewayAdminController::class, 'webhookLogs']);
        Route::get('/reports/download', [PaymentGatewayAdminController::class, 'downloadReport']);
    });
});
