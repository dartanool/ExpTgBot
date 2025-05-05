<?php

namespace App\Http\Telegraph\Handlers\Authorization;

use App\Models\Telegraph\TelegramUserState;
use DefStudio\Telegraph\Facades\Telegraph;

class SetPasswordHandler
{
    public function handle(int $userId, string $password) : void
    {

        $data = TelegramUserState::query()->where('user_id', $userId)->first();

        (new CompleteAuthHandler())->handle($userId, $data->data, $password);
    }
}
