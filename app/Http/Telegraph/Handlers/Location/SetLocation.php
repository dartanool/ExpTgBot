<?php

namespace App\Http\Telegraph\Handlers\Location;

use App\Http\Services\TelegraphService;
use App\Http\Telegraph\API\GetSessionAPI;
use App\Http\Telegraph\API\GetTaskListAPI;
use App\Http\Telegraph\API\Location\GetCityIdAPI;
use App\Models\Telegraph\TelegraphUsers;
use App\Models\Telegraph\TelegraphUserState;
use DefStudio\Telegraph\Facades\Telegraph;

class SetLocation
{
    private TelegraphService $telegraphService;
    private int $chatId;

    public function __construct(int $chatId)
    {
        $this->telegraphService = new TelegraphService($chatId);
        $this->chatId = $chatId;
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
        $getCityId = new GetCityIdAPI();
        Telegraph::message("$city")->send();

        $cityId = $getCityId->handle($this->chatId, $city);

        Telegraph::message("$cityId")->send();


        if ($cityId) {
            Telegraph::message("Вы успешно авторизовались")->send();
            Telegraph::message("$cityId")->send();

            TelegraphUserState::query()->updateOrCreate(
                ['user_id' => $this->chatId],
                ['state' => 'awaiting_city', 'data' => $cityId]
            );

        }


    }
}
