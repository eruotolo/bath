<?php

namespace App\Application\Certificate;

use App\Domain\Certificate\Certificate;
use App\Domain\Certificate\CertificateRepositoryInterface;

final class CreateCertificate
{
    public function __construct(private readonly CertificateRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $correlativo = $this->repository->nextCorrelativeForToday();
        $nroCertificado = sprintf('%05d', $correlativo);

        $certificate = new Certificate(
            id: null,
            nroCertificado: $nroCertificado,
            idCliente: (int) $input['id_Cliente'],
            idContrato: (int) $input['id_Contrato'],
            fechaServicio: $input['fecha_Servicio'],
            mts: (int) $input['mts_Certificado'],
        );

        return $this->repository->insert($certificate);
    }
}
