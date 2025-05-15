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
            ReplyButton::make('Список заданий')->requestLocation(),
//            ReplyButton::make('Приём со склада'),
//            ReplyButton::make('Выполнение задания'),
//            ReplyButton::make('Завершить заданий '),
            ]);
    }
}
