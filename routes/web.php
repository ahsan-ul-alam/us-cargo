<?php

use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('/',          [HomeController::class, 'index'])->name('home');
Route::get('/services',  [HomeController::class, 'services'])->name('services');
Route::get('/pricing',   [HomeController::class, 'pricing'])->name('pricing');
Route::get('/about',     [HomeController::class, 'about'])->name('about');
Route::get('/faq',       [HomeController::class, 'faq'])->name('faq');

Route::get('/reviews',   [\App\Http\Controllers\ReviewController::class, 'index'])->name('reviews');
Route::post('/reviews',  [\App\Http\Controllers\ReviewController::class, 'store'])->middleware('throttle:5,1')->name('reviews.store');

Route::get('/videos',    [\App\Http\Controllers\VideoController::class, 'index'])->name('videos');

Route::get('/tracking',  [TrackingController::class, 'index'])->name('tracking');
Route::post('/tracking', [TrackingController::class, 'track'])->name('tracking.search');

Route::get('/quote',     [QuoteController::class, 'index'])->name('quote');
Route::post('/quote',    [QuoteController::class, 'store'])->name('quote.store');

Route::get('/contact',   [ContactController::class, 'index'])->name('contact');
Route::post('/contact',  [ContactController::class, 'store'])->name('contact.store');

Route::get('/admin/shipments/{shipment}/invoice', [InvoiceController::class, 'show'])
    ->middleware('auth')
    ->name('admin.invoice');
