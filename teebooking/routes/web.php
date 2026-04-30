<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Response;
/* use App\http\Controllers\LoginController;
use App\http\Controllers\ProfileController;
use App\http\Controllers\RoomController; */
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
//http://localhost/teebooking/#
/* Member Routes */

// Route::get('/', function () {
//     return view('website.home');
// });

Route::group(['namespace' => 'App\Http\Controllers'], function () {
    Route::get('/', 'LoginController@index')->name('index');
    Route::get('/login', 'LoginController@index')->name('index');
    Route::get('/login/{member_id}', 'LoginController@login')->name('login');
    Route::post('/login/admin', 'LoginController@login_admin')->name('login.admin');
    Route::get('/forgot-password', 'LoginController@forgot_password')->name('forgot_password');
    Route::any('/otp-send', 'LoginController@otp_send')->name('otp_send');
    Route::any('/otp-verify', 'LoginController@otp_verify')->name('otp-verify');
    Route::get('/reload-captcha', 'LoginController@reloadCaptcha')->name('reloadCaptcha');

   
    Route::post('/store_tee_booking', 'web\HomeController@store_tee_booking')->name('store_tee_booking');
    Route::post('/store_group_booking', 'web\HomeController@store_group_booking')->name('store_group_booking');
    Route::post('/store_buddy_booking', 'web\HomeController@store_buddy_booking')->name('store_buddy_booking');
    Route::post('/lock_tee_booking', 'web\HomeController@lock_tee_booking')->name('lock-tee-booking');

    Route::post('/store-buddy', 'web\MemberController@store_buddy')->name('store-buddy');
    Route::get('/delete-buddy/{id}', 'web\MemberController@delete')->name('delete-buddy');
    Route::post('/store_group', 'web\MemberController@store_group')->name('store_group');
    Route::get('/delete-group/{id}', 'web\MemberController@delete_group')->name('delete-group');
    
});

Route::get('/get-notification-image/{filename}', function ($filename) {
    $path = public_path('notification/' . $filename);

    if (!file_exists($path)) {
        abort(404, 'Image not found.');
    }

    return Response::file($path);
});

