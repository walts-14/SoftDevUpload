<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/dbconn', function(){ 
        return view('dbconn'); 
        });


    Route::get('/login/admin', [AuthController::class, 'showAdminLogin'])->name('login.admin');
    Route::post('/login/admin', [AuthController::class, 'adminLogin']);

    Route::get('/login/student', [AuthController::class, 'showStudentLogin'])->name('login.student');
    Route::post('/login/student', [AuthController::class, 'studentLogin']);

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('send-mail', [MailController::class, 'index']);

    // Document routes grouped under /documents
    Route::prefix('documents')->group(function () {
        Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('/upload', [DocumentController::class, 'upload'])->name('documents.upload');
        Route::delete('/remove/{id}', [DocumentController::class, 'remove'])->name('documents.remove');
        Route::get('/check-missing', [DocumentController::class, 'checkMissingDocs'])->name('documents.checkMissingDocs');
        Route::post('/send-reminder', [DocumentController::class, 'sendReminder'])->name('documents.sendReminder');
    });