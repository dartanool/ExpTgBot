<?php

namespace App\Http\Telegraph\Handlers\Authorization;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\MainKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Models\TelegraphChat;

class CompleteAuthHandler
{
    private ExpeditorApiService $expeditorApiService;
    private TelegraphChat $chat;
    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }
    public function handle(string $login, string $password): void
    {
        $token = $this->expeditorApiService->getSession($login, $password);

        if (isset($token)) {
            $this->chat->message("Вы успешно авторизовались")->replyKeyboard(MainKeyboard::handle())->send();

            TelegraphUsers::updateOrCreate(
                ['user_id' => $this->chat->chat_id],
                ['token' => $token]
            );

            TelegraphUserState::where('user_id', $this->chat->chat_id)->delete();
        } else {
            $this->chat->message("Повторите регистрацию")->send();

        }
    }
}
