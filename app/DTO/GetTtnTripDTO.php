<?php

namespace App\DTO;

class GetTtnTripDTO
{
    public function __construct(
        public int $id,
        public string $idAexTtnTrip,
        public string $aexTtnTripIdRec,
        public string $aexoTel,
        public string $idS72,
        public float $prchVes,
        public float $prchObyom,
        public int $prchCliMest,
        public int $prchBagMest,
        public string $prchStrNom)
    {}
}
