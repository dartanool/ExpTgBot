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
                $trip->carNumber,
                date('H:i', strtotime($trip->startDate)),
                date('H:i', strtotime($trip->endDate)),
                $trip->cityName);

            $keyboard->button($buttonText)
                ->action('selectTrip')
                ->param('tripId', $trip->id);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetTaskDTO $trip): Keyboard
    {
        return Keyboard::make()
            ->button('✅ Подтвердить выполнение')->action('completeTrip')->param('tripId', $trip->id)
            ->button('📍 Отметить прибытие')->action('arriveTrip')->param('tripId', $trip->id)
            ->button('🔙 Назад к списку')->action('showTripsList');
    }
}
