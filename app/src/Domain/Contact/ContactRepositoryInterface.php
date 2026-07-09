<?php

namespace App\Domain\Contact;

interface ContactRepositoryInterface
{
    public function findById(int $id): ?Contact;

    public function insert(Contact $contact): int;

    public function update(Contact $contact): void;

    public function delete(int $id): void;

    /**
     * @return Contact[]
     */
    public function listActiveByCustomer(int $customerId): array;
}
