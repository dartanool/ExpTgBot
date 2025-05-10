<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Telegraph\API\Location\SetUserStationAPI;
use App\Http\Telegraph\Keyboards\TaskKeyboard;
use DefStudio\Telegraph\Facades\Telegraph;

class SetUserStation
{
    public function handle(int $userId, string $stationId)
    {
        $setStation = new SetUserStationAPI();
        $setStation->handle($userId, $stationId);

        Telegraph::message("На какой станции вы находитесь")->replyKeyboard(TaskKeyboard::handle())->send();
    }
}
