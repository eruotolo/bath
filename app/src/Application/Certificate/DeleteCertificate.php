<?php

namespace App\Application\Certificate;

use App\Domain\Certificate\CertificateRepositoryInterface;

final class DeleteCertificate
{
    public function __construct(private readonly CertificateRepositoryInterface $repository) {}

    public function handle(int $id): void
    {
        $this->repository->delete($id);
    }
}
