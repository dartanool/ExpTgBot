<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;

class SetLoginHandler
{
    public function handle(int $userId, string $login ): void
    {

        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_password', 'data' => $login]
        );

        Telegraph::message(' Введите пароль без пробелов.')->send();

    }

}

