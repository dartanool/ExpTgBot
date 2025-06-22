<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetTtnTripDTO;
use DefStudio\Telegraph\Keyboard\Keyboard;

class WarehouseTtnsKeyboard
{
    public static function show(array $ttns, string $tripId)
    {
        $keyboard = Keyboard::make();

        foreach($ttns as $ttn) {

            $buttonText = sprintf(
                "📍 %s ",
                $ttn->prchStrNom);

            $keyboard->button($buttonText)
                ->action('selectTtnTrip')
                ->param('ttnId', $ttn->id)
                ->param('tripId', $tripId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Назад')->action('selectTrip')->param('tripId', $tripId);

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetTtnTripDTO $trip, string $tripId): Keyboard
    {
        return Keyboard::make()
            ->button('📍 Перемещение отправления на ТС по поручению  ')->action('moveByOrder')->param('tripId', $tripId)->param('ttnTripId',$trip->id)
            ->button('🔙 Окончил приём')->action('finishAcceptation')->param('tripId', $tripId)
            ->button('❌ Назад')->action('acceptanceFromWarehouse')->param('tripId', $tripId);
    }
}
