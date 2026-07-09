<?php

namespace App\Domain\Customer;

final class Customer
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $rut,
        public readonly string $name,
        public readonly string $phone,
        public readonly string $email,
        public readonly string $address,
        public readonly string $region,
        public readonly string $city,
        public readonly string $commune,
        public readonly bool $active = true,
    ) {}
}
