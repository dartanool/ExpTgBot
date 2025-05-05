<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegramUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\EmptyWebhookHandler;

class SetLoginHandler implements AuthServiceInterface
{
    public function handle(string $text = null): void
    {
        EmptyWebhookHandler::query()->reply("login");
        $this->reply("login");

        $userId = $this->message->from()->id();

        TelegramUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_login', 'data' => null]
        );
        Telegraph::message(' Введите сначала логин. Пример: Иванов И.В.')->send();

    }

}

