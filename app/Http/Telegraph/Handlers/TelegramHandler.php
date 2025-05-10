<?php

namespace App\Http\Telegraph\Handlers;

use App\Http\Telegraph\Handlers\Authorization\SetLoginHandler;
use App\Http\Telegraph\Handlers\Authorization\SetPasswordHandler;
use App\Http\Telegraph\Keyboards\StartKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Handlers\WebhookHandler;
use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use Illuminate\Support\Stringable;


class TelegramHandler extends WebhookHandler
{

    public function start(): void
    {
        Telegraph::message('Добро пожаловать. Вам необходимо авторизоваться.')
            ->keyboard(StartKeyboard::handle())->send();
    }


    public function auth(): void
    {
        $userId = $this->chat->chat_id;

        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $userId],
            ['state' => 'awaiting_login', 'data' => null]
        );
        Telegraph::message(' Введите сначала логин. Пример: Иванов И.В.')->send();

    }


    public function handleChatMessage(Stringable $text): void
    {

        $userId = $this->message->from()->id();
        $userState = TelegraphUserState::query()->where('user_id', $userId)->first();

        switch ($text->toString()) {
            case 'Список заданий':
                (new GetTaskList($userId))->handle($userId);
                break;
        }

        if (!$userState){
            $this->reply("Нажмите /start");
            return ;
        }

        switch ($userState->state) {
            case 'awaiting_login':
                (new SetLoginHandler())->handle($userId, $text->toString());
                break;

            case 'awaiting_password':
                (new SetPasswordHandler())->handle($userId, $text->toString());
                break;

        }


    }



}
