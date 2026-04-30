<?php

use App\Http\Controllers\Admin\PushNotifications;
use Illuminate\Support\Facades\Route;
use App\http\Controllers\Admin\LoginController;
use App\Http\Controllers\Admin\Tee\TeeSessionNameController;
use App\Http\Controllers\Admin\Tee\TeeSessionTimeController;
use App\Http\Controllers\Admin\Tee\SessionController;
use App\Http\Controllers\Admin\Tee\TeeSlotIntervalController;
use App\Http\Controllers\Admin\Tee\TeeHoleController;
use App\Http\Controllers\Admin\Tee\TransportationController;
use App\Http\Controllers\Admin\Tee\CaddyController;
use App\Http\Controllers\Admin\Tee\RentalClubController;
use App\Http\Controllers\Admin\Tee\TeeBookingController;
use App\Http\Controllers\Admin\Tee\TeeSheetController;
use App\Http\Controllers\Admin\Tee\SessionManageController;
use App\Http\Controllers\Admin\Tee\ServicesManageController;
use App\Http\Controllers\Admin\Tee\ConfigController;
use App\Http\Controllers\web\MemberController;

use App\Http\Controllers\Admin\Events;
use App\Http\Controllers\Admin\AdminTicketController;
use App\Http\Controllers\Admin\SeatController;
use App\Http\Controllers\Admin\AdminUsersController;

//use App\http\Controllers\Admin\RoomController;
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


/* Admin Routes */

/* Route::get('/login', [LoginController::class, 'index'])->name('admin.index');
Route::post('/login', [LoginController::class, 'login'])->name('admin.login'); */


Route::group(['namespace' => 'App\Http\Controllers\Admin'], function () {
    Route::get('/login', 'LoginController@index')->name('admin.index');
    Route::post('/login', 'LoginController@login')->name('admin.login');
});


