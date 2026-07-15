<?php

namespace App\Domain\Contract;

interface ContractRepositoryInterface
{
    public function insert(Contract $contract): int;

    public function update(Contract $contract): void;

    public function setState(int $id, int $state): void;

    public function find(int $id): ?Contract;

    /**
     * Contrato + nombre del cliente (join), para dash-contracts-edit.php / dash-contracts-item.php.
     * @return array<string, mixed>|null
     */
    public function findWithCustomerName(int $id): ?array;

    public function count(): int;

    /**
     * Cuenta contratos agrupados por estado en una sola consulta, para los botones de
     * filtro de dash-contracts.php.
     * @return array{todos: int, activos: int, terminados: int}
     */
    public function countStateTotals(): array;

    /**
     * Listado con nombre del cliente (join), para dash-contracts.php.
     * $state === null trae los estados 1 y 2 (activos + terminados), igual que el listado original.
     * @return array<int, array<string, mixed>>
     */
    public function listWithCustomerName(?int $state, string $sortBy = 'created_at', string $sortDir = 'DESC'): array;

    /**
     * @return Contract[] Contracts whose fechaFin_Contrato falls within the next $days days.
     */
    public function findExpiringSoon(int $days = 7): array;
}
