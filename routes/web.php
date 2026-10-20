<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/tentang', [PublicController::class, 'about'])->name('about');
Route::get('/kontak', [PublicController::class, 'contact'])->name('contact');
Route::post('/kontak', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');
Route::get('/sitemap.xml', [PublicController::class, 'sitemap'])->name('sitemap');
Route::get('/media/{media}', [MediaController::class, 'show'])->name('media.show');
Route::get('/riset/{slug}/bibtex', [PublicController::class, 'bibtex'])->name('research.bibtex');
Route::get('/karya/{type}', [PublicController::class, 'index'])->whereIn('type', ['project', 'research', 'article'])->name('content.index');
Route::get('/karya/{type}/{slug}', [PublicController::class, 'show'])->whereIn('type', ['project', 'research', 'article'])->name('content.show');
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [AuthController::class, 'login'])->name('login');
    Route::post('/admin/login', [AuthController::class, 'authenticate'])->middleware('throttle:login')->name('login.store');
    Route::get('/admin/forgot-password', [AuthController::class, 'forgot'])->name('password.request');
    Route::post('/admin/forgot-password', [AuthController::class, 'email'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/admin/reset-password/{token}', [AuthController::class, 'reset'])->name('password.reset');
    Route::post('/admin/reset-password', [AuthController::class, 'update'])->middleware('throttle:5,1')->name('password.update');
});
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/password', [AuthController::class, 'password'])->name('password');
    Route::post('/contents/bulk', [AdminController::class, 'bulk'])->name('contents.bulk');
    Route::patch('/contents/{content}/order', [AdminController::class, 'reorder'])->name('contents.order');
    Route::get('/contents/{content}/preview', [PublicController::class, 'preview'])->name('contents.preview');
    Route::resource('contents', AdminController::class)->except('show');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
    Route::put('/settings', [AdminController::class, 'saveSettings'])->name('settings.update');
    Route::post('/entries', [AdminController::class, 'saveEntry'])->name('entries.store');
    Route::delete('/entries/{entry}', [AdminController::class, 'deleteEntry'])->name('entries.destroy');
    Route::get('/taxonomies', [AdminController::class, 'taxonomies'])->name('taxonomies');
    Route::post('/taxonomies', [AdminController::class, 'saveTaxonomy'])->name('taxonomies.store');
    Route::delete('/taxonomies/{taxonomy}', [AdminController::class, 'deleteTaxonomy'])->name('taxonomies.destroy');
    Route::get('/messages', [AdminController::class, 'messages'])->name('messages');
    Route::patch('/messages/{message}', [AdminController::class, 'messageAction'])->name('messages.update');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}',[MediaController::class, 'destroy'])->name('media.destroy');
});
