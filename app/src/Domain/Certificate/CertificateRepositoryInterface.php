<?php

namespace App\Domain\Certificate;

interface CertificateRepositoryInterface
{
    public function nextCorrelativeForToday(): int;

    public function insert(Certificate $certificate): int;

    public function delete(int $id): void;

    /**
     * Certificado + datos de cliente/contrato para impresión (PDF y detalle).
     * @return array{nro_Certificado:string,fechahoy_Certificado:string,fecha_Servicio:string,mts_Certificado:int,nombre_Cliente:string,rut_Cliente:string,obra_Contrato:string}|null
     */
    public function findForPrint(int $id, int $idContrato): ?array;

    /**
     * Listado con datos de cliente/contrato para dash-certificates.php.
     * @return array<int, array<string, mixed>>
     */
    public function listWithDetails(): array;

    public function count(): int;
}
