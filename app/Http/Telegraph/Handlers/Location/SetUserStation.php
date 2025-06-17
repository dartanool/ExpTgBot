<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use DefStudio\Telegraph\Models\TelegraphChat;

class SetUserStation
{
    public TelegraphChat $chat;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
    }
    public function handle(string $stationId)
    {
        $response = $this->expeditorApiService->setUserStation($stationId);
        $this->chat->message("Станция успешно установлена")->send();

//       if ($response->status() == 200)
//        {
//            $this->chat->message("Станция успешно установлена")->send();
//        } else {
//           $this->chat->message("Попробуйте еще раз установить станцию")->send();
//        }
    }
}
