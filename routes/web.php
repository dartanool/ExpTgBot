<?php
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Facades\Route;

use App\Http\Telegraph\Handlers\TelegramHandler;

//Route::post('/telegraph/webhook', function () {
//    // обработка webhook
//    return response('ok', 200);
//})->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
//Route::post('/telegraph/webhook', [TelegramHandler::class, 'help']);
Route::post('/telegraph/webhook', [TelegramHandler::class, 'start']);
//Route::middleware('auth')->post('/telegraph/webhook', [TelegramHandler::class, 'tasks']);

//dd(\Illuminate\Support\Facades\Http::post('https://api.telegram.org/bot7742137107:AAECWRGyxbsJw59HNnaE-_euuXWLLs7Oz10/sendMessage',[
//    'chat_id' => 689216709,
//    'text' => 'Hello'
//])->json()
//);

//Route::
    Route::get('/', function () {
    return view('welcome');
});
