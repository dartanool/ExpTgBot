<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Models\TelegraphChat;

class SetStation
{
    private TelegraphChat $chat;
    private ExpeditorApiService $expeditorApiService;
    private SetUserStation $setUserStation;

    public function __construct(TelegraphChat $chat)
    {
        $this->chat = $chat;
        $this->expeditorApiService = new ExpeditorApiService($chat->chat_id);
        $this->setUserStation = new SetUserStation($chat);
    }
    public function handle(string $station)
    {
        $cityId = TelegraphUserState::query()->where('user_id', $this->chat->chat_id)->first()->data;

        $stationId = $this->expeditorApiService->getStationId($station, $cityId);

        if (isset($stationId)) {
            $this->setUserStation->handle($stationId);

            TelegraphUserLocation::query()->updateOrCreate([
                'user_id' => $this->chat->chat_id,
                'city_id' => $cityId,
                'station_id' => $stationId,
            ]);

            TelegraphUserState::where('user_id', $this->chat->chat_id)->delete();
        } else {
            $this->chat->message('Не нашли станцию. Повторите ввод города. Пример: Курская')->send();

        }

    }
}
