<?php

namespace App\DTO;

class GetAddressListDTO
{
    public function __construct(
        public bool  $success,
        /** @var GetAddressDTO[] */
        public array $addresses,
    ) {}
}
