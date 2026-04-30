<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\RoomBookingController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\TableBookingController;
use App\Http\Controllers\BanquetBookingController;
use App\Http\Controllers\MMRRegistrationController;
use App\Http\Controllers\ActivityBookingController;
use App\Http\Controllers\OTPController;
use App\Http\Controllers\api\v1\PaymentCheckoutController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::controller(LoginController::class)->group(function () {

    Route::get('sign-up', 'sign_up')->name('sign-up');

    Route::post('student/authentication', 'student_authentication')->name('student.authentication');

    Route::post('student/logout', 'student_logout')->name('student.logout');

});

Route::controller(OTPController::class)->group(function () {

    Route::get('otp/login', 'otp_login')->name('otp.login');

    Route::post('send/otp', 'send_otp')->name('send.otp');

    Route::get('otp/sended', 'otp_sended')->name('otp.sended');
    
    Route::post('verify/otp', 'verify_otp')->name('verify.otp');

    Route::get('otp/login', 'otp_login')->name('otp.login');

    Route::get('forgot/password', 'forgot_password')->name('student.forgot.password');

    Route::get('forgot/password/sended', 'forgot_password_sended')->name('student.forgot.password.sended');

    Route::post('forgot/password/verify/otp', 'forgot_password_verify_otp')->name('student.forgot.password.verify.otp');

    Route::get('make/forgot/password', 'make_forgot_password')->name('student.forgot.password.make');

    Route::post('update/forgot/password', 'update_forgot_password')->name('update.forgot.password');

});

Route::controller(HomeController::class)->group(function () {

    Route::any('permission', 'create_permission');

});

Route::get('payments/checkout/{transaction}', [PaymentCheckoutController::class, 'showCheckout'])
    ->name('payments.checkout');
Route::match(['get', 'post'], 'payments/callback/{gateway}/{transaction}', [PaymentCheckoutController::class, 'handleCallback'])
    ->name('payments.callback');

