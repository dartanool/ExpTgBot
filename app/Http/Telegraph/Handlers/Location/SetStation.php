<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\TelegraphService;
use App\Http\Telegraph\API\Location\GetCityIdAPI;
use App\Http\Telegraph\API\Location\GetStationIdAPI;
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
        $stationId = new GetStationIdAPI();
        $stationId = $stationId->handle($this->chatId, $station);

        if ($stationId) {
            $this->setUserStation->handle($this->chatId, $stationId);
            TelegraphUserState::where('user_id', $this->chatId)->delete();


        }

    }
}
