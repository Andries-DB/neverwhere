<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Logscontroller;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SourceController;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\TwoFactorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Symfony\Component\Mime\MessageConverter;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', '2fa', HandleInertiaRequests::class])->group(function () {
    Route::get('/two-factor/verify', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/verify', [TwoFactorController::class, 'confirmVerification'])->name('two-factor.confirm');

    Route::get('/two-factor/setup', [TwoFactorController::class, 'setup'])->name('two-factor.setup');
    Route::post('/two-factor/enable', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::post('/two-factor/disable', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    Route::get('/pinboard/{guid?}', [DashboardController::class, 'show'])->name('dashboard');

    Route::post('/pinboard/charts/reorder', [DashboardController::class, 'updateGraphOrder'])->name('dashboard.updateGraphOrder');
    Route::post('/pinboard/tables/reorder', [DashboardController::class, 'updateTableOrder'])->name('dashboard.updateTableOrder');
    Route::post('/pinboard/default', [DashboardController::class, 'makeDefault'])->name('dashboard.makeDefault');
    Route::delete('/pinboard/delete', [DashboardController::class, 'delete'])->name('dashboard.delete');
    Route::post('/pinboard/create', [DashboardController::class, 'create'])->name('dashboard.create');


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

    Route::get('/companies/{guid}/report/{report_guid}', [ReportController::class, 'read_company'])->name('company.report.read');
    Route::patch('/companies/{guid}/report/{report_guid}', [ReportController::class, 'update_company'])->name('company.report.update');
    Route::delete('/companies/{guid}/report/{report_guid}', [ReportController::class, 'delete_company'])->name('company.report.delete');

    Route::get('/companies/{guid}/{user_guid}/{report_guid}', [ReportController::class, 'read_user'])->name('user.report.read');
    Route::patch('/user/{guid}/report/{report_guid}', [ReportController::class, 'update_user'])->name('user.report.update');
    Route::delete('/user/{guid}/report/{report_guid}', [ReportController::class, 'delete_user'])->name('user.report.delete');

    Route::post('/sources', [SourceController::class, 'store'])->name('source.create');
    Route::post('/company/reports', [ReportController::class, 'store_company'])->name('company.report.create');
    Route::post('/user/reports', [ReportController::class, 'store_user'])->name('user.report.create');

    Route::post('/company/{guid}/usergroups', [UserGroupController::class, 'store'])->name('company.usergroup.store');
    Route::get('/company/{guid}/usergroups/{usergroup_guid}', [UserGroupController::class, 'read'])->name('company.usergroup.read');
    Route::patch('/companies/{guid}/usergroups/{usergroup_guid}', [UserGroupController::class, 'update'])->name('company.usergroup.update');
    Route::delete('/companies//usergroups/{usergroup_guid}', [UserGroupController::class, 'delete'])->name('company.usergroup.delete');

    Route::get('/reports', [ReportController::class, 'get'])->name('reports.get');

    Route::get('/feedback', [RequestController::class, 'get'])->name('requests.get');
    Route::post('/feedback', [RequestController::class, 'post'])->name('requests.post');
    Route::get('/feedback/admin', [RequestController::class, 'get_admin'])->name('reports.get.admin');
    Route::delete('/feedback/admin/{id}', [RequestController::class, 'delete'])->name('reports.delete.admin');

    Route::get('/users', [UserController::class, 'index'])->name('user.get');
    Route::post('/users', [UserController::class, 'store'])->name('user.create');
    Route::get('/users/{guid}', [UserController::class, 'read'])->name('user.read');
    Route::patch('/users/{guid}', [UserController::class, 'update_only_user'])->name('user.update');
    Route::delete('/users/{guid}', [UserController::class, 'delete_only_user'])->name('user.delete');

    Route::get('/conversation/{guid}', [ConversationController::class, 'read'])->name('conversation.read');
    Route::post('/conversation', [ConversationController::class, 'create'])->name('conversation.create');
    Route::patch('/conversation/{guid}', [ConversationController::class, 'patch'])->name('conversation.update');

    Route::delete('/conversation/{guid}', [ConversationController::class, 'delete'])->name('conversation.delete');

    Route::get('/conversation/{guid}/message', [ConversationController::class, 'postUserMessage'])->name('conversation.postUserMessage');
    Route::get('/conversation/{guid}/bot-response', [ConversationController::class, 'getBotResponse'])->name('conversation.getBotResponse');

    Route::post('/conversation/pin', [ConversationController::class, 'pinChart'])->name('conversation.pinChart');
    Route::delete('/conversation/pin/{id}', [ConversationController::class, 'unpinChart'])->name('conversation.unpinChart');
    Route::delete('/conversation/message/pin/{id}', [ConversationController::class, 'unpinChartByMessage'])->name('conversation.unpinChartByMessage');
    Route::patch('/conversation/chart/{id}', [ConversationController::class, 'updateChartTitle'])->name('conversation.updateChartTitle');
    Route::patch('/conversation/table/{id}', [ConversationController::class, 'updateTableTitle'])->name('conversation.updateTableTitle');
    Route::post('/conversation/pin/table', [ConversationController::class, 'pinTable'])->name('conversation.pinTable');
    Route::delete('/conversation/message/pin/{id}', [ConversationController::class, 'unpinTableByMessage'])->name('conversation.unpinTable');
    Route::delete('/conversation/table/pin/{id}', [ConversationController::class, 'unpinTable'])->name('conversation.unpinTable');
    Route::patch('/conversation/chart/{id}/width', [ConversationController::class, 'updateChartWidth'])->name('conversation.updateChartWidth');
    Route::patch('/conversation/table/{id}/width', [ConversationController::class, 'updateTableWidth'])->name('conversation.updateTableWidth');
    Route::patch('/conversation/chart/{id}/refresh', [ConversationController::class, 'updateChartJson'])->name('conversation.updateChartJson');
    Route::patch('/conversation/table/{id}/refresh', [ConversationController::class, 'updateTableJson'])->name('conversation.updateTableJson');
    Route::post('/conversation/chart/{id}/duplicate', [ConversationController::class, 'duplicateGraph'])->name('conversation.duplicateChart');
    Route::post('/conversation/table/{id}/duplicate', [ConversationController::class, 'duplicateTable'])->name('conversation.duplicateTable');


    Route::post('/conversation/likemessage', [ConversationController::class, 'likeMessage'])->name('conversation.likeMessage');
    Route::post('/conversation/dislikemessage', [ConversationController::class, 'dislikeMessage'])->name('conversation.dislikeMessage');

    Route::get('/message/{guid}', [MessageController::class, 'read'])->name('message.read');

    Route::get('/training', [TrainingController::class, 'index'])->name('training.index');

    Route::get('/logs', [Logscontroller::class, 'index'])->name('logs.get');

    Route::post('/dr-itchy/summary', [ConversationController::class, 'summarize'])->name('conversation.message.summarize');
});




require __DIR__ . '/auth.php';
