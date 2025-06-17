<?php

namespace App\DTO;

class GetAddressDTO
{
    public function __construct(
        public string $id,
        public readonly string $address,
        public readonly float $lat,
        public readonly float $lon,
        public readonly string | null $workHours,
        public readonly string $clientName,
    ) {}
}
