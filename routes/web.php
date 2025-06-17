<?php
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Facades\Route;


//Route::post('/telegraph/webhook', [TelegramHandler::class, 'start']);
//Route::post('/telegraph/webhook', [\App\Http\Controllers\TelegramController::class, 'start']);

    Route::get('/', function () {
    return view('welcome');
});
