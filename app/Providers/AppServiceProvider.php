<?php

declare(strict_types=1);

namespace App\Providers;

use App\Transports\MsGraphTransport;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\ServiceProvider;
use Laravel\Cashier\Cashier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Cashier::useCustomerModel(Business::class);
        Cashier::calculateTaxes();

        Paginator::useBootstrapFive();
        Paginator::useBootstrapFour();

        Blade::directive('autofocus', static function ($expression) {
            // phpcs:ignore Squiz.Strings.DoubleQuoteUsage
            return "<?php echo ($expression) ? 'autofocus' : '' ?>";
        });

        Mail::extend('microsoft-graph', static function () {
            return new MsGraphTransport();
        });
    }
}
