<?php

namespace App\Domain\Service;

interface ServiceRepositoryInterface
{
    public function insert(Service $service): int;

    public function update(Service $service): void;

    public function setInactive(int $id): void;

    public function find(int $id): ?Service;

    /**
     * Servicio + contrato + cliente (join), para dash-services-edit.php / dash-services-bath.php / dash-services-print.php.
     * @return array<string, mixed>|null
     */
    public function findWithContractAndCustomer(int $id): ?array;

    /**
     * Servicios activos con obra/cliente/estado de facturación, para dash-services.php.
     * @return array<int, array<string, mixed>>
     */
    public function listActiveWithDetails(): array;

    /**
     * @param int[] $bathIds
     */
    public function assignBathrooms(int $idServicio, array $bathIds): void;

    public function removeAssignedBathroom(int $idRelacion): void;

    /**
     * Baños asignados a un servicio, para dash-services-bath.php / dash-services-print.php.
     * @return array<int, array<string, mixed>>
     */
    public function listAssignedBathrooms(int $idServicio): array;
}
