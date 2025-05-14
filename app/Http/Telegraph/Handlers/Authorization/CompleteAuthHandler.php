<?php

namespace App\Http\Telegraph\Handlers\Authorization;
use App\Http\Services\ExpeditorApiService;
use App\Http\Telegraph\Keyboards\MainKeyboard;
use App\Models\Telegraph\TelegraphUserState;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Facades\Telegraph;

class CompleteAuthHandler
{
    private ExpeditorApiService $expeditorApiService;
    private int $chatId;
    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
        $this->expeditorApiService = new ExpeditorApiService($chatId);
    }
    public function handle(string $login, string $password): void
    {
        $token = $this->expeditorApiService->getSession($login, $password);

        if ($token) {
            Telegraph::message("Вы успешно авторизовались")->replyKeyboard(MainKeyboard::handle())->send();

            TelegraphUsers::updateOrCreate(
                ['user_id' => $this->chatId],
                ['token' => $token]
            );

            TelegraphUserState::where('user_id', $this->chatId)->delete();
        }
    }
}
