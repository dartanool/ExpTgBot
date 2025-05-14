<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetAddressDTO;
use App\DTO\GetTaskDTO;
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
                ->param('addressId', $address->id)
                ->param('tripId', $tripId);
        }

        // Добавляем кнопку "Отмена"
        $keyboard->button('❌ Отмена')->action('cancel_trips');

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetAddressDTO $address, string $tripId): Keyboard
    {
        return Keyboard::make()
            ->button('✅Прибыл по адресу')->action('arrivedToAddress')->param('addressId', $address->id)->param('tripId', $tripId)
            ->button('✅Убыл по адресу')->action('leftAtTheAddress')->param('addressId', $address->id)->param('tripId', $tripId)
            ->button('✅Список клиентов по указанному адресу')->action('getClientList')->param('addressId', $address->id)->param('tripId', $tripId);
    }

}
