<?php

namespace App\Http\Telegraph\Handlers\Authorization;
use App\Http\Telegraph\API\GetSession;
use App\Http\Telegraph\Handlers\SetUserStation;
use App\Models\Telegraph\TelegraphUsers;
use App\Models\Telegraph\TelegramUserState;
use DefStudio\Telegraph\Facades\Telegraph;

class CompleteAuthHandler
{
    public function handle(int $userId, string $login, string $password): void
    {
        $getSession = new GetSession();
        $token = $getSession->handle($login, $password);

        if ($token) {
            Telegraph::message("Вы успешно авторизовались")->send();
            Telegraph::message("$token")->send();


            (new SetUserStation())->handle();

            TelegraphUsers::updateOrCreate(
                ['user_id' => $userId],
                ['token' => $token]
            );

            TelegramUserState::where('user_id', $userId)->delete();
        }
    }
}
