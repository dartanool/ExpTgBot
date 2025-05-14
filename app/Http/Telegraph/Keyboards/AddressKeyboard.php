<?php

namespace App\Http\Telegraph\Keyboards;

use DefStudio\Telegraph\Facades\Telegraph;
use DefStudio\Telegraph\Keyboard\Keyboard;

class AddressKeyboard
{
    public static function show(array $addresses, string $tripId)
    {
        $keyboard = Keyboard::make();

        foreach($addresses as $address) {

            $buttonText = sprintf(
                "📍 %s | %s",
                $address->id,
                $address->clientName);

            $keyboard->button($buttonText)
                ->action('selectAddress')
                ->param('id', $address->id)
                ->param('tripId', $tripId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }

}