Route::group(['middleware'=>['student.auth']],function(){

    Route::group(['prefix' => 'member'], function () {

        // Member Routes
        Route::controller(HomeController::class)->group(function () {

            Route::get('dashboard', 'student_dashboard')->name('student.dashboard');

            Route::get('bill/payments', 'bill_payments')->name('bill.payments');

            Route::post('pay/bill/payments', 'pay_card_recharge_bill_payments')->name('pay.bill.payment');
            
            Route::get('recharge', 'recharge')->name('recharge');

            Route::post('pay/card/recharge', 'pay_card_recharge_bill_payments')->name('pay.card.recharge');

            Route::get('student/profile', 'student_profile')->name('student.profile');
            
            Route::post('student/update/profile', 'student_update_profile')->name('student.update.profile');

            Route::get('student/change/password', 'student_change_password')->name('student.change.password');

            Route::post('student/update/password', 'student_update_password')->name('student.update.password');             

        });

        // Room Routes
        Route::controller(RoomBookingController::class)->group(function () {

            Route::get('room/booking', 'room_booking')->name('room.booking');

            Route::get('room/details/{id}', 'room_details')->name('room.details');

            Route::get('room/booking/card', 'room_booking_card')->name('room-booking.card');

            Route::post('remove-room-from-card', 'remove_room_from_card')->name('remove.room.from.card');

            Route::get('room/booking/summary', 'room_booking_summary')->name('room-booking.summary');
            
            Route::post('room/booking/check/occupant', 'check_occupant_room')->name('check.occupant.room');

            Route::post('check/room/availability', 'check_room_availability')->name('room-booking.check.availability');

            Route::post('store/card', 'store_card')->name('store.card');

            Route::post('cancel/room/item', 'cancel_room_item')->name('cancel.room.item');

            Route::post('get/room/item', 'get_room_item_front')->name('get.room.item.front');

            Route::get('empty/card/{booking_number}', 'empty_card')->name('empty.card');

            Route::get('checkout/card/{booking_number}', 'checkout_card')->name('checkout.card');

            Route::get('room/transactions', 'room_transactions')->name('room.transaction');

            Route::get('room/booking/details/{booking_id}', 'room_booking_details')->name('room.booking.details');

            Route::get('room/booking/cancel/{booking_id}', 'room_booking_cancel')->name('room.booking.cancel');

            Route::get('room/booking/download/{booking_id}', 'room_details_download')->name('room.details.download');

            Route::post('cancelRoom', 'cancelRoom')->name('cancelRoom');

            Route::post('get-room-item', 'get_room_item')->name('get.room.item');

        });

        // Transaction Routes
        Route::controller(TransactionController::class)->group(function () {
            
            Route::get('transaction', 'index')->name('transaction');  

            Route::get('postpaid/transaction', 'postpaid')->name('postpaid.transaction');  

            Route::get('prepaid/transaction', 'prepaid')->name('prepaid.transaction');  

        });

        // Banquet Booking Routes
        Route::controller(BanquetBookingController::class)->group(function () {

            Route::get('banquet/booking', 'banquet_booking')->name('banquet.booking');

            Route::post('banquet/booking/store', 'banquet_store')->name('banquet.store');

            Route::get('banquet/payment/checkout/{banq_id}', 'banquet_payment_checkout')->name('banquet.payment.checkout');

            Route::post('banquet/booking/check/occupant', 'check_occupant')->name('check.occupant');

            Route::post('get/venue/session', 'get_venue_by_session')->name('get.venue.by.session');

            Route::post('/get/charges', 'get_charges')->name('get.charges');

            Route::get('/append/extra/field', 'append_extra_field')->name('append.extra.field');

            Route::post('/remove/extra/field', 'remove_extra_field')->name('remove.extra.field');

            Route::get('banquet/transactions', 'banquet_transactions')->name('banquet.transaction');

            Route::get('banquet/booking/details/{booking_id}', 'banquet_booking_details')->name('banquet.booking.details');

            Route::get('banquet/booking/cancel/{booking_id}', 'banquet_booking_cancel')->name('banquet.booking.cancel');

            Route::get('banquet/booking/download/{booking_id}', 'banquet_details_download')->name('banquet.details.download');

            Route::post('/banquet/cancel/post', 'cancelVenue')->name('cancelVenue');

            Route::post('/get/booking/venues', 'getBookingVenue')->name('get.booking.venues');
        });

        // TableBooking Routes
        Route::controller(TableBookingController::class)->group(function () {
            
            Route::get('table-booking', 'index')->name('table.booking');
            
            Route::post('get-times', 'get_times')->name('get.times');
            
            Route::post('table-booking/store', 'store')->name('table.booking.store');

            Route::get('table-transaction', 'index_transaction')->name('table.transaction');

        });

        // MMR Registration Routes
        Route::controller(MMRRegistrationController::class)->group(function () {

            Route::get('mmr-registration', 'index')->name('mmr.registration');

            Route::post('mmr-registration/store', 'store')->name('mmr.registration.store');
            
        });

        // Activity Booking Routes
        Route::controller(ActivityBookingController::class)->group(function () {

            Route::get('activity-booking', 'index')->name('activity.booking');

            Route::post('get/facility', 'get_facility')->name('get.facility');

            Route::post('select/facility', 'select_facility')->name('select.facility');

            Route::post('get/slots', 'get_slots')->name('get.slots');

            Route::post('get/game/type', 'get_game_type')->name('get.game.type');

            Route::post('add/game/in/session', 'add_game_in_session')->name('add.game.in.session');

            Route::post('get/summary/card', 'get_summary_card')->name('get.summary.card');

            Route::post('get/game_type', 'get_game_type')->name('get.game_type');

            Route::get('get/guest/list', 'guest_list')->name('get.guest.list');

            Route::post('get/guest/info', 'guest_info')->name('get.guest.info');

            Route::post('favorite/active', 'favorite_active')->name('favorite.active');

            Route::post('remove/player', 'remove_player')->name('remove.player');

            Route::post('store/guest/in/table', 'store_guest_in_table')->name('store.guest.in.table');

            Route::get('checkout/booking/{booking_id}', 'checkout_booking')->name('checkout.booking');
        
            Route::post('modify/guest/list', 'modify_guest_list')->name('get.modify.guest.list');
        
            Route::get('activity/booking/transactions', 'booking_transactions')->name('activity.booking.transactions');

            Route::post('activity/booking/transaction', 'booking_transaction')->name('booking.transaction');

            Route::post('cancel/slot', 'cancel_slot')->name('cancel.slot');

            Route::post('store/guest', 'guest_store')->name('store.guest');

        });

    });

});

// Payment Routes
Route::get('/payment/initiated/{order_id}', [PaymentController::class, 'pay'])->name('billdesk.pay');

Route::post('/payment/response', [PaymentController::class, 'response']);

Route::get('/payment/confirmed/{order_id}', [PaymentController::class, 'payment_confirmed'])->name('payment.confirmed');

Route::get('/', function () {
    return view('auth.student_login');
})->name('student.login');

Route::get('/', function () {
    return view('auth.student_login');
})->name('sign-up');

