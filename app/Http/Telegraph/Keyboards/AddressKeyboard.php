<?php

namespace App\Http\Telegraph\Keyboards;

use App\DTO\GetAddressDTO;
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
                $address->address);

            $keyboard->button($buttonText)
                ->action('selectAddress')
                ->param('addressId', $address->id)
                ->param('tripId', $tripId);
        }

        return $keyboard;
    }

    public static function createDetailsKeyboard(GetAddressDTO $address, string $tripId): Keyboard
    {
        return Keyboard::make()
            ->button('✅Убыл по адресу')->action('leftAtAddress')->param('addressId', $address->id)->param('tripId', $tripId)
            ->button('✅Прибыл по адресу')->action('arrivedToAddress')->param('addressId', $address->id)->param('tripId', $tripId)
            ->button('✅Список клиентов по указанному адресу')->action('getClientList')->param('addressId', $address->id)->param('tripId', $tripId)
            ->button('❌ Назад')->action('getAddressList')->param('tripId', $tripId);
    }

}
