<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Keyboard\Button;
use DefStudio\Telegraph\Keyboard\Keyboard;
use DefStudio\Telegraph\Keyboard\ReplyButton;
use DefStudio\Telegraph\Keyboard\ReplyKeyboard;

class ActionKeyboard
{
    public static function handle(string $tripId): Keyboard
    {
        return Keyboard::make()
            ->buttons([
                Button::make('Приём со склада')->action('acceptanceFromWarehouse')->param('tripId', $tripId),
                Button::make('Выполнение задания')->action('completeTask')->param('tripId', $tripId),
                Button::make('Завершить задание')->action('finishTask')->param('tripId', $tripId),
                Button::make('❌ Назад')->action('showTripsList')
            ]);
    }
}
