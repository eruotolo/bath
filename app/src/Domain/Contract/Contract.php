<?php

namespace App\Domain\Contract;

final class Contract
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $customerId,
        public readonly string $obra,
        public readonly string $address,
        public readonly int $state,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly int $monthlyValue,
        public readonly int $totalValue,
        public readonly ?string $observation,
    ) {}
}
