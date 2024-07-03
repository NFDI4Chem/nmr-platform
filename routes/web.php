<?php

use App\Http\Controllers\Auth\SocialController;
use Filament\Pages;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => 'auth',
], function () {
    Route::get('/login/{service}', [SocialController::class, 'redirectToProvider']);
    Route::get('/login/{service}/callback', [SocialController::class, 'handleProviderCallback']);
});

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        if (! Auth::user()->personalCompany()) {
            return redirect(url('company\new'));
        } else {
            return redirect(url(Pages\Dashboard::getUrl(panel: 'company', tenant: Auth::user()->personalCompany())));
        }
    })->name('dashboard');
});
