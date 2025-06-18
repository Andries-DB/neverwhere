<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('dashboard');
});


Route::middleware(['auth', HandleInertiaRequests::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'show'])->name('dashboard');

    // Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    // Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/companies', [CompanyController::class, 'index'])->name('company.get');
    Route::post('/companies', [CompanyController::class, 'store'])->name('company.create');
    Route::get('/companies/{guid}', [CompanyController::class, 'show'])->name('company.read');
    Route::patch('/companies/{guid}', [CompanyController::class, 'update'])->name('company.update');
    Route::delete('/companies/{guid}', [CompanyController::class, 'delete'])->name('company.delete');

    Route::get('/companies/{guid}/{user_guid}', [UserController::class, 'show'])->name('company.user.read');
    Route::post('/companies/{guid}', [UserController::class, 'store'])->name('company.user.store');
    Route::patch('/companies/{guid}/{user_guid}', [UserController::class, 'update'])->name('company.user.update');
    Route::delete('/companies/{guid}/{user_guid}', [UserController::class, 'delete'])->name('company.user.delete');

    Route::get('/sources', [SourceController::class, 'index'])->name('source.get');
    Route::post('/sources', [SourceController::class, 'store'])->name('source.create');
    Route::get('/sources/{id}', [SourceController::class, 'read'])->name('source.read');
    Route::patch('/sources/{id}', [SourceController::class, 'update'])->name('source.update');
    Route::delete('/sources/{id}', [SourceController::class, 'delete'])->name('source.delete');

    Route::get('/conversation/{guid}', [ConversationController::class, 'read'])->name('conversation.read');
    Route::post('/conversation', [ConversationController::class, 'create'])->name('conversation.create');
    Route::get('/conversation/{guid}/message', [ConversationController::class, 'postUserMessage'])->name('conversation.postUserMessage');
    Route::get('/conversation/{guid}/bot-response', [ConversationController::class, 'getBotResponse'])->name('conversation.getBotResponse');
});

require __DIR__ . '/auth.php';
