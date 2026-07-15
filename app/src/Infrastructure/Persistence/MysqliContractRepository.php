<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contract\Contract;
use App\Domain\Contract\ContractRepositoryInterface;
use mysqli;

final class MysqliContractRepository implements ContractRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function insert(Contract $contract): int
    {
        $customerId = $contract->customerId;
        $obra = $contract->obra;
        $address = $contract->address;
        $state = $contract->state;
        $startDate = $contract->startDate;
        $endDate = $contract->endDate;
        $monthlyValue = $contract->monthlyValue;
        $totalValue = $contract->totalValue;
        $observation = $contract->observation;

        $stmt = $this->connection->prepare(
            'INSERT INTO contratos (id_Cliente, obra_Contrato, direccion_Contrato, estado_Contrato,
             fechaInicio_Contrato, fechaFin_Contrato, valorMensual_Contrato, valorTotal_Contrato, observacion_Contrato)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ississiis',
            $customerId, $obra, $address, $state, $startDate, $endDate, $monthlyValue, $totalValue, $observation
        );
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(Contract $contract): void
    {
        $id = $contract->id;
        $customerId = $contract->customerId;
        $obra = $contract->obra;
        $address = $contract->address;
        $state = $contract->state;
        $startDate = $contract->startDate;
        $endDate = $contract->endDate;
        $monthlyValue = $contract->monthlyValue;
        $totalValue = $contract->totalValue;
        $observation = $contract->observation;

        $stmt = $this->connection->prepare(
            'UPDATE contratos SET id_Cliente = ?, obra_Contrato = ?, direccion_Contrato = ?, estado_Contrato = ?,
             fechaInicio_Contrato = ?, fechaFin_Contrato = ?, valorMensual_Contrato = ?, valorTotal_Contrato = ?,
             observacion_Contrato = ? WHERE id_Contrato = ?'
        );
        $stmt->bind_param(
            'ississiisi',
            $customerId, $obra, $address, $state, $startDate, $endDate, $monthlyValue, $totalValue, $observation, $id
        );
        $stmt->execute();
    }

    public function setState(int $id, int $state): void
    {
        $stmt = $this->connection->prepare('UPDATE contratos SET estado_Contrato = ? WHERE id_Contrato = ?');
        $stmt->bind_param('ii', $state, $id);
        $stmt->execute();
    }

    public function find(int $id): ?Contract
    {
        $stmt = $this->connection->prepare('SELECT * FROM contratos WHERE id_Contrato = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function findWithCustomerName(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT CT.*, CL.nombre_Cliente FROM contratos CT
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             WHERE CT.id_Contrato = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM contratos');

        return (int) $result->fetch_assoc()['total'];
    }

    public function countStateTotals(): array
    {
        $result = $this->connection->query(
            'SELECT
                SUM(CASE WHEN estado_Contrato IN (1, 2) THEN 1 ELSE 0 END) AS todos,
                SUM(CASE WHEN estado_Contrato = 2 THEN 1 ELSE 0 END) AS activos,
                SUM(CASE WHEN estado_Contrato = 1 THEN 1 ELSE 0 END) AS terminados
             FROM contratos'
        );
        $row = $result->fetch_assoc();

        return [
            'todos' => (int) $row['todos'],
            'activos' => (int) $row['activos'],
            'terminados' => (int) $row['terminados'],
        ];
    }

    public function listWithCustomerName(?int $state, string $sortBy = 'created_at', string $sortDir = 'DESC'): array
    {
        $sortByMap = [
            'created_at' => 'CT.created_at',
            'cliente' => 'CL.nombre_Cliente',
            'obra' => 'CT.obra_Contrato',
            'estado' => 'CT.estado_Contrato',
        ];
        $allowedSortDir = ['ASC', 'DESC'];

        $column = $sortByMap[$sortBy] ?? $sortByMap['created_at'];
        $direction = in_array($sortDir, $allowedSortDir, true) ? $sortDir : 'DESC';
        $orderBy = $column . ' ' . $direction . ', CT.id_Contrato DESC';

        if ($state !== null) {
            $stmt = $this->connection->prepare(
                'SELECT CT.*, CL.nombre_Cliente FROM contratos CT
                 JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
                 WHERE CT.estado_Contrato = ?
                 ORDER BY ' . $orderBy
            );
            $stmt->bind_param('i', $state);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $result = $this->connection->query(
            'SELECT CT.*, CL.nombre_Cliente FROM contratos CT
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             WHERE CT.estado_Contrato IN (1, 2)
             ORDER BY ' . $orderBy
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findExpiringSoon(int $days = 7): array
    {
        $today = date('Y-m-d');
        $threshold = date('Y-m-d', strtotime("+{$days} days"));

        $stmt = $this->connection->prepare(
            'SELECT * FROM contratos WHERE fechaFin_Contrato BETWEEN ? AND ? ORDER BY fechaFin_Contrato ASC'
        );
        $stmt->bind_param('ss', $today, $threshold);
        $stmt->execute();

        return array_map(
            fn(array $row) => $this->hydrate($row),
            $stmt->get_result()->fetch_all(MYSQLI_ASSOC)
        );
    }

    private function hydrate(array $row): Contract
    {
        return new Contract(
            id: (int) $row['id_Contrato'],
            customerId: (int) $row['id_Cliente'],
            obra: $row['obra_Contrato'],
            address: $row['direccion_Contrato'],
            state: (int) $row['estado_Contrato'],
            startDate: $row['fechaInicio_Contrato'],
            endDate: $row['fechaFin_Contrato'],
            monthlyValue: (int) $row['valorMensual_Contrato'],
            totalValue: (int) $row['valorTotal_Contrato'],
            observation: $row['observacion_Contrato'],
        );
    }
}
