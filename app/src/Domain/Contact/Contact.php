<?php

namespace App\Domain\Contact;

final class Contact
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $customerId,
        public readonly string $name,
        public readonly string $lastname,
        public readonly string $rut,
        public readonly string $phone,
        public readonly string $address,
        public readonly ?string $observation,
        public readonly int $state = 1,
    ) {}
}