Route::group(['middleware' => 'auth', 'namespace' => 'App\Http\Controllers'], function () {
   
    Route::prefix('member')->group(function () {

        Route::get('tee-booking/{date?}', 'web\HomeController@index')->name('home');
        Route::get('cancel-booking/{id?}', 'web\HomeController@cancel_booking')->name('cancel-booking');
        Route::get('/dashboard', 'LoginController@dashboard')->name('dashboard');
        Route::get('/member-profile', 'web\MemberController@member_profile')->name('member_profile');
        Route::get('/member-edit', 'web\MemberController@member_edit')->name('member_edit');
        Route::post('/member-update', 'web\MemberController@update')->name('member_update');
        Route::get('/member-transactions', 'web\MemberController@member_transactions')->name('member_transactions');
        Route::get('/member-subscription', 'web\MemberController@member_subscription')->name('member_subscription');
        Route::post('/confirm-subscription', 'web\MemberController@subscription_confirm')->name('subscription_confirm');
        Route::post('/subscription-handle', 'web\MemberController@subscriptionCcavRequestHandler')->name('subscription-handle');
        Route::post('/subscription-response', 'web\MemberController@subscriptionResponse')->name('subscription-response');
        Route::get('/member-otp', 'web\MemberController@member_otp')->name('member_otp');
        Route::get('/member-card_recharge', 'web\MemberController@member_card_recharge')->name('member_card_recharge');
        Route::post('/payment/checkout', 'web\MemberController@payment_checkout')->name('payment.checkout');
        Route::post('/razorpay/success', 'web\MemberController@razorpay_success')->name('razorpay.success');
        Route::post('/razorpay/failed', 'web\MemberController@razorpay_failed')->name('razorpay.failed');
        Route::get('/e-transaction/{ref}', 'web\MemberController@etransaction')->name('e-transaction');
        
        Route::post('/confirm-card-recharge', 'web\MemberController@confirm')->name('member_card_recharge-confirm');
        Route::post('/card-recharge-handle', 'web\MemberController@ccavRequestHandler')->name('card-recharge-handle');
        Route::post('/card-recharge-response', 'web\MemberController@CardChargeResponse')->name('card-recharge-response');
        Route::get('/member-change_password', 'web\MemberController@member_change_password')->name('member_change_password');
        Route::get('/get-otp', 'web\MemberController@get_otp')->name('get-otp');
        Route::get('/profile', 'ProfileController@index')->name('profile');
        Route::match(['GET', 'POST'], '/change-password', 'ProfileController@changePassword')->name('changePassword');

        Route::get('/rooms', 'RoomController@index')->name('rooms');
        //Route::get('/room-details/{id}', 'RoomController@roomDetails')->name('roomDetails');

        Route::get('/room-details/{id}', 'RoomController@roomDetails')->name('roomDetails');

        Route::get('/validate-room-booking', 'RoomController@validateRoomBooking');

        Route::post('/add-to-list', 'RoomController@addToList')->name('addToList');

        Route::post('/add-occupants-to-list', 'RoomController@addOccupantsToList')->name('addOccupantsToList');

        Route::get('/delete-occupant/{id}', 'RoomController@deleteOccupantsFromList')->name('deleteOccupantsFromList');

        // Route::get('/checkout', 'RoomController@checkout')->name('checkout');
        Route::get('/checkout', 'CheckoutController@index')->name('checkout');
        Route::post('/checkout', 'CheckoutController@store')->name('checkoutStore');

        Route::get('/logout', 'LoginController@logout')->name('logout');

        Route::get('/banquet/form/old', 'web\MemberController@banquet_form')->name('banquet.form.old');
        Route::get('/banquet/transaction', 'web\MemberController@banquet_traction')->name('banquet.traction');
        Route::get('/banquet/details/{id}', 'web\MemberController@banquet_details')->name('banquet.details');
        Route::get('/banquet/details/download/{id}', 'web\MemberController@banquet_details_download')->name('banquet.details.download');
        Route::get('/banquet-availability', 'web\MemberController@banquet_ability')->name('banquet.ability');
        Route::get('/banquet/cancel/{id}', 'web\MemberController@banquet_cancel')->name('banquet.cancel');
        Route::get('/banquet/cancel/post/{id}', 'web\MemberController@cancelVenue')->name('cancelVenue');
        Route::post('/get/booking/venue', 'web\MemberController@getBookingVenue')->name('get.booking.venues');
        Route::post('/check/occupant', 'web\MemberController@check_occupant')->name('check.occupant');
        Route::get('/append/extra/field', 'web\MemberController@append_extra_field')->name('append.extra.field');
        Route::post('/remove/extra/field', 'web\MemberController@remove_extra_field')->name('remove.extra.field');        
        
        
        Route::get('/banquet/payment/checout/{id}', 'web\MemberController@banquet_payment_checkout')->name('banquet.payment.checout');

        // New Banquet Form
        Route::get('/banquet/form', 'web\BanquetController@booking_form')->name('banquet.form');
        Route::get('/append/extra/field', 'web\BanquetController@append_extra_field')->name('append.extra.more.field');
        Route::post('/remove/extra/field', 'web\BanquetController@remove_extra_field')->name('remove.extra.more.field');
        Route::post('/get/venue/session', 'web\BanquetController@get_venue_by_session')->name('get.venue.by.session');
        Route::post('/get/charges', 'web\BanquetController@get_charges')->name('get.charges');
        Route::post('/get/venue/pax', 'web\BanquetController@get_venue_pax_new')->name('get.venue.pax');
        Route::post('/banquet/store', 'web\BanquetController@banquet_store')->name('banquet.store');


        // Room Booking
        
        Route::get('/room-booking', 'web\RoomBookingController@booking_check_availability')->name('room-booking.check.availability');
        Route::get('/room-booking/summary', 'web\RoomBookingController@booking_summary')->name('room-booking.summary');

        Route::get('/room-booking/form', 'web\RoomBookingController@booking_form')->name('room-booking.form');
        Route::get('/append/extra/field/room', 'web\RoomBookingController@append_extra_field')->name('append.extra.field.room');
        Route::get('/room-booking/transaction', 'web\RoomBookingController@room_traction')->name('room.traction');
        Route::get('/room-booking/details/{id}', 'web\RoomBookingController@room_details')->name('room.booking.details');
        Route::get('/room-booking/cancel/{id}', 'web\RoomBookingController@room_cancel')->name('room.booking.cancel');

        Route::post('/check/occupant/room', 'web\RoomBookingController@check_occupant_room')->name('check.occupant.room');
        Route::post('/store/card', 'web\RoomBookingController@store_card')->name('store.card');
        Route::get('/checkout/card/{id}', 'web\RoomBookingController@checkout_card')->name('checkout.card');
        Route::get('/room/details/download/{id}', 'web\RoomBookingController@room_details_download')->name('room.details.download');
        Route::post('/room/cancel/post', 'web\RoomBookingController@cancelRoom')->name('cancelRoom');
        Route::post('/get/room/item', 'web\RoomBookingController@room_item')->name('get.room.item');
        Route::get('/empty/card/{id}', 'web\RoomBookingController@empty_card')->name('empty.card');

        Route::post('/cancel/room/item', 'web\RoomBookingController@cancel_room_item')->name('cancel.room.item');
        Route::post('/get/room/item/front', 'web\RoomBookingController@get_room_item_front')->name('get.room.item.front');

        Route::post('/cancel/room/item/booking', 'web\RoomBookingController@cancel_room_item_booking')->name('cancel.room.item.booking');

    });
});

