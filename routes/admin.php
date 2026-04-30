<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    UserController,
    RolesController,
    ProfileController,
    BlockRoomController,
    RoomChargesController,
    RoomCategoryController,
    RegistrationController,
    OccupantMasterController,
    RoomCancellationPolicyController,
    RoomBookingController,
    SOPController,
    AdminSettingController,
    VenueMasterController,
    SessionController,
    BanquetOccupantController,
    FunctionMasterController,
    VenueChargesController,
    VenueBlockController,
    CancellationPolicyController,
    BanquetBookingController,
    DocumentController,
    TableVenueController,
    TableMealController,
    TableTimeController,
    TableController,
    TableBookingController,
    MMRRegistrationSettingController,
    ActivityBookingController
};

use App\Http\Controllers\Activity\{
    FacilityController,
    SlotController,
    ActivitySessionController,
    FacilitySlotController,
    BlockSlotController,
    GameTypeController,
    ActivityCancellationPolicyController,
    ActivityOccupantMasterController
};

use App\Http\Controllers\Auth\LoginController;

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

Route::controller(LoginController::class)->group(function () {

    Route::get('/', 'login')->name('login');

    Route::post('/', 'authentication')->name('authentication');

    Route::post('registration', 'registration')->name('registration');

    Route::get('dashboard', 'dashboard')->name('dashboard');

    Route::post('logout', 'logout')->name('logout');

});

