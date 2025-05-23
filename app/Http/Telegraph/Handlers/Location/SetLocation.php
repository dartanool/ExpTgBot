<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;

class SetLocation
{
    private int $chatId;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $chatId)
    {
        $this->expeditorApiService = new ExpeditorApiService($chatId);
        $this->chatId = $chatId;
    }
    public function handleLocation($location): void
    {
        TelegraphUserLocation::query()->where('user_id', $this->chatId)->update(
            [
                'event_lat' => $location->latitude(),
                'event_lon' => $location->longitude()
            ]
        );

    }
    public function location()
    {
        TelegraphUserState::query()->updateOrCreate(
            ['user_id' => $this->chatId],
            ['state' => 'awaiting_city', 'data' => null]
        );
        Telegraph::message('Введите сначала город. Пример: Москва')->send();
    }

    public function setCity(string $city)
    {
        $cityId = $this->expeditorApiService->getCityId($city);

        if (isset($cityId)) {
            TelegraphUserState::query()->updateOrCreate(
                ['user_id' => $this->chatId],
                ['state' => 'awaiting_station', 'data' => $cityId]
            );
            Telegraph::message('Введите станцию. Пример: Курская')->send();
        } else {
            Telegraph::message('Не нашли город. Повторите ввод города. Пример: Москва')->send();
        }
    }
}
