<?php

declare(strict_types=1);

use App\Http\Routes\Routes;
use Illuminate\Support\Facades\Route;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization as ZRoute;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::group([
    'prefix' => ZRoute::setLocale(),
    'middleware' => [
        'localeSessionRedirect',
        'localeCookieRedirect',
        'localizationRedirect',
        'localeViewPath',
        'localize',
    ],
], static function () {

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Auth::routes();

    Routes::initializeGetPostRoutes();
});

Route::post('webhook/stripe', 'Laravel\Cashier\Http\Controllers\WebhookController@handleWebhook');