/*
Route::middleware(['admin'])->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('admin.logout');
    Route::get('/dashboard', function () {

        return view('admin.dashboard');
    })->name('admin.dashboard');

    /*Route::get('/room', function () {
        return view('admin.room-view');
    })->name('admin.roomView');
* /

    Route::get('/room', function () {
        return redirect()->route('admin.roomList');
    })->name('admin.room');

});
*/
Route::group(['middleware' => 'admin', 'namespace' => 'App\Http\Controllers\Admin'], function () {
 
    Route::prefix('member')->group(function () {
        Route::get('/', 'MemberController@index')->name('admin.members');
    });
    
    // room
    Route::prefix('room')->group(function () {
        Route::get('/list', 'RoomController@view')->name('admin.roomList');
        Route::post('/add', 'RoomController@addStore')->name('admin.roomAddStore');
        Route::get('/add', 'RoomController@add')->name('admin.roomAdd');

        //price
        Route::prefix('price')->group(function () {
            Route::get('/add', 'RoomPriceController@add')->name('admin.roomPriceAdd');
            Route::post('/add', 'RoomPriceController@store')->name('admin.roomPriceStore');
            Route::get('/list', 'RoomPriceController@list')->name('admin.roomPriceList');
            Route::get('/edit/{roomPrice}', 'RoomPriceController@edit')->name('admin.roomPriceEdit');
            Route::post('/edit/{roomPrice}', 'RoomPriceController@update')->name('admin.roomPriceUpdate');
        });
     

    });

    Route::prefix('tee')->group(function () {
        Route::get('/list', 'App\Http\Controllers\Admin\tee\SheetController@index')->name('admin.list');
      
    });
    
    Route::get('tee/session_manage',[SessionManageController::class, 'index'])->name('session_manage');
    Route::get('tee/service_manage',[ServicesManageController::class, 'index'])->name('service_manage');
    Route::resource('tee/session_names', TeeSessionNameController::class);
    Route::resource('tee/tee-session-times', TeeSessionTimeController::class);
    Route::post('tee/tee-session-times-status',[TeeSessionTimeController::class, 'status_update'])->name('tee-session-times.status');
    Route::resource('tee/sessions', SessionController::class);
    Route::post('tee/status-update',[SessionController::class, 'status_update'])->name('sessions.status-update');
    Route::resource('tee/tee_slot_intervals', TeeSlotIntervalController::class);
    Route::resource('tee/tee_holes', TeeHoleController::class);
    Route::post('tee/tee_holes/status-update',[TeeHoleController::class, 'status_update'])->name('tee_holes.status-update');
    Route::resource('tee/transportations', TransportationController::class);
    Route::post('tee/transportations/status-update',[TransportationController::class, 'status_update'])->name('transportations.status-update');
    Route::resource('tee/caddies', CaddyController::class);
    Route::post('tee/caddies/status-update',[CaddyController::class, 'status_update'])->name('caddies.status-update');
    Route::resource('tee/rental_clubs', RentalClubController::class);
    Route::post('tee/rental_clubs/status-update',[RentalClubController::class, 'status_update'])->name('rental_clubs.status-update');
    Route::resource('tee/tee_bookings', TeeBookingController::class);
    Route::post('tee/tee_bookings/status-update',[TeeBookingController::class, 'status_update'])->name('tee_bookings.status-update');
    Route::get('/admin/tee/tee_bookings/create', 'TeeBookingController@create')->name('admin.tee.tee_bookings.create');
    Route::resource('tee/tee_sheets', TeeSheetController::class);
    Route::post('tee/tee_sheets/status-update',[TeeSheetController::class, 'status_update'])->name('tee_sheets.status-update');
    Route::post('tee/tee_sheets/is-locked-by-admin-status-update',[TeeSheetController::class, 'is_locked_by_admin_status_update'])->name('tee_sheets.is-locked-by-admin-status-update');
    Route::get('tee/tee_sheets/{id}', [TeeSheetController::class, 'show'])->name('tee.tee_sheets.show');
    Route::post('tee/tee_sheets', [TeeSheetController::class, 'show_search'])->name('tee.tee_sheets.show_search');
    Route::post('tee/export_sheets/', [TeeSheetController::class, 'export_tee_sheet'])->name('tee.tee_sheets.export');
   
   
    Route::get('tee/config/', [ConfigController::class, 'index'])->name('tee_bookings.config');
    Route::post('tee/config/store', [ConfigController::class, 'store'])->name('tee_bookings.config.store');
    
    
    Route::get('/logout', 'LoginController@logout')->name('admin.logout');
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
    
    Route::get('/room', function () {
        return redirect()->route('admin.roomList');
    })->name('admin.room');
    //notifications
    Route::get('notifications', [PushNotifications::class,'index'])->name('notifications');

    Route::post('notifications/store', [PushNotifications::class,'store'])->name('store');
    Route::get('notifications/edit/{id}', [PushNotifications::class,'edit'])->name('edit');
    Route::post('notifications/update/{id}',  [PushNotifications::class,'update'])->name('update');
    Route::post('notifications/status',  [PushNotifications::class,'status'])->name('status');
    Route::post('notifications/broadcast',  [PushNotifications::class,'broadcast'])->name('broadcast');
    Route::get('notifications/delete/{id}', [PushNotifications::class,'delete'])->name('delete');
    Route::get('/notifications/{id}/sent-users', [PushNotifications::class, 'showSentUsers'])->name('admin.notifications.sentUsers');
    Route::post('/notifications/resend/{id}', [PushNotifications::class, 'resend'])->name('admin.notifications.resend');
   Route::get('reminders', [PushNotifications::class,'reminders'])
    ->name('reminders');


    
    //events
    
    
     Route::get('events', [Events::class,'index'])->name('events');
     Route::get('/admin/events', [Events::class, 'index'])->name('admin.events');
Route::post('/events/store', [Events::class, 'store'])->name('admin.events.store');
Route::post('/events/status', [Events::class, 'updateStatus'])->name('admin.events.status');
Route::get('/events/edit/{id}', [Events::class, 'edit'])->name('admin.events.edit');
Route::post('/events/update/{id}', [Events::class, 'update'])->name('admin.events.update');
Route::delete('/events/delete/{id}', [Events::class, 'destroy'])->name('admin.events.delete');
Route::get('/events/{id}/tickets',[Events::class, 'tickets'])->name('admin.events.tickets');
    Route::get('/events/{id}/tickets/export',[Events::class, 'exportTickets'])->name('admin.events.tickets.export');
    
    
    Route::get('/events/bookings', [Events::class, 'redirectToLatestEventBookings'])->name('admin.events.bookings.redirect');
  



    //passes
    Route::get('/events/{id}/passes',[Events::class, 'passes'])->name('admin.events.passes');

Route::post('/events/{id}/passes/store',[Events::class,'storePass'])->name('admin.events.passes.store');
Route::get('/events/passes', [Events::class, 'passesLanding'])->name('admin.events.passes.landing');
Route::post('/passes/status',[Events::class,'updatePassStatus'])->name('admin.passes.status');

Route::post('/passes/{id}',[Events::class,'deletePass'])->name('admin.passes.delete');
Route::post('/passes/{id}/update',[Events::class,'updatePass'])->name('admin.passes.update');
});


