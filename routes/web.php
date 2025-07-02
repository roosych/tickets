<?php

use App\Http\Controllers\Cabinet\DepartmentController;
use App\Http\Controllers\Cabinet\IndexController;
use App\Http\Controllers\Cabinet\MentionController;
use App\Http\Controllers\Cabinet\ReportController;
use App\Http\Controllers\Cabinet\RoleController;
use App\Http\Controllers\Cabinet\TagController;
use App\Http\Controllers\Cabinet\TicketController;
use App\Http\Controllers\Cabinet\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TelegramBotController;
use App\Http\Controllers\TemporaryFileController;
use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::redirect('/', '/cabinet');

Route::get('lang/{language}', LanguageController::class)->name('language');

Route::middleware('auth')->prefix('cabinet')->name('cabinet.')->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('index');

    Route::get('chart-data', [IndexController::class, 'getChartData'])->name('get_tickets_chart');


    //upload files (filepond)
    Route::post('upload', [TemporaryFileController::class, 'store'])->name('files.upload');
    Route::delete('delete', [TemporaryFileController::class, 'delete'])->name('files.delete');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/{user:username}', [UserController::class, 'show'])->name('show');
        Route::post('{user}/attach-roles', [UserController::class, 'attach_roles'])->middleware('check.department.status')->name('attach_roles');
        Route::post('{user}/attach-permissions', [UserController::class, 'attach_permissions'])->middleware('check.department.status')->name('attach_permissions');
    });

    Route::prefix('dept')->name('dept.')->middleware('check.department.status')->group(function () {

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [DepartmentController::class, 'users'])->name('index');
        });

        Route::get('roles', [RoleController::class, 'index'])->name('roles');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
        Route::post('roles/store', [RoleController::class, 'store'])->name('roles.store');
        Route::post('roles/{role}/update', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('roles/{role}/delete', [RoleController::class, 'delete'])->name('roles.delete');
        Route::post('roles/{role}/attach_users', [RoleController::class, 'attach_users'])->name('roles.attach_users');
        Route::post('roles/{role}/detach_user', [RoleController::class, 'detach_user'])->name('roles.detach_user');
    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [TicketController::class, 'index'])->name('index')->middleware('check.department.status');
        Route::get('inbox', [TicketController::class, 'inbox'])->name('inbox')->middleware('check.department.status');
        Route::get('sent', [TicketController::class, 'sent'])->name('sent');
        Route::get('sent-dept', [TicketController::class, 'sentDept'])->name('sent-dept');
        Route::get('{ticket}', [TicketController::class, 'show'])->name('show')
            ->middleware(['check.private.ticket']);
        Route::post('store', [TicketController::class, 'store'])->name('store');
        Route::post('complete', [TicketController::class, 'complete'])->name('complete');
        Route::post('cancel', [TicketController::class, 'cancel'])->name('cancel');
        Route::post('{ticket}/close', [TicketController::class, 'close'])->name('close');
        Route::post('{id}/inprogress', [TicketController::class, 'inprogress'])->name('inprogress');
        Route::post('{ticket}/comment', [TicketController::class, 'storeComment'])->name('comment.store');
        Route::post('attach_users', [TicketController::class, 'attachUsers'])->name('attach_users')
            ->middleware('check.department.status');
        Route::post('{ticket}/attach_tags', [TicketController::class, 'attachTags'])->name('attach_tags')
            ->middleware('check.department.status');
        Route::get('{ticket}/performers', [TicketController::class, 'getTicketPerformers'])->name('performers')
            ->middleware('check.department.status');
    });

    Route::resource('tags', TagController::class)->middleware('check.department.status')->except(
        ['create', 'edit'] // закрытые маршруты
    );

    Route::prefix('reports')->name('reports.')->middleware('check.department.status')->group(function () {
        Route::get('/tickets', [ReportController::class, 'tickets'])->name('tickets');
        Route::get('/depts', [ReportController::class, 'depts'])->name('depts');
        Route::get('/tags', [ReportController::class, 'tags'])->name('tags');
    });

    Route::prefix('settings')->name('settings.')->middleware('admin')->group(function () {
        Route::get('/users', [SettingsController::class, 'users'])->name('users');
        Route::post('/users/{id}/toggle-setting/{setting}', [SettingsController::class, 'toggleUserSetting'])->name('users.toggleUserSetting');
        Route::get('/departments', [SettingsController::class, 'departments'])->name('departments');
        Route::get('/departments/{department}/show', [SettingsController::class, 'show'])->name('departments.show');
        Route::post('/departments/{department}/show', [SettingsController::class, 'store'])->name('departments.store');
    });

    Route::prefix('mentions')->name('mentions.')->group(function () {
        Route::get('/unread', [MentionController::class, 'getUnreadMentions'])->name('unread');
    });
});


Route::post('bot/'.config('services.telegram.bot_token').'/setwebhook', function () {
    $response = Telegram::setWebhook(['url' => 'https://f4ec-81-17-91-221.ngrok-free.app/bot/'.config('services.telegram.bot_token').'/webhook']);
    return $response;
});
Route::post('bot/'.config('services.telegram.bot_token').'/webhook', [TelegramBotController::class, 'handleWebhook'])
    ->name('telegram.webhook');

require __DIR__.'/auth.php';
