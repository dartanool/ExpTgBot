<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;

class SetStation
{
    private int $chatId;
    private ExpeditorApiService $expeditorApiService;
    private SetUserStation $setUserStation;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
        $this->expeditorApiService = new ExpeditorApiService($chatId);
        $this->setUserStation = new SetUserStation($this->chatId);
    }
    public function handle(string $station)
    {
        $cityId = TelegraphUserState::query()->where('user_id', $this->chatId)->first()->data;

        $stationId = $this->expeditorApiService->getStationId($station, $cityId);


        if ($stationId) {
            $this->setUserStation->handle($stationId);

            TelegraphUserLocation::query()->updateOrCreate([
                'user_id' => $this->chatId,
                'city_id' => $cityId,
                'station_id' => $stationId,
            ]);

            TelegraphUserState::where('user_id', $this->chatId)->delete();


        }

    }
}
