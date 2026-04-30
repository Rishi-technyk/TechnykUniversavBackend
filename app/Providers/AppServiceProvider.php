<?php

namespace App\Providers;

use App\Models\MMRRegistrationSetting;
use App\Services\Payments\PaymentGatewayManager;
use App\Services\Payments\PaymentModuleSyncService;
use App\Services\Payments\PaymentTransactionService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Pagination\Paginator;
use App\Models\AdminSetting;
use App\Models\Document;
use App\Models\Menu;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(PaymentGatewayManager::class);
        $this->app->singleton(PaymentModuleSyncService::class);
        $this->app->singleton(PaymentTransactionService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();

        View::composer('*', function ($view) {

            $setting = AdminSetting::first(); 

            $view->with('setting', $setting);
        });

        View::composer('*', function ($view) {

            $menu = Menu::orderBy('id', 'DESC')->where('status', 'Active')->get(); 

            $view->with('menus', $menu);

        });

        View::composer('*', function ($view) {

            $document = Document::orderBy('id', 'DESC')->where('status', 'Active')->get(); 

            $view->with('document', $document);
        });

        View::composer('*', function ($view) {

            $registrationSetting = MMRRegistrationSetting::where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->first();

            $view->with('registrationSetting', $registrationSetting);
        });
    }
}
