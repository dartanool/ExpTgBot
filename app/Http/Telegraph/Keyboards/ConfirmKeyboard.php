<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Models\TelegraphBot;

class ConfirmKeyboard
{

    public function handle(string $message)
    {
        $bot = TelegraphBot::first(); // Ваш бот

        $chatId = 123456789; // ID чата пользователя

        $message = $bot->message("$message")
            ->keyboard(
                Keyboard::make()
                    ->button('Да')->action('confirm_delivery')->param('id', 1)
                    ->button('Нет')->action('cancel_delivery')->param('id', 0)
            )
            ->send();

    }
}
