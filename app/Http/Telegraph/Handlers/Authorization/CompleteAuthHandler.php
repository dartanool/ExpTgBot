<?php

namespace App\Http\Telegraph\Handlers\Authorization;
use App\Http\Telegraph\API\GetSessionAPI;
use App\Http\Telegraph\Handlers\Location\SetUserStation;
use App\Http\Telegraph\Keyboards\TaskKeyboard;
use App\Http\Telegraph\Keyboards\TaskListKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Facades\Telegraph;

class CompleteAuthHandler
{
    public function handle(int $userId, string $login, string $password): void
    {
        $getSession = new GetSessionAPI();
        $token = $getSession->handle($login, $password);

        if ($token) {
            Telegraph::message("Вы успешно авторизовались")->replyKeyboard(TaskKeyboard::handle())->send();
            Telegraph::message("$token")->send();


            TelegraphUsers::updateOrCreate(
                ['user_id' => $userId],
                ['token' => $token]
            );

            TelegraphUserState::where('user_id', $userId)->delete();
        }
    }
}
