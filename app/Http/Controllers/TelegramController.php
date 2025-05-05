<?php

namespace App\Http\Controllers;

class TelegramController
{
    private $chat;

    public function start(): void
    {
        $this->chat->message('Привет! Добро пожаловать в нашего бота!')->send();
    }
}
