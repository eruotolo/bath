<?php

namespace App\Application\Contact;

use App\Domain\Contact\ContactRepositoryInterface;

final class ListContactsByCustomer
{
    public function __construct(private readonly ContactRepositoryInterface $repository) {}

    /**
     * @return \App\Domain\Contact\Contact[]
     */
    public function handle(int $customerId): array
    {
        return $this->repository->listActiveByCustomer($customerId);
    }
}
