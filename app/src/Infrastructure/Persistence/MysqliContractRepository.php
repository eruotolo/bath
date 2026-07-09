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

    public function listWithCustomerName(?int $state): array
    {
        if ($state !== null) {
            $stmt = $this->connection->prepare(
                'SELECT CT.*, CL.nombre_Cliente FROM contratos CT
                 JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
                 WHERE CT.estado_Contrato = ?
                 ORDER BY CT.created_at DESC, CT.id_Contrato DESC'
            );
            $stmt->bind_param('i', $state);
            $stmt->execute();

            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $result = $this->connection->query(
            'SELECT CT.*, CL.nombre_Cliente FROM contratos CT
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             WHERE CT.estado_Contrato IN (1, 2)
             ORDER BY CT.created_at DESC, CT.id_Contrato DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
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
