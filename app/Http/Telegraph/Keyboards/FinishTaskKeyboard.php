<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;

class FinishTaskKeyboard
{
    public static function handle(string $tripId): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('Прибыл для разгрузки')->action('arrivedToUnload')->param('tripId', $tripId),
                Button::make('Окончил сдачу груза')->action('completeDelivery')->param('tripId', $tripId),
                Button::make('Поставил ТС и сдал документы')->action('submitVehicleAndDocuments')->param('tripId', $tripId),
                Button::make('❌ Назад')->action('selectTrip')->param('tripId', $tripId)
            ]);
    }
}
