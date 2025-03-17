<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DocumentController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dbconn', function(){ 
    return view('dbconn'); 
    });

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/documents/upload', [DocumentController::class, 'upload'])->name('documents.upload');
    Route::delete('/documents/remove/{id}', [DocumentController::class, 'remove'])->name('documents.remove');
    Route::get('/documents/check-missing', [DocumentController::class, 'checkMissingDocs'])->name('documents.checkMissingDocs');