<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetTaskDTO;
use DefStudio\Telegraph\Keyboard\Keyboard;

class TaskListKeyboard
{
    public static function handle(array $trips)
    {
        $keyboard = Keyboard::make();

        foreach($trips as $trip) {
            $buttonText = sprintf(
                "🚛  %s-%s | %s-%s | %s",
                date('d.m', strtotime($trip->startDate)),
                date('d.m', strtotime($trip->endDate)),
                date('H:i', strtotime($trip->startDate)),
                date('H:i', strtotime($trip->endDate)),
                $trip->cityName);

            $keyboard->button($buttonText)
                ->action('selectTrip')
                ->param('tripId', $trip->id);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('')->action('cancel_trips');

        return $keyboard;
    }

}
