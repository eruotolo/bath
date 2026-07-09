<?php

namespace App\Application\Certificate;

use App\Domain\Certificate\Certificate;
use App\Domain\Certificate\CertificateRepositoryInterface;

final class FindCertificateForPrint
{
    public function __construct(private readonly CertificateRepositoryInterface $repository) {}

    /**
     * @return array{certificado:string,nro_Certificado:string,fechahoy_Certificado:string,fecha_Servicio:string,mts_Certificado:int,nombre_Cliente:string,rut_Cliente:string,obra_Contrato:string}|null
     */
    public function handle(int $id, int $idContrato): ?array
    {
        $row = $this->repository->findForPrint($id, $idContrato);

        if ($row === null) {
            return null;
        }

        $row['certificado'] = Certificate::displayNumber($row['fechahoy_Certificado'], $row['nro_Certificado']);

        return $row;
    }
}