Route::get('/rooms-ui', function () {
    return view('member.rooms-ui');
});

/* Route::get('/login', [LoginController::class, 'index'])->name('index');
Route::post('/login', [LoginController::class, 'login'])->name('login');
Route::get('/reload-captcha', [LoginController::class, 'reloadCaptcha'])->name('reloadCaptcha');

Route::group(['middleware' => 'auth'], function () {

    Route::get('/dashboard', [LoginController::class, 'dashboard'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::match(['GET', 'POST'], '/change-password', [ProfileController::class, 'changePassword'])->name('changePassword');

    Route::get('/rooms', [RoomController::class, 'index'])->name('rooms');
    Route::get('/room-details', [RoomController::class, 'roomDetails'])->name('roomDetails');

    Route::get('/logout', [LoginController::class, 'logout'])->name('logout');
});
 */


Route::get('/clear-cache', 'App\Http\Controllers\web\MemberController@clearCache');

Route::get('/webhook/payload', 'App\Http\Controllers\web\MemberController@webhook_payload')->name('webhook.payload');

Route::post('/razorpay/callback', 'App\Http\Controllers\web\MemberController@razorpay_callback')->name('payment.response')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/razorpay/cancel', 'App\Http\Controllers\web\MemberController@razorpay_callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::group(['middleware' => 'auth', 'namespace' => 'App\Http\Controllers'], function () {
   
    Route::prefix('superadmin')->group(function () {

        Route::get('/dashboard', 'LoginController@admin_dashboard')->name('superadmin.dashboard');

        Route::get('/staff/list', 'LoginController@super_admin_dashboard')->name('main.superadmin.dashboard');

        Route::controller(AdminController::class)->group(function () {

            // Venue Master

            Route::get('venue-master', 'venue_master')->name('venue.master');

            Route::get('venue-master/add', 'venue_master_add')->name('venue.master.add');

            Route::post('venue-master/store', 'venue_master_store')->name('venue.master.store');

            Route::get('venue-master/status/{id}', 'venue_master_status')->name('venue.master.status');

            Route::get('venue-master/edit/{id}', 'venue_master_edit')->name('venue.master.edit');

            Route::post('venue-master/update', 'venue_master_update')->name('venue.master.update');
           
            Route::get('venue-master/delete/{id}', 'venue_master_delete')->name('venue.master.delete');


            // Venue Group

            Route::get('venue-group', 'venue_group')->name('venue.group');

            Route::get('venue-group/add', 'venue_group_add')->name('venue.group.add');

            Route::post('venue-group/store', 'venue_group_store')->name('venue.group.store');

            Route::get('venue-group/status/{id}', 'venue_group_status')->name('venue.group.status');

            Route::get('venue-group/edit/{id}', 'venue_group_edit')->name('venue.group.edit');

            Route::post('venue-group/update', 'venue_group_update')->name('venue.group.update');
           
            Route::get('venue-group/delete/{id}', 'venue_group_delete')->name('venue.group.delete');


            // Venue Pax

            Route::get('venue-pax', 'venue_pax')->name('venue.pax');

            Route::get('venue-pax/add', 'venue_pax_add')->name('venue.pax.add');

            Route::post('venue-pax/store', 'venue_pax_store')->name('venue.pax.store');

            Route::get('venue-pax/status/{id}', 'venue_pax_status')->name('venue.pax.status');

            Route::get('venue-pax/edit/{id}', 'venue_pax_edit')->name('venue.pax.edit');

            Route::post('venue-pax/update', 'venue_pax_update')->name('venue.pax.update');
           
            Route::get('venue-pax/delete/{id}', 'venue_pax_delete')->name('venue.pax.delete');


            // Session Master
            
            Route::get('session-master', 'session_master')->name('session.master');

            Route::get('session-master/add', 'session_master_add')->name('session.master.add');

            Route::post('session-master/store', 'session_master_store')->name('session.master.store');

            Route::get('session-master/edit/{id}', 'session_master_edit')->name('session.master.edit');

            Route::post('session-master/update', 'session_master_update')->name('session.master.update');
           
            Route::get('session-master/delete/{id}', 'session_master_delete')->name('session.master.delete');

            Route::get('session-master/status/{id}', 'session_master_status')->name('session.master.status');


            // Occupant Master
            
            Route::get('occupant-master', 'occupant_master')->name('occupant.master');

            Route::get('occupant-master/add', 'occupant_master_add')->name('occupant.master.add');

            Route::post('occupant-master/store', 'occupant_master_store')->name('occupant.master.store');

            Route::get('occupant-master/edit/{id}', 'occupant_master_edit')->name('occupant.master.edit');

            Route::post('occupant-master/update', 'occupant_master_update')->name('occupant.master.update');
           
            Route::get('occupant-master/delete/{id}', 'occupant_master_delete')->name('occupant.master.delete');

            Route::get('occupant-master/status/{id}', 'occupant_master_status')->name('occupant.master.status');


            // Venue Charges
            
            Route::get('venue-charges', 'venue_charges')->name('venue.charge');

            Route::get('venue-charges/add', 'venue_charges_add')->name('venue.charge.add');

            Route::post('venue-charges/store', 'venue_charges_store')->name('venue.charge.store');

            Route::get('venue-charges/edit/{id}', 'venue_charges_edit')->name('venue.charge.edit');

            Route::post('venue-charges/update', 'venue_charges_update')->name('venue.charge.update');
           
            Route::get('venue-charges/delete/{id}', 'venue_charges_delete')->name('venue.charge.delete');


            // Venue Block
            
            Route::get('venue-block', 'venue_block')->name('venue.block');

            Route::get('venue-block/add', 'venue_block_add')->name('venue.block.add');

            Route::get('venue-block/multi/add', 'venue_block_multi')->name('multi.venue.block');

            Route::post('venue-block/store', 'venue_block_store')->name('venue.block.store');

            Route::post('venue-block/multi/store', 'venue_block_store_multi')->name('venue.block.multi.store');

            Route::get('venue-block/edit/{id}', 'venue_block_edit')->name('venue.block.edit');

            Route::post('venue-block/update', 'venue_block_update')->name('venue.block.update');
           
            Route::get('venue-block/delete/{id}', 'venue_block_delete')->name('venue.block.delete');


            // Cancellation Policy
            
            Route::get('cancellation-policy', 'cancellation_policy')->name('cancellation.policy');

            Route::get('cancellation-policy/add', 'cancellation_policy_add')->name('cancellation.policy.add');

            Route::post('cancellation-policy/store', 'cancellation_policy_store')->name('cancellation.policy.store');

            Route::get('cancellation-policy/edit/{id}', 'cancellation_policy_edit')->name('cancellation.policy.edit');

            Route::post('cancellation-policy/update', 'cancellation_policy_update')->name('cancellation.policy.update');
           
            Route::get('cancellation-policy/delete/{id}', 'cancellation_policy_delete')->name('cancellation.policy.delete');


            // Function Master

            Route::get('function-master', 'function_master')->name('function.master');

            Route::get('function-master/add', 'function_master_add')->name('function.master.add');

            Route::post('function-master/store', 'function_master_store')->name('function.master.store');

            Route::get('function-master/status/{id}', 'function_master_status')->name('function.master.status');

            Route::get('function-master/edit/{id}', 'function_master_edit')->name('function.master.edit');

            Route::post('function-master/update', 'function_master_update')->name('function.master.update');
           
            Route::get('function-master/delete/{id}', 'function_master_delete')->name('function.master.delete');


            // Category Master
            
            Route::get('category-master', 'category_master')->name('category.master');

            Route::get('category-master/add', 'category_master_add')->name('category.master.add');

            Route::post('category-master/store', 'category_master_store')->name('category.master.store');

            Route::get('category-master/edit/{id}', 'category_master_edit')->name('category.master.edit');

            Route::post('category-master/update', 'category_master_update')->name('category.master.update');
           
            Route::get('category-master/delete/{id}', 'category_master_delete')->name('category.master.delete');

            Route::get('category-master/status/{id}', 'category_master_status')->name('category.master.status');


            // Category Type
            
            Route::get('category-type', 'category_type')->name('category.type');

            Route::get('category-type/add', 'category_type_add')->name('category.type.add');

            Route::post('category-type/store', 'category_type_store')->name('category.type.store');

            Route::get('category-type/edit/{id}', 'category_type_edit')->name('category.type.edit');

            Route::post('category-type/update', 'category_type_update')->name('category.type.update');
           
            Route::get('category-type/delete/{id}', 'category_type_delete')->name('category.type.delete');

            Route::get('category-type/status/{id}', 'category_type_status')->name('category.type.status');


            // Room Category
            
            Route::get('room-category', 'room_category')->name('room.category');

            Route::get('room-category/add', 'room_category_add')->name('room.category.add');

            Route::post('room-category/store', 'room_category_store')->name('room.category.store');

            Route::get('room-category/edit/{id}', 'room_category_edit')->name('room.category.edit');

            Route::post('room-category/update', 'room_category_update')->name('room.category.update');
           
            Route::get('room-category/delete/{id}', 'room_category_delete')->name('room.category.delete');

            Route::get('room-category/status/{id}', 'room_category_status')->name('room.category.status');


            // Room Charges Master
            
            Route::get('room-charges-master', 'room_charges_master')->name('room.charges.master');

            Route::get('room-charges-master/add', 'room_charges_master_add')->name('room.charges.master.add');

            Route::post('room-charges-master/store', 'room_charges_master_store')->name('room.charges.master.store');

            Route::get('room-charges-master/edit/{id}', 'room_charges_master_edit')->name('room.charges.master.edit');

            Route::post('room-charges-master/update', 'room_charges_master_update')->name('room.charges.master.update');
           
            Route::get('room-charges-master/delete/{id}', 'room_charges_master_delete')->name('room.charges.master.delete');

            Route::get('room-charges-master/status/{id}', 'room_charges_master_status')->name('room.charges.master.status');


            // Room Cancellation Policy
            
            Route::get('room-cancellation-policy', 'room_cancellation_policy')->name('room.cancellation.policy');

            Route::get('room-cancellation-policy/add', 'room_cancellation_policy_add')->name('room.cancellation.policy.add');

            Route::post('room-cancellation-policy/store', 'room_cancellation_policy_store')->name('room.cancellation.policy.store');

            Route::get('room-cancellation-policy/edit/{id}', 'room_cancellation_policy_edit')->name('room.cancellation.policy.edit');

            Route::post('room-cancellation-policy/update', 'room_cancellation_policy_update')->name('room.cancellation.policy.update');
           
            Route::get('room-cancellation-policy/delete/{id}', 'room_cancellation_policy_delete')->name('room.cancellation.policy.delete');


            // Room Blocking
            
            Route::get('room-block', 'room_block')->name('room.block');

            Route::get('room-block/add', 'room_block_add')->name('room.block.add');

            Route::post('room-block/store', 'room_block_store')->name('room.block.store');

            Route::get('room-block/edit/{id}', 'room_block_edit')->name('room.block.edit');

            Route::post('room-block/update', 'room_block_update')->name('room.block.update');
           
            Route::get('room-block/delete/{id}', 'room_block_delete')->name('room.block.delete');


            // SOP
            
            Route::get('SOP', 'SOP')->name('SOP');

            Route::get('SOP/edit/{id}', 'SOP_edit')->name('SOP.edit');

            Route::post('SOP/update', 'SOP_update')->name('SOP.update');
           

            Route::get('admin-setting', 'admin_setting')->name('admin.setting');

            Route::post('admin-setting/store', 'admin_setting_store')->name('admin.setting.store');
        

            Route::get('bookings', 'bookings')->name('bookings');

            Route::get('cancel-bookings', 'cancellation_bookings')->name('cancel.bookings');

            Route::get('room-bookings', 'room_bookings')->name('room.bookings');

            Route::get('cancel-room-bookings', 'cancel_room_bookings')->name('cancel.room.bookings');

            Route::get('room-bookings/details/{id}', 'room_booking_details')->name('admin.room.booking.details');


            Route::get('staff/add', 'staff_add')->name('staff.add');

            Route::get('staff/edit/{id}', 'staff_edit')->name('staff.edit');

            Route::post('staff/update', 'staff_update')->name('staff.update');

            Route::post('staff/store', 'staff_store')->name('staff.store');

            Route::get('staff/delete/{id}', 'staff_delete')->name('staff.delete');
        });

    });

});

Route::get('/check/mail', 'App\Http\Controllers\web\MemberController@check_email')->name('check.mail');
Route::get('/php/check/mail', 'App\Http\Controllers\web\MemberController@php_check_email');

