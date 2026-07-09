<?php

namespace App\Domain\Bathroom;

interface BathroomRepositoryInterface
{
    public function codeExists(string $codigo, ?int $excludeId = null): bool;

    public function insert(Bathroom $bathroom): int;

    public function update(Bathroom $bathroom): void;

    public function delete(int $id): void;

    public function setEstado(int $id, int $estado): void;

    public function setAsignado(int $id, int $asignado): void;

    public function find(int $id): ?Bathroom;

    /**
     * Listado plano para dash-bathrooms.php.
     * @return array<int, array<string, mixed>>
     */
    public function listAll(): array;

    public function count(): int;

    public function assignToContract(int $idContrato, int $idBath): void;

    public function unassignFromContract(int $idRelacion, int $idBath): void;

    public function countAssignedToContract(int $idContrato): int;

    /**
     * Baños relacionados a un contrato (join contrato_bathroom + bathrooms), para dash-contracts-item.php.
     * @return array<int, array<string, mixed>>
     */
    public function listByContract(int $idContrato): array;

    /**
     * Desasigna todos los baños de un contrato (usado al inactivar un contrato):
     * pone asignado_Bath = 0 en cada uno y borra la relación en contrato_bathroom.
     */
    public function unassignAllFromContract(int $idContrato): void;

    /**
     * Cierra el contrato (estado_Contrato = 1) — cruza al dominio Contract, todavía no migrado.
     * Excepción documentada, mismo criterio que MysqliCertificateRepository con clientes/contratos.
     */
    public function closeContract(int $idContrato): void;
}
