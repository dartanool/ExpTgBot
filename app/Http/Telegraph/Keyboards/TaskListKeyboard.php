<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetTasksListDTO;
use DefStudio\Telegraph\Keyboard\Keyboard;

class TaskListKeyboard
{

    public static function handle(array $tasks)
    {
        $keyboard = Keyboard::make();

        foreach($tasks as $task) {
            $buttonText = sprintf(
                "🚛 %s | %s-%s | %s",
                $task->carNumber,
                date('H:i', strtotime($task->startDate)),
                date('H:i', strtotime($task->endDate)),
                $task->cityName);

            $keyboard->button($buttonText)
                ->action('select_trip')
                ->param('trip_id', $task->id);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }
}
