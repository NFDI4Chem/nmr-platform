<?php

use App\Http\Controllers\Auth\SocialController;
use Filament\Pages;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

Route::get('/privacy-policy', function () {
    $content = file_get_contents(resource_path('markdown/policy.md'));
    $policy = Str::markdown($content);

    return view('policy', compact('policy'));
})->name('privacy-policy');

Route::get('/terms-of-use', function () {
    $content = file_get_contents(resource_path('markdown/terms.md'));
    $terms = Str::markdown($content);

    return view('terms', compact('terms'));
})->name('terms-of-use');

Route::get('/dashboard', function () {
    if (! Auth::user()->personalCompany()) {
        return redirect(url('group\new'));
    } else {
        return redirect(url(Pages\Dashboard::getUrl(panel: 'groups', tenant: Auth::user()->personalCompany())));
    }
})->name('dashboard');
