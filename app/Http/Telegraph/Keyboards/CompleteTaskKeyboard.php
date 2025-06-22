<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\CompleteTaskGetTtnTripDTO;
use App\DTO\GetTaskDTO;
use App\DTO\GetTtnTripListDTO;
use DefStudio\Telegraph\Keyboard\Keyboard;

class CompleteTaskKeyboard
{
    public static function handle(array $trips)
    {
        $keyboard = Keyboard::make();

        foreach($trips as $trip) {
            $buttonText = sprintf(
                "🚛 %s | %s-%s | %s",
                $trip->carNumber,
                date('H:i', strtotime($trip->startDate)),
                date('H:i', strtotime($trip->endDate)),
                $trip->cityName);

            $keyboard->button($buttonText)
                ->action('selectTripTask')
                ->param('tripId', $trip->id);
        }

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetTaskDTO $trip): Keyboard
    {
        return Keyboard::make()
            ->button('✅Список адресов')->action('getAddressList')->param('tripId', $trip->id)
            ->button('❌ Назад')->action('selectTrip')->param('tripId', $trip->id);

    }

    public static function buildTripOrdersKeyboard(array $ttns, string $data)
    {
        $keyboard = Keyboard::make();

        foreach($ttns as $ttn) {

            $buttonText = sprintf(
                "📍 %s ",
                $ttn->ID_AEX_TTNTRIP );

            $keyboard->button($buttonText)
                ->action('completeTaskSelectTtnTrip')
                ->param('ttnId', $ttn->id)
                ->param('data', $data);
        }

        return $keyboard;
    }
//Событие
    public static function createDetailsKeyboardForEvent(CompleteTaskGetTtnTripDTO $ttn): Keyboard
    {
        return Keyboard::make()
            ->button('Получение отправления (ТТН) по поручению')->action('setTtnStatusReceived')->param('ttnId', $ttn->ID_AEX_TTNTRIP)
            ->button('Выдача отправления (ТТН) по поручению')->action('setTtnStatusIssued')->param('ttnId',$ttn->ID_AEX_TTNTRIP)
            ->button('Поручение не выполнено')->action('failOrder')->param('ttnId',$ttn->ID_AEX_TTNTRIP)
            ->button('❌ Назад')->action('selectTrip')->param('ttnId', $ttn->id);

    }
}
