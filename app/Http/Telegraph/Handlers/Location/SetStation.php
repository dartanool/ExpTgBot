<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Telegraph\API\Location\GetStationIdAPI;
use App\Models\Telegraph\TelegraphUserLocation;
use App\Models\Telegraph\TelegraphUserState;

class SetStation
{
    private int $chatId;
    private SetUserStation $setUserStation;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
        $this->setUserStation = new SetUserStation();
    }
    public function handle(string $station)
    {
        $cityId = TelegraphUserState::query()->where('user_id', $this->chatId)->first()->data;

        $stationId = new GetStationIdAPI();
        $stationId = $stationId->handle($this->chatId, $station, $cityId);


        if ($stationId) {
            $this->setUserStation->handle($this->chatId, $stationId);

            TelegraphUserLocation::query()->updateOrCreate([
                'user_id' => $this->chatId,
                'city_id' => $cityId,
                'station_id' => $stationId,
            ]);

            TelegraphUserState::where('user_id', $this->chatId)->delete();


        }

    }
}
