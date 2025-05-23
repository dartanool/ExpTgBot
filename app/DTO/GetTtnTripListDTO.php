<?php

namespace App\DTO;

class GetTtnTripListDTO
{
    public function __construct(
        public bool $success,
    /** @var GetTtnTripDTO[] */
    public array $trips)
    {}

}
