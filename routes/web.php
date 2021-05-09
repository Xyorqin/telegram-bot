<?php

use Illuminate\Support\Facades\Route;

Route::post('/action', 'TelegramController@index');
Route::get('/set-webhook', 'TelegramController@setWebhook');


Route::post(Telegram::getAccessToken(), 'TelegramController@action')->name('telegram')->middleware(\App\Http\Middleware\OnlyMessage::class);

Auth::routes();
