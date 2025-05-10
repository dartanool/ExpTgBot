<?php

namespace App\Http\Services;

use Illuminate\Support\Collection;

class ApiService
{

    public function getCityId(array $response): int
    {
        return (int) ($response['result'][0]['ID_KG'] ?? 0);
    }

    public function getStationId(array $response): int
    {
        return (int) ($response['result'][0]['ID_MST'] ?? 0);
    }
}
