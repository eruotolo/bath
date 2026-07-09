<?php

namespace App\Application\Certificate;

use App\Domain\Certificate\Certificate;
use App\Domain\Certificate\CertificateRepositoryInterface;

final class ListCertificates
{
    public function __construct(private readonly CertificateRepositoryInterface $repository) {}

    /**
     * @return array{total:int, items:array<int, array<string, mixed>>}
     */
    public function handle(): array
    {
        $items = $this->repository->listWithDetails();

        foreach ($items as &$row) {
            $row['certificado'] = Certificate::displayNumber($row['fechahoy_Certificado'], $row['nro_Certificado']);
        }
        unset($row);

        return [
            'total' => $this->repository->count(),
            'items' => $items,
        ];
    }
}
