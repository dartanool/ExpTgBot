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
            TelegraphUsers::query()->updateOrCreate(
                ['user_id' => $this->chat->chat_id],
                ['token' => $token]
            );
            $this->chat->message("Вы успешно авторизовались")->send();

            $this->chat->message("Выполните действия в следующем порядке:\n\n"
                . "1️⃣ Нажмите кнопку «Установить станцию»\n"
                . "2️⃣ Затем — «Определить местоположение»\n"
                . "3️⃣ После этого станет доступна кнопка «Список заданий»\n\n"
                . "Это необходимо для корректной работы бота и получения актуальной информации.")->replyKeyboard(MainKeyboard::handle())->send();

            TelegraphUserState::query()->where('user_id', $this->chat->chat_id)->delete();
        } else {
            $this->chat->message("Логин или пароль неверен. Повторите регистрацию")->send();
            TelegraphUserState::query()->where('user_id', $this->chat->chat_id)->delete();
        }
    }
}
