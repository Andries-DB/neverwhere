<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\UserController;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\Mime\MessageConverter;

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

    Route::get('/companies/{guid}/source/{source_id}', [SourceController::class, 'read'])->name('company.source.read');
    Route::patch('/companies/{guid}/sources/{source_id}', [SourceController::class, 'update'])->name('company.source.update');
    Route::delete('/companies/{guid}/sources/{source_id}', [SourceController::class, 'delete'])->name('company.source.delete');
    Route::post('/sources', [SourceController::class, 'store'])->name('source.create');

    Route::get('/users', [UserController::class, 'index'])->name('user.get');
    Route::post('/users', [UserController::class, 'store'])->name('user.create');
    Route::get('/users/{guid}', [UserController::class, 'read'])->name('user.read');
    Route::patch('/users/{guid}', [UserController::class, 'update_only_user'])->name('user.update');
    Route::delete('/users/{guid}', [UserController::class, 'delete_only_user'])->name('user.delete');


    Route::get('/conversation/{guid}', [ConversationController::class, 'read'])->name('conversation.read');
    Route::post('/conversation', [ConversationController::class, 'create'])->name('conversation.create');
    Route::delete('/conversation/{guid}', [ConversationController::class, 'delete'])->name('conversation.delete');

    Route::get('/conversation/{guid}/message', [ConversationController::class, 'postUserMessage'])->name('conversation.postUserMessage');
    Route::get('/conversation/{guid}/bot-response', [ConversationController::class, 'getBotResponse'])->name('conversation.getBotResponse');

    Route::post('/conversation/pin', [ConversationController::class, 'pinChart'])->name('conversation.pinChart');
    Route::delete('/conversation/pin/{id}', [ConversationController::class, 'unpinChart'])->name('conversation.unpinChart');
    Route::delete('/conversation/message/pin/{id}', [ConversationController::class, 'unpinChartByMessage'])->name('conversation.unpinChartByMessage');
    Route::patch('/conversation/chart/{id}', [ConversationController::class, 'updateChartTitle'])->name('conversation.updateChartTitle');
    Route::post('/conversation/pin/table', [ConversationController::class, 'pinTable'])->name('conversation.pinTable');
    Route::delete('/conversation/message/pin/{id}', [ConversationController::class, 'unpinTableByMessage'])->name('conversation.unpinTable');
    Route::delete('/conversation/table/pin/{id}', [ConversationController::class, 'unpinTable'])->name('conversation.unpinTable');

    Route::post('/conversation/likemessage', [ConversationController::class, 'likeMessage'])->name('conversation.likeMessage');
    Route::post('/conversation/dislikemessage', [ConversationController::class, 'dislikeMessage'])->name('conversation.dislikeMessage');

    Route::get('/message/{guid}', [MessageController::class, 'read'])->name('message.read');
});

require __DIR__ . '/auth.php';
