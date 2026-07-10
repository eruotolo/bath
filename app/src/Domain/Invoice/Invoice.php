<?php

namespace App\Domain\Invoice;

final class Invoice
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $customerId,
        public readonly int $contractId,
        public readonly string $number,
        public readonly string $date,
        public readonly int $value,
        public readonly int $state,
        public readonly ?string $paymentDate = null,
    ) {}
}
