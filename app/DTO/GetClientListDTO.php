<?php

namespace App\DTO;

class GetClientListDTO
{
    public function __construct(
        public bool  $success,
        /** @var GetAddressDTO[] */
        public array $clients,
    ) {}
}
