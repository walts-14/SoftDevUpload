<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\AdminController;

/*
|--------------------------------------------------------------------------
| Public / Auth Routes
|--------------------------------------------------------------------------
*/

// 1. Student login & (optional) registration
Route::get('/login/student', [AuthController::class, 'showStudentLogin'])
     ->name('login.student');
Route::post('/login/student', [AuthController::class, 'studentLogin'])
     ->name('login.student.submit');

// 2. Admin login
Route::get('/login/admin', [AuthController::class, 'showAdminLogin'])
     ->name('login.admin');
Route::post('/login/admin', [AuthController::class, 'adminLogin'])
     ->name('login.admin.submit');

// 3. Logout (for both guards — your controller handles which guard to log out)
Route::post('/logout', [AuthController::class, 'logout'])
     ->name('logout');


/*
|--------------------------------------------------------------------------
| Student‐only Routes (guard: student)
|--------------------------------------------------------------------------
*/
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
         Route::post('/send-reminder',
                              [DocumentController::class,'sendReminder'])
                              ->name('sendReminder');

         // NEW: submission confirmation endpoint
         Route::post('/submit',
                              [DocumentController::class,'submitApplication'])
                              ->name('submitApplication');
     });


/*
|--------------------------------------------------------------------------
| Admin‐only Routes (guard: admin)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:admin')
     ->prefix('admin')
     ->name('admin.')
     ->group(function(){
         // GET /admin → list of pending applications
         Route::get('/', [AdminController::class, 'listApplications'])
                       ->name('applications.list');

         // Approval / rejection
         Route::post('/applications/{student}/approve',
                      [AdminController::class,'approveApplication'])
                       ->name('applications.approve');
         Route::post('/applications/{student}/reject',
                      [AdminController::class,'rejectApplication'])
                       ->name('applications.reject');
     });


/*
|--------------------------------------------------------------------------
| Fallback / Home
|--------------------------------------------------------------------------
|
| Redirect the root URL to student login (or whatever you prefer).
|
*/
Route::get('/', fn() => redirect()->route('login.student'));
// 