Route::get('/events/waiters',[Events::class,'getWaiters'])
    ->name('admin.events.waiters.landing');
Route::get('/events/{id}/waiters',[Events::class,'waiters'])->name('admin.events.waiters');

Route::post('/events/{id}/waiters',[Events::class,'updateWaiters'])->name('admin.events.waiters.update');

//create booking
Route::get('/tickets/create/{event}', [AdminTicketController::class,'create'])
->name('admin.events.createbooking');

Route::post('/book-tickets', [AdminTicketController::class,'adminBookTickets']);

Route::get('/payment-types', [AdminTicketController::class,'paymentTypes']);
Route::get('/events/{id}/tickets/landing',[AdminTicketController::class, 'index'])
    ->name('admin.ticket.landing');

//banners
Route::get('/events/banners',[Events::class,'getBanners'])->name('admin.events.banners');
Route::post('/events/banner/update/{id}', [Events::class,'updateBanner'])
    ->name('admin.events.banner.update');
    
    //Seatting
     Route::get('/admin/events/seating', [SeatController::class, 'redirect'])
    ->name('admin.events.seating.redirect');


      Route::get('events/{id}/seating',[SeatController::class,'index'])
    ->name('admin.events.seating.index');
Route::get('/admin/seats/{seat}/booking',
[SeatController::class,'getSeatBooking'])
->name('admin.seats.booking');

Route::post('/seats/toggle',
[SeatController::class,'toggleSeatBlock'])
->name('admin.seats.toggle');

    Route::get('events/{id}/seating/create',[SeatController::class,'create'])
    ->name('admin.events.seating.create');

    Route::post('events/seating/store',[SeatController::class,'store'])
    ->name('admin.events.seating.store');
   
   Route::get('/events/staff',[AdminUsersController::class,'getStaff'])
    ->name('admin.events.staff');

Route::post('/events/staff/store',[AdminUsersController::class,'storeStaff'])
    ->name('admin.events.staff.store');

Route::post('/events/staff/update/{id}',[AdminUsersController::class,'updateStaff'])
    ->name('admin.events.staff.update');

Route::post('/events/staff/status/{id}',[AdminUsersController::class,'statusStaff'])
    ->name('admin.events.staff.status');
    
    Route::get(
'events/{id}/seating/categories',
[SeatController::class,'categories']
)->name('admin.events.seating.categories');

Route::post(
'events/seating/categories/store',
[SeatController::class,'storeCategory']
)->name('admin.events.seating.categories.store');


Route::get('/autocomplete-members', [TeeSheetController::class, 'autocompleteMembers'])->name('autocomplete-members');
Route::get('/autocomplete-buddy', [MemberController::class, 'autocompleteBuddy'])->name('autocomplete-buddy');
