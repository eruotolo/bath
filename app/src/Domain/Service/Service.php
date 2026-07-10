<?php

namespace App\Domain\Service;

final class Service
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $contractId,
        public readonly int $nro,
        public readonly string $date,
        public readonly ?string $observations,
        public readonly int $state,
        public readonly bool $installation,
        public readonly bool $repair,
        public readonly bool $cleaning,
        public readonly bool $disinfection,
        public readonly bool $sanitization,
        public readonly bool $toiletPaper,
        public readonly bool $soap,
        public readonly bool $others,
        public readonly bool $removal,
    ) {}
}
