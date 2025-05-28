<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Http\Services\TelegraphService;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class SetLoginHandler
{
    public TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
    }
    public function handle(string $login ): void
    {

        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $this->chat->chat_id],
            ['state' => 'awaiting_password', 'data' => $login]
        );
        $this->chat->message('Введите пароль без пробелов.')->send();

    }

}

