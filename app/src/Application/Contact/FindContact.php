<?php

namespace App\Application\Contact;

use App\Domain\Contact\Contact;
use App\Domain\Contact\ContactRepositoryInterface;

final class FindContact
{
    public function __construct(private readonly ContactRepositoryInterface $repository) {}

    public function handle(int $id): ?Contact
    {
        return $this->repository->findById($id);
    }
}
