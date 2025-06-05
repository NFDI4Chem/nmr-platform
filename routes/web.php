<?php

use App\Http\Controllers\Auth\SocialController;
use Filament\Pages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Socialite Routes
Route::group([
    'prefix' => 'auth',
], function () {
    Route::get('/login/{service}', [SocialController::class, 'redirectToProvider']);
    Route::get('/login/{service}/callback', [SocialController::class, 'handleProviderCallback']);
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    if (! Auth::user()->personalCompany()) {
        return redirect(url('group\new'));
    } else {
        return redirect(url(Pages\Dashboard::getUrl(panel: 'groups', tenant: Auth::user()->personalCompany())));
    }
})->name('dashboard');
