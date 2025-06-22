<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class MainKeyboard
{
    public static function handle(): ReplyKeyboard
    {
        return ReplyKeyboard::make()->buttons([
            ReplyButton::make('Установить станцию'),
            ReplyButton::make('📍 Отправить местоположение')->requestLocation(),
            ReplyButton::make('Список заданий'),
            ReplyButton::make('Выйти')
            ]);
    }
}
