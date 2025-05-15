<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetTaskDTO;
use App\DTO\GetTasksListDTO;
use DefStudio\Telegraph\Keyboard\Keyboard;

class TaskListKeyboard
{
    public static function handle(array $trips)
    {
        $keyboard = Keyboard::make();

        foreach($trips as $trip) {
            $buttonText = sprintf(
                "🚛 %s | %s-%s | %s",
                $trip->id,
                date('H:i', strtotime($trip->startDate)),
                date('H:i', strtotime($trip->endDate)),
                $trip->cityName);

            $keyboard->button($buttonText)
                ->action('selectTripWareHouse')
                ->param('tripId', $trip->id);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetTaskDTO $trip): Keyboard
    {
        return Keyboard::make()
            ->button('✅ Груз погружен')->action('completeAcceptation')->param('tripId', $trip->id)
            ->button('📍 Отмена события')->action('cancelEvent')->param('tripId', $trip->id)
            ->button('🔙 Окончил приём')->action('finishAcceptation')->param('tripId', $trip->id)    ;
    }
}
