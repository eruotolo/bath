<?php

namespace App\Domain\Bathroom;

final class Bathroom
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $codigoBath,
        public readonly string $fechaCompraBath,
        public readonly string $observacionBath,
        public readonly int $estadoBath,
        public readonly int $asignadoBath = 0,
    ) {}
}
