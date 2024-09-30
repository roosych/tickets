<?php

use App\Http\Controllers\Cabinet\DepartmentController;
use App\Http\Controllers\Cabinet\IndexController;
use App\Http\Controllers\Cabinet\RoleController;
use App\Http\Controllers\Cabinet\TagController;
use App\Http\Controllers\Cabinet\TicketController;
use App\Http\Controllers\Cabinet\UserController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\TemporaryFileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/cabinet');

Route::get('mail', [\App\Http\Controllers\MailController::class, 'index']);

//Route::get('/cabinet', function () {
//    return view('cabinet.index');
//})->middleware(['auth'])->name('cabinet');

Route::get('lang/{language}', LanguageController::class)->name('language');

Route::middleware('auth')->prefix('cabinet')->name('cabinet.')->group(function () {
    Route::get('/', IndexController::class)->name('index');

    //upload files (filepond)
    Route::post('upload', [TemporaryFileController::class, 'store'])->name('files.upload');
    Route::delete('delete', [TemporaryFileController::class, 'delete'])->name('files.delete');

    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/{user:username}', [UserController::class, 'show'])->name('show');
        Route::post('{user}/attach-roles', [UserController::class, 'attach_roles'])->name('attach_roles');
        Route::post('{user}/attach-permissions', [UserController::class, 'attach_permissions'])->name('attach_permissions');
    });

    Route::prefix('dept')->name('dept.')->group(function () {

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
        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('inbox', [TicketController::class, 'inbox'])->name('inbox');
        Route::get('sent', [TicketController::class, 'sent'])->name('sent');
        Route::get('{ticket}', [TicketController::class, 'show'])->name('show');
        Route::post('store', [TicketController::class, 'store'])->name('store');
        Route::post('complete', [TicketController::class, 'complete'])->name('complete');
        Route::post('cancel', [TicketController::class, 'cancel'])->name('cancel');
        Route::post('{ticket}/close', [TicketController::class, 'close'])->name('close');
        Route::post('{id}/inprogress', [TicketController::class, 'inprogress'])->name('inprogress');
        Route::post('{ticket}/comment', [TicketController::class, 'storeComment'])->name('comment.store');
        Route::post('attach_users', [TicketController::class, 'attachUsers'])->name('attach_users');
        Route::post('{ticket}/attach_tags', [TicketController::class, 'attachTags'])->name('attach_tags');
        Route::get('{ticket}/performers', [TicketController::class, 'getTicketPerformers'])->name('performers');
    });



    Route::resource('tags', TagController::class)->except(
        ['create', 'edit'] // закрытые маршруты
    );

});

require __DIR__.'/auth.php';
