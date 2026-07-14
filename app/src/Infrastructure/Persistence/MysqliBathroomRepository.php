<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Bathroom\Bathroom;
use App\Domain\Bathroom\BathroomRepositoryInterface;
use mysqli;

final class MysqliBathroomRepository implements BathroomRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function codeExists(string $codigo, ?int $excludeId = null): bool
    {
        if ($excludeId === null) {
            $stmt = $this->connection->prepare('SELECT COUNT(*) AS total FROM bathrooms WHERE codigo_Bath = ?');
            $stmt->bind_param('s', $codigo);
        } else {
            $stmt = $this->connection->prepare('SELECT COUNT(*) AS total FROM bathrooms WHERE codigo_Bath = ? AND id_Bath != ?');
            $stmt->bind_param('si', $codigo, $excludeId);
        }
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    public function insert(Bathroom $bathroom): int
    {
        $codigo = $bathroom->codigoBath;
        $fechaCompra = $bathroom->fechaCompraBath;
        $observacion = $bathroom->observacionBath;
        $estado = $bathroom->estadoBath;

        $stmt = $this->connection->prepare(
            'INSERT INTO bathrooms (codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath) VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('sssi', $codigo, $fechaCompra, $observacion, $estado);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(Bathroom $bathroom): void
    {
        $codigo = $bathroom->codigoBath;
        $fechaCompra = $bathroom->fechaCompraBath;
        $observacion = $bathroom->observacionBath;
        $estado = $bathroom->estadoBath;
        $id = $bathroom->id;

        $stmt = $this->connection->prepare(
            'UPDATE bathrooms SET codigo_Bath = ?, fechaCompra_Bath = ?, observacion_Bath = ?, estado_Bath = ? WHERE id_Bath = ?'
        );
        $stmt->bind_param('sssii', $codigo, $fechaCompra, $observacion, $estado, $id);
        $stmt->execute();
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare('DELETE FROM bathrooms WHERE id_Bath = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function setEstado(int $id, int $estado): void
    {
        $stmt = $this->connection->prepare('UPDATE bathrooms SET estado_Bath = ? WHERE id_Bath = ?');
        $stmt->bind_param('ii', $estado, $id);
        $stmt->execute();
    }

    public function setAsignado(int $id, int $asignado): void
    {
        $stmt = $this->connection->prepare('UPDATE bathrooms SET asignado_Bath = ? WHERE id_Bath = ?');
        $stmt->bind_param('ii', $asignado, $id);
        $stmt->execute();
    }

    public function find(int $id): ?Bathroom
    {
        $stmt = $this->connection->prepare(
            'SELECT id_Bath, codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath, asignado_Bath FROM bathrooms WHERE id_Bath = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        return new Bathroom(
            id: (int) $row['id_Bath'],
            codigoBath: $row['codigo_Bath'],
            fechaCompraBath: $row['fechaCompra_Bath'],
            observacionBath: $row['observacion_Bath'],
            estadoBath: (int) $row['estado_Bath'],
            asignadoBath: (int) $row['asignado_Bath'],
        );
    }

    public function listAll(): array
    {
        $result = $this->connection->query(
            'SELECT id_Bath, codigo_Bath, fechaCompra_Bath, observacion_Bath, estado_Bath, asignado_Bath
             FROM bathrooms WHERE estado_Bath IN (0, 1, 2) ORDER BY fechaCompra_Bath DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function listAllWithAssignment(): array
    {
        $result = $this->connection->query(
            'SELECT BT.id_Bath, BT.codigo_Bath, BT.fechaCompra_Bath, BT.observacion_Bath,
                    BT.estado_Bath, BT.asignado_Bath,
                    CT.obra_Contrato, CL.nombre_Cliente
             FROM bathrooms BT
             LEFT JOIN contrato_bathroom CB ON CB.id_Relacion = (
                 SELECT MAX(CB_LATEST.id_Relacion)
                 FROM contrato_bathroom CB_LATEST
                 JOIN contratos CT_LATEST ON CT_LATEST.id_Contrato = CB_LATEST.id_Contrato
                     AND CT_LATEST.estado_Contrato = 2
                 WHERE CB_LATEST.id_Bath = BT.id_Bath
             )
             LEFT JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato
             LEFT JOIN clientes CL ON CL.id_Cliente = CT.id_Cliente
             WHERE BT.estado_Bath IN (0, 1, 2)
             ORDER BY BT.fechaCompra_Bath DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findActiveAssignment(int $idBath): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT CB.id_Relacion, CB.id_Contrato
             FROM contrato_bathroom CB
             JOIN contratos CT ON CT.id_Contrato = CB.id_Contrato AND CT.estado_Contrato = 2
             WHERE CB.id_Bath = ?
             ORDER BY CB.id_Relacion DESC
             LIMIT 1'
        );
        $stmt->bind_param('i', $idBath);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            return null;
        }

        return [
            'id_Relacion' => (int) $row['id_Relacion'],
            'id_Contrato' => (int) $row['id_Contrato'],
        ];
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM bathrooms');

        return (int) $result->fetch_assoc()['total'];
    }

    public function assignToContract(int $idContrato, int $idBath): void
    {
        $this->connection->begin_transaction();

        try {
            $stmtBath = $this->connection->prepare(
                'SELECT estado_Bath, asignado_Bath FROM bathrooms WHERE id_Bath = ? FOR UPDATE'
            );
            $stmtBath->bind_param('i', $idBath);
            $stmtBath->execute();
            $bathroom = $stmtBath->get_result()->fetch_assoc();

            if (!$bathroom || (int) $bathroom['estado_Bath'] !== 1 || (int) $bathroom['asignado_Bath'] !== 0) {
                throw new \DomainException('Bathroom is not available.');
            }

            $stmtContract = $this->connection->prepare(
                'SELECT estado_Contrato FROM contratos WHERE id_Contrato = ? FOR UPDATE'
            );
            $stmtContract->bind_param('i', $idContrato);
            $stmtContract->execute();
            $contract = $stmtContract->get_result()->fetch_assoc();

            if (!$contract || (int) $contract['estado_Contrato'] !== 2) {
                throw new \DomainException('Contract is not active.');
            }

            if ($this->findActiveAssignment($idBath) !== null) {
                throw new \DomainException('Bathroom already has an active assignment.');
            }

            $stmtInsert = $this->connection->prepare(
                'INSERT INTO contrato_bathroom (id_Contrato, id_Bath) VALUES (?, ?)'
            );
            $stmtInsert->bind_param('ii', $idContrato, $idBath);
            $stmtInsert->execute();

            $this->setAsignado($idBath, 1);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function unassignFromContract(int $idRelacion, int $idBath): void
    {
        $this->connection->begin_transaction();

        try {
            $stmtBath = $this->connection->prepare(
                'SELECT id_Bath FROM bathrooms WHERE id_Bath = ? FOR UPDATE'
            );
            $stmtBath->bind_param('i', $idBath);
            $stmtBath->execute();

            if (!$stmtBath->get_result()->fetch_assoc()) {
                throw new \DomainException('Bathroom does not exist.');
            }

            $stmtDelete = $this->connection->prepare(
                'DELETE FROM contrato_bathroom WHERE id_Relacion = ? AND id_Bath = ?'
            );
            $stmtDelete->bind_param('ii', $idRelacion, $idBath);
            $stmtDelete->execute();

            if ($stmtDelete->affected_rows !== 1) {
                throw new \DomainException('Assignment does not exist.');
            }

            $asignado = $this->findActiveAssignment($idBath) === null ? 0 : 1;
            $this->setAsignado($idBath, $asignado);
            $this->connection->commit();
        } catch (\Throwable $exception) {
            $this->connection->rollback();
            throw $exception;
        }
    }

    public function countAssignedToContract(int $idContrato): int
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) AS total FROM contrato_bathroom WHERE id_Contrato = ?');
        $stmt->bind_param('i', $idContrato);
        $stmt->execute();

        return (int) $stmt->get_result()->fetch_assoc()['total'];
    }

    public function closeContract(int $idContrato): void
    {
        $stmt = $this->connection->prepare('UPDATE contratos SET estado_Contrato = 1 WHERE id_Contrato = ?');
        $stmt->bind_param('i', $idContrato);
        $stmt->execute();
    }

    public function listByContract(int $idContrato): array
    {
        $stmt = $this->connection->prepare(
            'SELECT CB.id_Relacion, CB.id_Contrato, CB.id_Bath, BT.codigo_Bath, BT.fechaCompra_Bath, BT.asignado_Bath
             FROM contrato_bathroom CB
             JOIN bathrooms BT ON CB.id_Bath = BT.id_Bath
             WHERE CB.id_Contrato = ?'
        );
        $stmt->bind_param('i', $idContrato);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function unassignAllFromContract(int $idContrato): void
    {
        $stmt = $this->connection->prepare('SELECT id_Bath FROM contrato_bathroom WHERE id_Contrato = ?');
        $stmt->bind_param('i', $idContrato);
        $stmt->execute();
        $bathIds = array_column($stmt->get_result()->fetch_all(MYSQLI_ASSOC), 'id_Bath');

        foreach ($bathIds as $idBath) {
            $this->setAsignado((int) $idBath, 0);

            $stmtDelete = $this->connection->prepare('DELETE FROM contrato_bathroom WHERE id_Contrato = ? AND id_Bath = ?');
            $stmtDelete->bind_param('ii', $idContrato, $idBath);
            $stmtDelete->execute();
        }
    }
}
