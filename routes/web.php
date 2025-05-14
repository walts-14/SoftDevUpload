<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AuthController;

// Home / test
        Route::get('/', fn() => view('welcome'));
        Route::get('/dbconn', fn() => view('dbconn'));

        
            
        // Admin login (unchanged)
        Route::get('/login/admin', [AuthController::class,'showAdminLogin'])
            ->name('login.admin');
        Route::post('/login/admin', [AuthController::class,'adminLogin']);


        // Student login — exactly one GET, one POST:
        Route::get('/login/student', [AuthController::class,'showStudentLogin'])
            ->name('login.student');
        Route::post('/login/student',[AuthController::class,'studentLogin'])
            ->name('login.student.submit');

        Route::get('/login', fn() => redirect()->route('login.student'))
            ->name('login');

            
        // REGISTER GET
        // Route::get('/register/student', [AuthController::class,'showStudentRegister'])
        //     ->name('register.student')
        //     ->name('login');  // again, give it the ‘login’ alias if you want the same redirect target

        // // REGISTER POST
        // Route::post('/register/student', [AuthController::class,'studentRegister'])
        //     ->name('register.student.submit');

        // Logout
        Route::post('/logout',[AuthController::class,'logout'])
            ->name('logout');

        // Documents (only for logged‑in students)
        Route::middleware('auth:student')
            ->prefix('documents')
            ->name('documents.')
            ->group(function(){
                Route::get('/',       [DocumentController::class,'index'])
                                    ->name('index');
                Route::post('/upload',[DocumentController::class,'upload'])
                                    ->name('upload');
                Route::delete('/remove/{id}',
                                [DocumentController::class,'remove'])
                                    ->name('remove');
                Route::get('/check-missing',
                                [DocumentController::class,'checkMissingDocs'])
                                    ->name('checkMissingDocs');
                Route::post('/send-reminder',
                                [DocumentController::class,'sendReminder'])
                                    ->name('sendReminder');
            });

