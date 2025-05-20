<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class StartKeyboard
{
    public static function handle(): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('🔐 Авторизация')->action('auth'),
                Button::make('ℹ️ Помощь')->action('help'),
            ])
            ->chunk(2);
    }
}
