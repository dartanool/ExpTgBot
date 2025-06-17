<?php

namespace App\Http\Services;

use App\DTO\GetAddressDTO;
use App\DTO\GetAddressListDTO;
use App\DTO\GetClientDTO;
use App\DTO\GetClientListDTO;
use App\DTO\GetTaskDTO;
use App\DTO\GetTasksListDTO;
use App\DTO\GetTtnTripDTO;
use App\DTO\GetTtnTripListDTO;
use App\Http\Services\Client\ExpeditorClient;
use DefStudio\Telegraph\Facades\Telegraph;

class ExpeditorApiService
{

    protected ExpeditorClient $expeditorClient;
    private string $method;

    public function __construct(int $userId)
    {
        $this->expeditorClient = new ExpeditorClient($userId);
        $this->method = 'rt';
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

    public function parseClientApiResponse(array $apiResponse): GetClientListDTO
    {
        $clients = [];
        $count = 1;
        foreach ($apiResponse['result'] as $item) {
            $clients[] = new GetClientDTO(
                id : $count,
                clientName: $item['CLIENT'],
                count: $item['CNT'],
                type: $item['TIP_NAME'],
            );
            $count++;
        }

        return new GetClientListDTO(
            success: $apiResponse['result'] === '1',
            clients: $clients,
        );
    }

    public function parseAddressApiResponse(array $apiResponse): GetAddressListDTO
    {
        $addresses = [];
        $count = 1;
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

        }

        return new GetAddressListDTO(
            success: $apiResponse['result'] === '1',
            addresses: $addresses,
        );
    }

    public function parseTtnApiResponse(array $apiResponse): GetTtnTripListDTO
    {
        $ttns = [];
        $count = 1;
        foreach ($apiResponse['result'] as $item) {
            $ttns[] = new GetTtnTripDTO(
                id: $count,
                idAexTtnTrip: $item['ID_AEX_TTNTRIP'],
                aexTtnTripIdRec:$item['AEX_TTNTRIP_ID_REC'],
                aexoTel: $item['AEXO_TEL'],
                idS72: $item['ID_S72'],
                prchVes: (float)$item['PRCH_VES'],
                prchObyom: (float)str_replace(',', '.', $item['PRCH_OBYOM']),
                prchCliMest:(int)$item['PRCH_CLI_MEST'],
                prchBagMest:(int)$item['PRCH_BAG_MEST'],
                prchStrNom: $item['PRCH_STR_NOM'],
            );
            $count++;
        }

        return new GetTtnTripListDTO(
            success: $apiResponse['result'] === '1',
            trips: $ttns,
        );
    }

    public function getTtnTripById(int $ttnTripId, array $ttns)
    {
        foreach ($ttns as $ttn) {
            if ($ttn->id === $ttnTripId) {
                return $ttn;
            }
        }
        throw new \Exception("Задание не найдено");
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
        throw new \Exception(" Address не найдено");

    }

    public function getAddressByAddressIdTripId(string $addressId, string $tripId) : GetAddressDTO
    {
        $addresses = $this->getAddressList($tripId);

        foreach ($addresses->addresses as $address){
            if ($address->id === $addressId)
                return $address;
        }
        throw new \Exception(" Address не найдено");

    }

    public function getClientByName(string $clientName, array $clients) :GetClientDTO
    {
        Telegraph::message("$clientName")->send();
        foreach ($clients as $client){
            if ($client->clientName === $clientName)
                return $client;
        }
        throw new \Exception(" Address не найдено");
    }


    public function getSession(string $login, string $password)
    {
        $data = [
            'Username' => $login,
            'Password' => $password
        ];

        $method ='GetSession';
        $response = $this->expeditorClient->auth($method, $data);


        return $response['Pragma'] ?? null;
    }

