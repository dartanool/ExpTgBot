<?php

namespace App\DTO;

class CompleteTaskGetTtnTripDTO
{
    public int $id;
    public string $ID_AEX_TTNTRIP;       // ID поручения
    public string $ID_AEXO;               // Заявка
    public string $AEX_TSTTNTRIP_STATE_PR; // Статус
    public string $AEX_TTNTRIP_TIP;       // Тип поручения
    public string $AEXO_PRIM;             // Примечание по заявке
    public string $CLIENT_TIP_NAME;       // Тип поручения + Клиент
    public string $AEXO_TEL;              // Телефоны
    public string $AEXO_TWORK_STOR;       // Время работы Клиента
    public string $AEXO_LAT_ADR;          // Широта
    public string $AEXO_LON_ADR;          // Долгота
    public string $ID_S78X;               // Оплата наличными или по QR
    public string $ID_S90;                // Фотофиксация
    public string $ID_S101;               // QRCode на оплату
    public ?string $S101_PTIP;            // Подтип генерации QR кода
    public ?string $S101_TXT;             // QR код
    public ?string $S101_PRIM;            // Описание
    public ?string $VTTN_STR_NOM_GR;      // Строковый номер груза
    public string $VTTN_PLAN_SUM;         // Плановая сумма по ТТН
    public string $PRCH_VES;              // Вес
    public string $PRCH_OBYOM;            // Объем
    public string $PRCH_CLI_MEST;         // Количество клиентских мест
    public string $PRCH_BAG_MEST;         // Количество багажных мест'
    public GetClientDTO $clientDTO;
    public function __construct(int $count, array $data)
    {
        $this->id = $count;
        $this->ID_AEX_TTNTRIP = $data['ID_AEX_TTNTRIP'] ?? '';
        $this->ID_AEXO = $data['ID_AEXO'] ?? '';
        $this->AEX_TSTTNTRIP_STATE_PR = $data['AEX_TSTTNTRIP_STATE_PR'] ?? '';
        $this->AEX_TTNTRIP_TIP = $data['AEX_TTNTRIP_TIP'] ?? '';
        $this->AEXO_PRIM = $data['AEXO_PRIM'] ?? '';
        $this->CLIENT_TIP_NAME = $data['CLIENT_TIP_NAME'] ?? '';
        $this->AEXO_TEL = $data['AEXO_TEL'] ?? '';
        $this->AEXO_TWORK_STOR = $data['AEXO_TWORK_STOR'] ?? '';
        $this->AEXO_LAT_ADR = $data['AEXO_LAT_ADR'] ?? '';
        $this->AEXO_LON_ADR = $data['AEXO_LON_ADR'] ?? '';
        $this->ID_S78X = $data['ID_S78X'] ?? '';
        $this->ID_S90 = $data['ID_S90'] ?? '';
        $this->ID_S101 = $data['ID_S101'] ?? '';
        $this->S101_PTIP = $data['S101_PTIP'] ?? null;
        $this->S101_TXT = $data['S101_TXT'] ?? null;
        $this->S101_PRIM = $data['S101_PRIM'] ?? null;
        $this->VTTN_STR_NOM_GR = $data['VTTN_STR_NOM_GR'] ?? null;
        $this->VTTN_PLAN_SUM = $data['VTTN_PLAN_SUM'] ?? '';
        $this->PRCH_VES = $data['PRCH_VES'] ?? '';
        $this->PRCH_OBYOM = $data['PRCH_OBYOM'] ?? '';
        $this->PRCH_CLI_MEST = $data['PRCH_CLI_MEST'] ?? '';
        $this->PRCH_BAG_MEST = $data['PRCH_BAG_MEST'] ?? '';
    }
    public function setClient(GetClientDTO $clientDTO)
    {
        $this->clientDTO = $clientDTO;
    }
}
