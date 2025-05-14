<?php

namespace App\Http\Services;

use App\DTO\GetAddressDTO;
use App\DTO\GetAddressListDTO;
use App\DTO\GetTaskDTO;
use App\DTO\GetTasksListDTO;
use App\Http\Services\Client\ExpeditorClient;
use App\Models\Telegraph\TelegraphUsers;
use DefStudio\Telegraph\Facades\Telegraph;
use Illuminate\Support\Collection;

class ExpeditorApiService
{

    protected ExpeditorClient $expeditorClient;
    private string $token;

    public function __construct(int $userId)
    {
        $this->expeditorClient = new ExpeditorClient();

        $this->token = TelegraphUsers::query()->where('user_id', $userId)->first()->token;
    }




    public function parseApiResponse(array $apiResponse): GetTasksListDTO
    {
        $trips = [];

        foreach ($apiResponse['result'] as $tripData) {
            $trips[] = new GetTaskDTO(
                id: $tripData['ID_AEX_TRIP'],
                trsId: $tripData['AEX_TRIP_ID_TRS'],
                carNumber: $tripData['TRS_SID'],
                startDate: $tripData['AEX_TRIP_DT_BG'],
                endDate: $tripData['AEX_TRIP_DT_END'],
                cityId: $tripData['ID_KG'],
                cityName: $tripData['KG_NAME'],
                totalTasks: (int)$tripData['Z_CNT'],
                deliveryTasksCount: (int)$tripData['Z_DST_CNT'],
                pickupTasksCount: (int)$tripData['Z_ZBR_CNT'],
                deliveryWeight: (float)str_replace(',', '.', $tripData['Z_DST_VES']),
                pickupWeight: (float)str_replace(',', '.', $tripData['Z_ZBR_VES']),
                deliveryVolume: (float)str_replace(',', '.', $tripData['Z_DST_OBYOM']),
                pickupVolume: (float)str_replace(',', '.', $tripData['Z_ZBR_OBYOM']),
                statusReady: (int)$tripData['S71'],
                statusCompleted: (int)$tripData['S48'],
            );
        }

        return new GetTasksListDTO(
            success: $apiResponse['result'] === '1',
            trips: $trips,
        );
    }

    public function parseAddressApiResponse(array $apiResponse): GetAddressListDTO
    {
        $addresses = [];
        $count = 0;
        foreach ($apiResponse['result'] as $item) {
            $addresses[] = new GetAddressDTO(
                id : $count,
                address: $item['AEXO_ADR'],
                lat: (float)str_replace(',', '.', $item['AEXO_ADR_LAT']),
                lon: (float)str_replace(',', '.', $item['AEXO_ADR_LON']),
                workHours: $item['AEXO_TWORK_STOR'],
                clientName: $item['ADDR_CLIENT']

            );
            $count++;

//            Telegraph::message("{$item['AEXO_ADR']}")->send();

        }
//        Telegraph::message('Список адресов4')->send();


        return new GetAddressListDTO(
            success: $apiResponse['result'] === '1',
            addresses: $addresses,
        );
    }

    public function getTripById(string $tripId, array $trips): GetTaskDTO
    {
        foreach ($trips as $trip) {
            if ($trip->id === $tripId) {
                return $trip;
            }
        }

        throw new \Exception("Задание не найдено");
    }

    public function getAddressById(string $addressId, array $addresses) : GetAddressDTO
    {
        foreach ($addresses as $address){
            if ($address->id === $addressId)
                return $address;
        }
        throw new \Exception("Address не найдено");

    }


    public function getSession(string $login, string $password)
    {
        $data = [
            'Username' => $login,
            'Password' => $password
        ];

        $method ='GetSession';
        $response = $this->expeditorClient->auth($method, $data);

        return $response['Pragma'];
    }

    public function getCityId(int $userId, string $city)
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token",
            'init' => [
                'type' => 'data',
                'report' => 'te.kg.r'
            ],
            'params' => [
                'KgName' => "{$city}%"
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return (int) ($response['result'][0]['ID_KG'] ?? 0);

    }

    public function getStationId(int $userId, string $station, string $cityId)
    {
        $method = 'rt';


        $data = [
            'Pragma' => "$this->token",
            'init' => [
                'type' => 'data',
                'report' => 'te.mst.r'
            ],
            'params' => [
                'idKg' => $cityId,
                'mstName' => "{$station}%"
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return (int) ($response['result'][0]['ID_MST'] ?? 0);

    }

    public function setUserStation(int $userId, int $stationId)
    {
        $method = 'SetUserMst';

        $data = [
            'Pragma' => "$this->token",
            'mst' => "$stationId"
        ];

        $this->expeditorClient->send($method, $data);
    }

    public function getTaskList(int $userId) : GetTasksListDTO
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token ",
            'init' => [
                'type' => 'data',
                'report' => 'te.trips.r'
            ]
        ];

        $response = $this->expeditorClient->send($method, $data);

        return $this->parseApiResponse($response);
    }

    public function completeAcceptation(string $tripId)
    {
        $method = 'rt';
        $data = [
            'Pragma' => "$this->token ",
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.2.72.0",
                "eventIdTrip" => $tripId,
            ]
        ];

        return $this->expeditorClient->send($method, $data);
    }

    public function cancelEvent(string $tripId)
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token ",
            "init" => [
                "type" => "data",
                "report" => "te.eventDel.w"
            ],
            "params" => [
                "eventId" =>  $tripId
            ]
        ];

        return $this->expeditorClient->send($method, $data);
    }

    public function finishAcceptation(string $tripId)
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token ",
            "init" => [
                "type" => "data",
                "report" => "te.eventDel.w"
            ],
            "params" => [
                "eventCode" => "st.1.76.2",
                "eventId" =>  $tripId,
            ]
        ];

        return $this->expeditorClient->send($method, $data);
    }
    public function getAddressList(string $tripId)
    {
        $method = 'rt';

        $data = [
            'Pragma' => "$this->token ",
            "init" => [
                "type" => "data",
                "report" => "te.addrList.r"
            ],
            "params" => [
                "idTrip" =>  $tripId,
            ]
        ];


        $response = $this->expeditorClient->send($method, $data);


        return $this->parseAddressApiResponse($response);
    }

}