    public function getCityId(string $city)
    {
        $data = [
            'init' => [
                'type' => 'data',
                'report' => 'te.kg.r'
            ],
            'params' => [
                'KgName' => "{$city}%"
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return ($response['result'][0]['ID_KG'] ?? null);

    }

    public function getStationId(string $station, string $cityId)
    {
        $data = [
            'init' => [
                'type' => 'data',
                'report' => 'te.mst.r'
            ],
            'params' => [
                'idKg' => $cityId,
                'mstName' => "{$station}%"
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return ($response['result'][0]['ID_MST'] ?? null);

    }

    public function setUserStation(int $stationId)
    {
        $method = 'SetUserMst';

        $data = [
            'mst' => "$stationId"
        ];

        $response = $this->expeditorClient->send($method, $data);
        return $response['id_mst'] ?? null;
    }

    public function getTaskList() : GetTasksListDTO
    {
        $data = [
            'init' => [
                'type' => 'data',
                'report' => 'te.trips.r'
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return $this->parseApiResponse($response);
    }
    public function getCurrentTask(int $cityId)
    {
        $data = [
            'init' => [
                'type' => 'data',
                'report' => 'te.trip.r'
            ],
            'params' => [
                'idKg' => $cityId,
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return (string) ($response['result'][0]['ID_AEX_TRIP'] ?? 0);
    }

    //ПРЁМ СО СКЛАД

    public function acceptanceFromWarehouse(string $tripId)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.ttnList.r"
            ],
            "params" => [
                "idTrip" => $tripId,
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return $this->parseTtnApiResponse($response);
    }
    public function markAsRead(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.1.46.1",
                "eventIdTrip" => $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }

    public function moveByOrder(string $tripId, string $ttnTripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.2.72.0",
                "eventIdTrip" => 2252182255931561,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }
    public function completeAcceptation(string $tripId, int $ttnTripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.2.72.0",
                "eventIdTrip" => $tripId,
                "eventIdTtnTrip" => $ttnTripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
                "eventAddr"
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }

    public function cancelEvent(string $tripId)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.eventDel.w"
            ],
            "params" => [
                "eventId" =>  $tripId
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }

    public function finishAcceptation(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.eventDel.w"
            ],
            "params" => [
                "eventCode" => "st.1.76.2",
                "eventIdTrip" =>  $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }

    //ВЫПОЛНЕНИЕ ЗАДАНИЯ

    public function completeTask(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.1.47.0",
                "eventIdTrip" =>  $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon
            ]
        ];

        return $this->expeditorClient->send($this->method, $data);
    }
    public function getAddressList(string $tripId)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.addrList.r"
            ],
            "params" => [
                "idTrip" =>  $tripId,
            ]
        ];


        $response = $this->expeditorClient->send($this->method, $data);


        return $this->parseAddressApiResponse($response);
    }

    public function arrivedToAddress(string $tripId, string $eventLat, string $eventLon, string $address)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.2.56.0",
                "eventIdTrip" =>  $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
                "eventAddr" => $address
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);
    }
    public function leftAtAddress(string $tripId, string $eventLat, string $eventLon, string $address)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.2.57.0",
                "eventIdTrip" =>  $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
                "eventAddr" => $address
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);
    }
    public function getClientList(string $tripId, string $address)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.addrClientList.r"
            ],
            "params" => [
                "idTrip" =>  $tripId,
                "Addr" => $address
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);

        return $this->parseClientApiResponse($response);
    }


    //ЗАВЕРШЕНИЕ ЗАДАНИЯ
    public function arrivedToUnload(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.1.77.1",
        		"eventIdTrip" => $tripId,
       		    "eventLat" => $eventLat,
        		"eventLon" => $eventLon,
            ]
        ];

        $response = $this->expeditorClient->send($this->method, $data);
    }

    public function completeDelivery(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.1.77.2",
                "eventIdTrip" => $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
            ]
        ];
        $response = $this->expeditorClient->send($this->method, $data);
    }

    public function submitVehicleAndDocuments(string $tripId, string $eventLat, string $eventLon)
    {
        $data = [
            "init" => [
                "type" => "data",
                "report" => "te.event.w"
            ],
            "params" => [
                "eventCode" => "st.1.48.0",
                "eventIdTrip" => $tripId,
                "eventLat" => $eventLat,
                "eventLon" => $eventLon,
            ]
        ];
        $response = $this->expeditorClient->send($this->method, $data);
    }
}
