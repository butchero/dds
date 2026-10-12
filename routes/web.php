<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountDataController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PhoneConfirmationController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\SocialAuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::view('/politica-de-confidentialitate', 'legal.privacy')->name('privacy');
Route::view('/politica-de-cookies', 'legal.cookies')->name('cookies');

Route::get('/', [SiteController::class, 'home'])->name('home');
Route::get('/cauta', [SiteController::class, 'search'])->name('search');
Route::get('/producator/{manufacturer}', [SiteController::class, 'manufacturer'])->name('manufacturer');
Route::get('/categorie/{productCategory}', [SiteController::class, 'category'])->name('category');
Route::get('/produs/{product}', [SiteController::class, 'product'])->name('product');
Route::permanentRedirect('/servicii/contact', '/contact');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/contact/verificare', [ContactController::class, 'challenge'])->name('contact.challenge');
Route::get('/servicii/{service}', [SiteController::class, 'service'])->name('service');
Route::get('/stiri/{news}', [SiteController::class, 'news'])->name('news');

Route::middleware('guest')->group(function () {
    Route::get('/cont/autentificare', [AccountController::class, 'login'])->name('login');
    Route::post('/cont/autentificare', [AccountController::class, 'authenticate']);
    Route::get('/cont/inregistrare', [AccountController::class, 'register'])->name('register');
    Route::post('/cont/inregistrare', [AccountController::class, 'store']);
    Route::get('/cont/parola', fn () => view('auth.forgot-password'))->name('password.request');
    Route::post('/cont/parola', [AccountController::class, 'sendResetLink'])->name('password.email');
    Route::get('/cont/resetare/{token}', fn (string $token) => view('auth.reset-password', [
        'token' => $token,
        'email' => request('email'),
    ]))->name('password.reset');
    Route::post('/cont/resetare', [AccountController::class, 'resetPassword'])->name('password.update');
});

Route::get('/cont/telefon', [PhoneConfirmationController::class, 'show'])->name('phone.confirm');
Route::post('/cont/telefon', [PhoneConfirmationController::class, 'store']);
Route::get('/cont/auth/{provider}', [SocialAuthController::class, 'redirect'])->name('social.redirect');
Route::get('/cont/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');

Route::middleware('auth')->group(function () {
    Route::get('/cont/confirma-email', fn () => view('auth.verify-email'))->name('verification.notice');
    Route::get('/cont/confirma-email/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('account.home');
    })->middleware('signed')->name('verification.verify');
    Route::post('/cont/confirma-email', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'Ți-am trimis din nou linkul de confirmare.');
    })->middleware('throttle:6,1')->name('verification.send');
    Route::post('/cont/iesire', [AccountController::class, 'logout'])->name('logout');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/cont', [AccountController::class, 'home'])->name('account.home');
    Route::get('/cont/adrese', [AccountController::class, 'addresses'])->name('account.addresses');
    Route::get('/cont/echipamente', [AccountController::class, 'equipment'])->name('account.equipment');
    Route::get('/cont/facturare', [AccountController::class, 'billing'])->name('account.billing');
    Route::get('/cont/programari', [AccountController::class, 'appointments'])->name('account.appointments');
    Route::post('/cont/profil', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::post('/cont/parola-noua', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::post('/cont/telefon-nou', [AccountController::class, 'updatePhone'])->name('account.phone');
    Route::post('/cont/programare', [AccountController::class, 'appointment'])->name('account.appointment');
    Route::post('/cont/adrese', [AccountDataController::class, 'storeAddress'])->name('account.addresses.store');
    Route::post('/cont/adrese/{address}', [AccountDataController::class, 'updateAddress'])->name('account.addresses.update');
    Route::delete('/cont/adrese/{address}', [AccountDataController::class, 'deleteAddress'])->name('account.addresses.delete');
    Route::post('/cont/echipamente', [AccountDataController::class, 'storeEquipment'])->name('account.equipment.store');
    Route::post('/cont/echipamente/{equipment}', [AccountDataController::class, 'updateEquipment'])->name('account.equipment.update');
    Route::delete('/cont/echipamente/{equipment}', [AccountDataController::class, 'deleteEquipment'])->name('account.equipment.delete');
    Route::post('/cont/facturare', [AccountDataController::class, 'storeBilling'])->name('account.billing.store');
    Route::post('/cont/facturare/{billingProfile}', [AccountDataController::class, 'updateBilling'])->name('account.billing.update');
    Route::delete('/cont/facturare/{billingProfile}', [AccountDataController::class, 'deleteBilling'])->name('account.billing.delete');
});
