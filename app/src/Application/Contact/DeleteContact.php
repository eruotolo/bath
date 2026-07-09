<?php

namespace App\Application\Contact;

use App\Domain\Contact\ContactRepositoryInterface;

final class DeleteContact
{
    public function __construct(private readonly ContactRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $this->repository->delete($id);
    }
}
