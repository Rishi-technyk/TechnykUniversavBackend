<?php

namespace App\Http\Controllers\api\v1;


use App\Http\Controllers\Controller;

use App\Models\AppModule;

use App\Models\Event;
use DB;

use AESEncDec;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use Razorpay\Api\Api;


class AppController extends Controller

{
    
    public function dashboardConfig()
{
    $modules = AppModule::where('is_active', 1)
        ->orderBy('position')
        ->where('type','member tab')
        ->get();

    $version = DB::table('app_config')
        ->where('config_key', 'dashboard_version')
        ->value('config_value') ?? 1;

    $topModules = [];
    $mainModules = [];

    /*
    |--------------------------------------------------------------------------
    | Detect Enabled Booking Modules
    |--------------------------------------------------------------------------
    */
    $bookingTabs = [];

    foreach ($modules as $m) {
        if (in_array($m->module_key, [
            'room',
            'banquet',
            'event',
            'activity',
            'tee',
            'table'
        ])) {
            $bookingTabs[] = ucfirst($m->module_key);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Build Modules
    |--------------------------------------------------------------------------
    */
    foreach ($modules as $m) {

        $item = [
            'id'       => $m->id,
            'name'     => $m->name,
            'subTitle' => $m->subtitle,
            'icon'     => $m->icon,
            'navigate' => $m->navigate,
            'layout'   => $m->layout,
            'data'     => $m->data_json
                ? json_decode($m->data_json, true)
                : null,
        ];

        /*
        |--------------------------------------------------------------------------
        | Event Smart Navigation
        |--------------------------------------------------------------------------
        */
        if ($m->module_key === 'event') {

            $activeEvents = Event::where('status', 'active')->get();

            if ($activeEvents->count() === 1) {
                $item['navigate'] = 'Event';
                $item['data'] = $activeEvents->first()->id;
            } else {
                $item['navigate'] = 'EventsList';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | My Bookings Dynamic Tabs
        |--------------------------------------------------------------------------
        */
        if ($m->module_key === 'my_bookings') {
            $item['data'] = $bookingTabs;
        }

        /*
        |--------------------------------------------------------------------------
        | Separate Layouts
        |--------------------------------------------------------------------------
        */
        if ($m->layout === 'top') {
            $topModules[] = $item;
        } else {
            $mainModules[] = $item;
        }
    }

    return response()->json([
        'status'      => true,
        'version'     => (int) $version,
        'top_modules' => $topModules,
        'modules'     => $mainModules,
    ]);
}


public function geDrawerConfig()
{
    $version = DB::table('app_config')
        ->where('config_key', 'menu_version')
        ->value('config_value') ?? 1;

    $menus = DB::table('drawer_menus')
        ->where('visible',1)
        ->orderBy('menu_order')
        ->get();

    return response()->json([
        'status'      => true,
        'version' => (int)$version,
        'drawer_menu' => $menus
    ]);
}
public function getAdminTabConfig()
{
    $version = DB::table('app_config')
        ->where('config_key', 'admin_login_version')
        ->value('config_value') ?? 1;

    $menus =  AppModule::where('is_active', 1)
        ->orderBy('position')
        ->where('type','admin login')
        ->get();

    return response()->json([
        'status'      => true,
        'version' => (int)$version,
        'login_tabs' => $menus
    ]);
}

}