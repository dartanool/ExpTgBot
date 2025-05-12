<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\ExpeditorApiService;
use DefStudio\Telegraph\Facades\Telegraph;

class SetUserStation
{
    private int $chatId;
    private ExpeditorApiService $expeditorApiService;

    public function __construct(int $chatId)
    {
        $this->chatId = $chatId;
        $this->expeditorApiService = new ExpeditorApiService($chatId);
    }
    public function handle( string $stationId)
    {
        $this->expeditorApiService->setUserStation($this->chatId, $stationId);

        Telegraph::message("успешно");
    }
}
