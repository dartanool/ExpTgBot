<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Models\TelegraphChat;

class SetLocation
{
    private TelegraphChat $chat;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(TelegraphChat $chat)
    {
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
        $this->chat = $chat;
    }
    public function handleLocation($location): void
    {
        TelegraphUserLocation::query()->where('user_id', $this->chat->chat_id)->update(
            [
                'event_lat' => $location->latitude(),
                'event_lon' => $location->longitude()
            ]
        );

    }
    public function location()
    {
        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $this->chat->chat_id],
            ['state' => 'awaiting_city', 'data' => null]
        );
        $this->chat->message('Введите сначала город. Пример: Москва')->send();
//        Telegraph::message('')->send();
    }

    public function setCity(string $city)
    {
        $cityId = $this->expeditorApiService->getCityId($city);

        if (isset($cityId)) {
            TelegraphUserState::query()->updateOrCreate(
                ['user_id' => $this->chat->chat_id],
                ['state' => 'awaiting_station', 'data' => $cityId]
            );
            $this->chat->message('Введите станцию. Пример: Курская')->send();
//            Telegraph::message('Введите станцию. Пример: Курская')->send();
        } else {
            $this->chat->message('Не нашли город. Повторите ввод города. Пример: Москва')->send();
//            Telegraph::message('')->send();
        }
    }
}