Route::middleware(['admin.auth'])->group(function () {

    Route::controller(ProfileController::class)->group(function () {

        Route::get('profile', 'index')->name('profile');

        Route::post('profile/update', 'update')->name('update.profile');

        Route::post('change/password', 'updatePassword')->name('change.password');

    });

    // Users
    Route::controller(UserController::class)->group(function () {

        Route::get('users', 'index')->name('admin.users');

        Route::get('user/create', 'create')->name('admin.user.create');

        Route::post('user/store', 'store')->name('admin.user.store');
        
        Route::get('user/edit/{id}', 'edit')->name('admin.user.edit');

        Route::post('user/update/{id}', 'update')->name('admin.user.update');

        Route::get('user/delete/{id}', 'delete')->name('admin.user.delete');

        Route::get('user/status/{id}', 'status')->name('admin.user.status');

    });

    // Roles
    Route::controller(RolesController::class)->group(function () {

        Route::get('roles', 'index')->name('admin.roles');

        Route::get('role/create', 'create')->name('admin.role.create');

        Route::post('role/store', 'store')->name('admin.role.store');
        
        Route::get('role/edit/{id}', 'edit')->name('admin.role.edit');

        Route::post('role/update/{id}', 'update')->name('admin.role.update');

        Route::get('role/delete/{id}', 'delete')->name('admin.role.delete');

    });

    // Occupant
    Route::controller(OccupantMasterController::class)->group(function () {

        Route::get('occupants', 'index')->name('admin.occupants');

        Route::get('occupant/create', 'create')->name('admin.occupant.create');

        Route::post('occupant/store', 'store')->name('admin.occupant.store');
        
        Route::get('occupant/edit/{id}', 'edit')->name('admin.occupant.edit');
        
        Route::post('occupant/update/{id}', 'update')->name('admin.occupant.update');

        Route::get('occupant/delete/{id}', 'destroy')->name('admin.occupant.delete');

        Route::get('occupant/status/{id}', 'status')->name('admin.occupant.status');

    });

    // Room Category
    Route::controller(RoomCategoryController::class)->group(function () {

        Route::get('room-categories', 'index')->name('admin.room_categories');

        Route::get('room-category/create', 'create')->name('admin.room_category.create');

        Route::post('room-category/store', 'store')->name('admin.room_category.store');
        
        Route::get('room-category/edit/{id}', 'edit')->name('admin.room_category.edit');
        
        Route::post('room-category/update/{id}', 'update')->name('admin.room_category.update');

        Route::get('room-category/delete/{id}', 'destroy')->name('admin.room_category.delete');

        Route::get('room-category/status/{id}', 'status')->name('admin.room_category.status');

    });

    // Room Charges
    Route::controller(RoomChargesController::class)->group(function () {

        Route::get('room-charges', 'index')->name('admin.room_charges');

        Route::get('room-charge/create', 'create')->name('admin.room_charge.create');

        Route::post('room-charge/store', 'store')->name('admin.room_charge.store');
        
        Route::get('room-charge/edit/{id}', 'edit')->name('admin.room_charge.edit');

        Route::post('room-charge/update/{id}', 'update')->name('admin.room_charge.update');

        Route::get('room-charge/delete/{id}', 'destroy')->name('admin.room_charge.delete');

        Route::get('room-charge/status/{id}', 'status')->name('admin.room_charge.status');
    });

    // Block Room
    Route::controller(BlockRoomController::class)->group(function () {

        Route::get('block-rooms', 'index')->name('admin.block_rooms');

        Route::get('block-room/create', 'create')->name('admin.block_room.create');

        Route::post('block-room/store', 'store')->name('admin.block_room.store');

        Route::get('block-room/edit/{id}', 'edit')->name('admin.block_room.edit');

        Route::post('block-room/update/{id}', 'update')->name('admin.block_room.update');

        Route::get('block-room/delete/{id}', 'destroy')->name('admin.block_room.delete');

    });

    // Room Cancellation Policy
    Route::controller(RoomCancellationPolicyController::class)->group(function () {

        Route::get('room-cancellation-policies', 'index')->name('admin.room_cancellation_policies');

        Route::get('room-cancellation-policy/create', 'create')->name('admin.room_cancellation_policy.create');

        Route::post('room-cancellation-policy/store', 'store')->name('admin.room_cancellation_policy.store');

        Route::get('room-cancellation-policy/edit/{id}', 'edit')->name('admin.room_cancellation_policy.edit');
        
        Route::post('room-cancellation-policy/update/{id}', 'update')->name('admin.room_cancellation_policy.update');

        Route::get('room-cancellation-policy/delete/{id}', 'destroy')->name('admin.room_cancellation_policy.delete');

    });

    // Room Booking
    Route::controller(RoomBookingController::class)->group(function () {

        Route::get('room-bookings', 'index')->name('admin.room_bookings');

        Route::get('room-booking/details/{id}', 'details')->name('admin.room_booking.details');

    });

    // Standard Operating Procedures
    Route::controller(SOPController::class)->group(function () {

        Route::get('room-sops', 'room_sop')->name('admin.room_sops');

        Route::post('room-sop/update', 'update_room_sop')->name('admin.room_sop.update');

        Route::get('banquet-sops', 'banquet_sop')->name('admin.banquet_sops');
        
        Route::post('banquet-sop/update', 'update_banquet_sop')->name('admin.banquet_sop.update');

    });

    // Settings
    Route::controller(AdminSettingController::class)->group(function () {

        Route::get('admin-settings', 'index')->name('admin.admin_settings');

        Route::post('admin-setting/update', 'update_admin_setting')->name('admin.admin_setting.update');

    });

    // Venue Master
    Route::controller(VenueMasterController::class)->group(function () {

        Route::get('venue-masters', 'index')->name('admin.venue_masters');

        Route::get('venue-master/create', 'create')->name('admin.venue_master.create');

        Route::post('venue-master/store', 'store')->name('admin.venue_master.store');
        
        Route::get('venue-master/edit/{id}', 'edit')->name('admin.venue_master.edit');
        
        Route::post('venue-master/update/{id}', 'update')->name('admin.venue_master.update');

        Route::get('venue-master/delete/{id}', 'destroy')->name('admin.venue_master.delete');

        Route::get('venue-master/status/{id}', 'status')->name('admin.venue_master.status');

    });

    // Session Master
    Route::controller(SessionController::class)->group(function () {

        Route::get('sessions', 'index')->name('admin.sessions');

        Route::get('session/create', 'create')->name('admin.session.create');

        Route::post('session/store', 'store')->name('admin.session.store');
        
        Route::get('session/edit/{id}', 'edit')->name('admin.session.edit');
        
        Route::post('session/update/{id}', 'update')->name('admin.session.update');

        Route::get('session/delete/{id}', 'destroy')->name('admin.session.delete');

        Route::get('session/status/{id}', 'status')->name('admin.session.status');
    });

    // Banquet Occupant
    Route::controller(BanquetOccupantController::class)->group(function () {

        Route::get('banquet-occupants', 'index')->name('admin.banquet_occupants');

        Route::get('banquet-occupant/create', 'create')->name('admin.banquet_occupant.create');

        Route::post('banquet-occupant/store', 'store')->name('admin.banquet_occupant.store');
        
        Route::get('banquet-occupant/edit/{id}', 'edit')->name('admin.banquet_occupant.edit');
        
        Route::post('banquet-occupant/update/{id}', 'update')->name('admin.banquet_occupant.update');

        Route::get('banquet-occupant/delete/{id}', 'destroy')->name('admin.banquet_occupant.delete');

        Route::get('banquet-occupant/status/{id}', 'status')->name('admin.banquet_occupant.status');
    });

    // Function Master
    Route::controller(FunctionMasterController::class)->group(function () {

        Route::get('functions', 'index')->name('admin.functions');

        Route::get('function/create', 'create')->name('admin.function.create');

        Route::post('function/store', 'store')->name('admin.function.store');
        
        Route::get('function/edit/{id}', 'edit')->name('admin.function.edit');
        
        Route::post('function/update/{id}', 'update')->name('admin.function.update');

        Route::get('function/delete/{id}', 'destroy')->name('admin.function.delete');

        Route::get('function/status/{id}', 'status')->name('admin.function.status');
    });

    // Venue Charges
    Route::controller(VenueChargesController::class)->group(function () {

        Route::get('venue-charges', 'index')->name('admin.venue_charges');

        Route::get('venue-charge/create', 'create')->name('admin.venue_charge.create');
        
        Route::post('venue-charge/store', 'store')->name('admin.venue_charge.store');
        
        Route::get('venue-charge/edit/{id}', 'edit')->name('admin.venue_charge.edit');
        
        Route::post('venue-charge/update/{id}', 'update')->name('admin.venue_charge.update');

        Route::get('venue-charge/delete/{id}', 'destroy')->name('admin.venue_charge.delete');

        Route::get('venue-charge/status/{id}', 'status')->name('admin.venue_charge.status');
    });

    // Venue Blocks
    Route::controller(VenueBlockController::class)->group(function () {

        Route::get('venue-blocks', 'index')->name('admin.venue_blocks');

        Route::get('venue-block/create', 'create')->name('admin.venue_block.create');
        
        Route::post('venue-block/store', 'store')->name('admin.venue_block.store');
        
        Route::get('venue-block/edit/{id}', 'edit')->name('admin.venue_block.edit');
        
        Route::post('venue-block/update/{id}', 'update')->name('admin.venue_block.update');

        Route::get('venue-block/delete/{id}', 'destroy')->name('admin.venue_block.delete');

    });

    // Cancellation Policies
    Route::controller(CancellationPolicyController::class)->group(function () {

        Route::get('cancellation-policies', 'index')->name('admin.cancellation_policies');

        Route::get('cancellation-policy/create', 'create')->name('admin.cancellation_policy.create');
        
        Route::post('cancellation-policy/store', 'store')->name('admin.cancellation_policy.store');
        
        Route::get('cancellation-policy/edit/{id}', 'edit')->name('admin.cancellation_policy.edit');
        
        Route::post('cancellation-policy/update/{id}', 'update')->name('admin.cancellation_policy.update');

        Route::get('cancellation-policy/delete/{id}', 'destroy')->name('admin.cancellation_policy.delete');
    });

    // Banquet Booking
    Route::controller(BanquetBookingController::class)->group(function () {

        Route::get('banquet-bookings', 'index')->name('admin.banquet_bookings');

        Route::get('banquet-booking/details/{id}', 'details')->name('admin.banquet_booking.details');

    });

    // Document
    Route::controller(DocumentController::class)->group(function () {

        Route::get('documents', 'index')->name('admin.documents');

        Route::get('document/create', 'create')->name('admin.document.create');
        
        Route::post('document/store', 'store')->name('admin.document.store');
        
        Route::get('document/edit/{id}', 'edit')->name('admin.document.edit');
        
        Route::post('document/update/{id}', 'update')->name('admin.document.update');

        Route::get('document/delete/{id}', 'destroy')->name('admin.document.delete');

        Route::get('document/status/{id}', 'status')->name('admin.document.status');

        // Menu Route
        Route::get('menus', 'menu_index')->name('admin.menus');

        Route::get('menu/create', 'menu_create')->name('admin.menu.create');

        Route::post('menu/store', 'menu_store')->name('admin.menu.store');

        Route::get('menu/edit/{id}', 'menu_edit')->name('admin.menu.edit');

        Route::post('menu/update/{id}', 'menu_update')->name('admin.menu.update');

        Route::get('menu/delete/{id}', 'menu_destroy')->name('admin.menu.delete');

        Route::get('menu/status/{id}', 'menu_status')->name('admin.menu.status');

    });

    // Table Venue
    Route::controller(TableVenueController::class)->group(function () {

        Route::get('table-venues', 'index')->name('admin.table_venues');
        
        Route::get('table-venue/create', 'create')->name('admin.table_venue.create');

        Route::post('table-venue/store', 'store')->name('admin.table_venue.store');
        
        Route::get('table-venue/edit/{id}', 'edit')->name('admin.table_venue.edit');
        
        Route::post('table-venue/update/{id}', 'update')->name('admin.table_venue.update');

        Route::get('table-venue/delete/{id}', 'destroy')->name('admin.table_venue.delete');

        Route::get('table-venue/status/{id}', 'status')->name('admin.table_venue.status');
        
    });

    // Table Meal
    Route::controller(TableMealController::class)->group(function () {

        Route::get('table-meals', 'index')->name('admin.table_meals');
        
        Route::get('table-meal/create', 'create')->name('admin.table_meal.create');

        Route::post('table-meal/store', 'store')->name('admin.table_meal.store');
        
        Route::get('table-meal/edit/{id}', 'edit')->name('admin.table_meal.edit');
        
        Route::post('table-meal/update/{id}', 'update')->name('admin.table_meal.update');

        Route::get('table-meal/delete/{id}', 'destroy')->name('admin.table_meal.delete');

        Route::get('table-meal/status/{id}', 'status')->name('admin.table_meal.status');
        
    });

    // Table Time
    Route::controller(TableTimeController::class)->group(function () {

        Route::get('table-times', 'index')->name('admin.table_times');
        
        Route::get('table-time/create', 'create')->name('admin.table_time.create');

        Route::post('table-time/store', 'store')->name('admin.table_time.store');
        
        Route::get('table-time/edit/{id}', 'edit')->name('admin.table_time.edit');
        
        Route::post('table-time/update/{id}', 'update')->name('admin.table_time.update');

        Route::get('table-time/delete/{id}', 'destroy')->name('admin.table_time.delete');

        Route::get('table-time/status/{id}', 'status')->name('admin.table_time.status');

    });

    // Table
    Route::controller(TableController::class)->group(function () {

        Route::get('tables', 'index')->name('admin.tables');
        
        Route::get('table/create', 'create')->name('admin.table.create');

        Route::post('table/store', 'store')->name('admin.table.store');
        
        Route::get('table/edit/{id}', 'edit')->name('admin.table.edit');
        
        Route::post('table/update/{id}', 'update')->name('admin.table.update');

        Route::get('table/delete/{id}', 'destroy')->name('admin.table.delete');

        Route::get('table/status/{id}', 'status')->name('admin.table.status');

    });

    // Table Booking
    Route::controller(TableBookingController::class)->group(function () {

        Route::get('table-booking', 'booking_list')->name('admin.table_bookings');

    });

    // MMR Registration Setting
    Route::controller(MMRRegistrationSettingController::class)->group(function () {

        Route::get('mmr-registration', 'index')->name('admin.mmr_registration.setting');
        
        Route::post('mmr-registration/update', 'update')->name('admin.mmr_registration.update');

        Route::get('mmr-registration-enquery', 'enquery')->name('admin.mmr_registration.list');

    });

    // Facility
    Route::controller(FacilityController::class)->group(function () {

        Route::get('facilities', 'index')->name('admin.facilities');
        
        Route::get('facility/create', 'create')->name('admin.facility.create');

        Route::post('facility/store', 'store')->name('admin.facility.store');
        
        Route::get('facility/edit/{id}', 'edit')->name('admin.facility.edit');
        
        Route::post('facility/update/{id}', 'update')->name('admin.facility.update');

        Route::get('facility/delete/{id}', 'destroy')->name('admin.facility.delete');

        Route::get('facility/status/{id}', 'status')->name('admin.facility.status');

    });

    // Slot
    Route::controller(SlotController::class)->group(function () {

        Route::get('slots', 'index')->name('admin.slots');
        
        Route::get('slot/create', 'create')->name('admin.slot.create');

        Route::post('slot/store', 'store')->name('admin.slot.store');
        
        Route::get('slot/edit/{id}', 'edit')->name('admin.slot.edit');
        
        Route::post('slot/update/{id}', 'update')->name('admin.slot.update');

        Route::get('slot/delete/{id}', 'destroy')->name('admin.slot.delete');

        Route::get('slot/status/{id}', 'status')->name('admin.slot.status');

    });

    // Activity Session
    Route::controller(ActivitySessionController::class)->group(function () {

        Route::get('activity-sessions', 'index')->name('admin.activity_sessions');
        
        Route::get('activity-session/create', 'create')->name('admin.activity_session.create');

        Route::post('activity-session/store', 'store')->name('admin.activity_session.store');
        
        Route::get('activity-session/edit/{id}', 'edit')->name('admin.activity_session.edit');
        
        Route::post('activity-session/update/{id}', 'update')->name('admin.activity_session.update');

        Route::get('activity-session/delete/{id}', 'destroy')->name('admin.activity_session.delete');

        Route::get('activity-session/status/{id}', 'status')->name('admin.activity_session.status');

    });

    // Facility Slot
    Route::controller(FacilitySlotController::class)->group(function () {

        Route::get('facility-slots', 'index')->name('admin.facility_slots');
        
        Route::get('facility-slot/create', 'create')->name('admin.facility_slot.create');

        Route::post('facility-slot/store', 'store')->name('admin.facility_slot.store');
        
        Route::get('facility-slot/edit/{id}', 'edit')->name('admin.facility_slot.edit');
        
        Route::post('facility-slot/update/{id}', 'update')->name('admin.facility_slot.update');

        Route::get('facility-slot/delete/{id}', 'destroy')->name('admin.facility_slot.delete');

        Route::get('facility-slot/status/{id}', 'status')->name('admin.facility_slot.status');

    });

    // Block Slot
    Route::controller(BlockSlotController::class)->group(function () {

        Route::get('block-slots', 'index')->name('admin.block_slots');
        
        Route::get('block-slot/create', 'create')->name('admin.block_slot.create');

        Route::post('block-slot/store', 'store')->name('admin.block_slot.store');
        
        Route::get('block-slot/edit/{id}', 'edit')->name('admin.block_slot.edit');
        
        Route::post('block-slot/update/{id}', 'update')->name('admin.block_slot.update');

        Route::get('block-slot/delete/{id}', 'destroy')->name('admin.block_slot.delete');

        Route::get('block-slot/status/{id}', 'status')->name('admin.block_slot.status');

    });

    // Game Type
    Route::controller(GameTypeController::class)->group(function () {

        Route::get('game-types', 'index')->name('admin.game_types');
        
        Route::get('game-type/create', 'create')->name('admin.game_type.create');

        Route::post('game-type/store', 'store')->name('admin.game_type.store');
        
        Route::get('game-type/edit/{id}', 'edit')->name('admin.game_type.edit');
        
        Route::post('game-type/update/{id}', 'update')->name('admin.game_type.update');

        Route::get('game-type/delete/{id}', 'destroy')->name('admin.game_type.delete');

        Route::get('game-type/status/{id}', 'status')->name('admin.game_type.status');

    });

    // Activity Cancellation Policy
    Route::controller(ActivityCancellationPolicyController::class)->group(function () {

        Route::get('activity-cancellation-policies', 'index')->name('admin.activity_cancellation_policies');
        
        Route::get('activity-cancellation-policy/create', 'create')->name('admin.activity_cancellation_policy.create');

        Route::post('activity-cancellation-policy/store', 'store')->name('admin.activity_cancellation_policy.store');
        
        Route::get('activity-cancellation-policy/edit/{id}', 'edit')->name('admin.activity_cancellation_policy.edit');
        
        Route::post('activity-cancellation-policy/update/{id}', 'update')->name('admin.activity_cancellation_policy.update');

        Route::get('activity-cancellation-policy/delete/{id}', 'destroy')->name('admin.activity_cancellation_policy.delete');

        Route::get('activity-cancellation-policy/status/{id}', 'status')->name('admin.activity_cancellation_policy.status');

    });

    // Activity Occupant Master
    Route::controller(ActivityOccupantMasterController::class)->group(function () {

        Route::get('activity-occupant-masters', 'index')->name('admin.activity_occupant_masters');
        
        Route::get('activity-occupant-master/create', 'create')->name('admin.activity_occupant_master.create');

        Route::post('activity-occupant-master/store', 'store')->name('admin.activity_occupant_master.store');
        
        Route::get('activity-occupant-master/edit/{id}', 'edit')->name('admin.activity_occupant_master.edit');
        
        Route::post('activity-occupant-master/update/{id}', 'update')->name('admin.activity_occupant_master.update');

        Route::get('activity-occupant-master/delete/{id}', 'destroy')->name('admin.activity_occupant_master.delete');

        Route::get('activity-occupant-master/status/{id}', 'status')->name('admin.activity_occupant_master.status');

    });

    // Activity Booking
    Route::controller(ActivityBookingController::class)->group(function () {

        Route::get('activity-bookings', 'admin_bookings')->name('admin.activity_bookings');

        Route::get('booking/detail/{id}', 'booking_details')->name('booking.details');

    });

    

});