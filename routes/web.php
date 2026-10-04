<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::view('/politica-de-confidentialitate', 'legal.privacy')->name('privacy');
Route::view('/politica-de-cookies', 'legal.cookies')->name('cookies');

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/cauta', [SiteController::class, 'search'])->name('search');
Route::get('/categorie/{productCategory}', [SiteController::class, 'category'])->name('category');
Route::get('/produs/{product}', [SiteController::class, 'product'])->name('product');
Route::get('/servicii/{service}', [SiteController::class, 'service'])->name('service');
Route::get('/stiri/{news}', [SiteController::class, 'news'])->name('news');

Route::middleware('guest')->group(function () {
    Route::get('/cont/autentificare', [AccountController::class, 'login'])->name('login');
    Route::post('/cont/autentificare', [AccountController::class, 'authenticate']);
    Route::get('/cont/inregistrare', [AccountController::class, 'register'])->name('register');
    Route::post('/cont/inregistrare', [AccountController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/cont', [AccountController::class, 'home'])->name('account.home');
    Route::post('/cont/programare', [AccountController::class, 'appointment'])->name('account.appointment');
    Route::post('/cont/iesire', [AccountController::class, 'logout'])->name('logout');
});
