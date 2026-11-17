<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Customer;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicInvitationController;
use Illuminate\Support\Facades\Route;

// ---------- Awam ----------
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/tema/{theme:slug}/demo', [HomeController::class, 'demo'])->name('theme.demo');

Route::get('/k/{slug}', [PublicInvitationController::class, 'show'])->name('invitation.show');
Route::post('/k/{slug}/rsvp', [PublicInvitationController::class, 'rsvp'])->middleware('throttle:10,1')->name('invitation.rsvp');
Route::get('/k/{slug}/kalendar.ics', [PublicInvitationController::class, 'calendar'])->name('invitation.calendar');

// ---------- Auth ----------
Route::middleware('guest')->group(function () {
    Route::get('/log-masuk', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/log-masuk', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/daftar', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/daftar', [AuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/log-keluar', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

// ---------- Pembayaran ----------
Route::get('/bayaran/simulasi/{payment:reference}', [PaymentController::class, 'simulator'])->middleware('signed')->name('payment.simulator');
Route::get('/bayaran/kembali', [PaymentController::class, 'return'])->name('payment.return');
Route::post('/bayaran/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// ---------- Pelanggan ----------
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [Customer\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profil', [Customer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profil', [Customer\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/kata-laluan', [Customer\ProfileController::class, 'password'])->name('profile.password');

    Route::get('/bayaran', [Customer\DashboardController::class, 'payments'])->name('payments.index');

    Route::prefix('kad')->name('invitations.')->controller(Customer\InvitationController::class)->group(function () {
        Route::get('/baru', 'create')->name('create');
        Route::post('/', 'store')->name('store');
        Route::get('/{invitation}', 'show')->name('show');
        Route::get('/{invitation}/edit', 'edit')->name('edit');
        Route::put('/{invitation}', 'update')->name('update');
        Route::delete('/{invitation}', 'destroy')->name('destroy');
        Route::get('/{invitation}/pratonton', 'preview')->name('preview');
        Route::post('/{invitation}/bayar', [PaymentController::class, 'checkout'])->name('checkout');
    });

    Route::prefix('kad/{invitation}')->name('invitations.')->group(function () {
        Route::get('/galeri', [Customer\GalleryController::class, 'index'])->name('gallery.index');
        Route::post('/galeri', [Customer\GalleryController::class, 'store'])->name('gallery.store');
        Route::delete('/galeri/{image}', [Customer\GalleryController::class, 'destroy'])->name('gallery.destroy');

        Route::get('/rsvp', [Customer\RsvpController::class, 'index'])->name('rsvp.index');
        Route::get('/rsvp/eksport', [Customer\RsvpController::class, 'export'])->name('rsvp.export');
        Route::patch('/rsvp/{rsvp}/ucapan', [Customer\RsvpController::class, 'toggleWish'])->name('rsvp.toggle');
        Route::delete('/rsvp/{rsvp}', [Customer\RsvpController::class, 'destroy'])->name('rsvp.destroy');
    });
});

// ---------- Admin ----------
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::resource('pakej', Admin\PackageController::class)->except('show')->parameters(['pakej' => 'package'])->names('packages');
    Route::get('/tema', [Admin\ThemeController::class, 'index'])->name('themes.index');
    Route::patch('/tema/{theme}', [Admin\ThemeController::class, 'update'])->name('themes.update');
    Route::get('/kad', [Admin\InvitationController::class, 'index'])->name('invitations.index');
    Route::patch('/kad/{invitation}/status', [Admin\InvitationController::class, 'status'])->name('invitations.status');
    Route::get('/bayaran', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::patch('/bayaran/{payment}/sahkan', [Admin\PaymentController::class, 'markPaid'])->name('payments.mark-paid');
    Route::get('/pengguna', [Admin\UserController::class, 'index'])->name('users.index');
});
