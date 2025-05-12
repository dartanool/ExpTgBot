<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class TaskKeyboard
{
    public static function handle(): ReplyKeyboard
    {
        return ReplyKeyboard::make()->buttons([
            ReplyButton::make('Установить станцию'),
            ReplyButton::make('Список заданий'),
            ReplyButton::make('Приём со склада'),
            ReplyButton::make('Выполнить заданий'),
            ReplyButton::make('Завершить заданий '),
            ]);
    }
}
