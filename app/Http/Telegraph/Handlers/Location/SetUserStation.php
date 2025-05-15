<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use DefStudio\Telegraph\Facades\Telegraph;

class SetUserStation
{
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $chatId)
    {
        $this->expeditorApiService = new ExpeditorApiService($chatId);
    }
    public function handle(string $stationId)
    {
        $response = $this->expeditorApiService->setUserStation($stationId);

        if ($response->status() == 200)
        {
            Telegraph::message("Станция успешно установлена")->send();
        } else {
            Telegraph::message("Попробуйте еще раз установить станцию")->send();

        }
    }
}
