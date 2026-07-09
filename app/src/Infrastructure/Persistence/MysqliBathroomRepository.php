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
             FROM bathrooms WHERE estado_Bath IN (0, 1) ORDER BY fechaCompra_Bath DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM bathrooms');

        return (int) $result->fetch_assoc()['total'];
    }

    public function assignToContract(int $idContrato, int $idBath): void
    {
        $stmtInsert = $this->connection->prepare('INSERT INTO contrato_bathroom (id_Contrato, id_Bath) VALUES (?, ?)');
        $stmtInsert->bind_param('ii', $idContrato, $idBath);
        $stmtInsert->execute();

        $this->setAsignado($idBath, 1);
    }

    public function unassignFromContract(int $idRelacion, int $idBath): void
    {
        $stmtDelete = $this->connection->prepare('DELETE FROM contrato_bathroom WHERE id_Relacion = ?');
        $stmtDelete->bind_param('i', $idRelacion);
        $stmtDelete->execute();

        $this->setAsignado($idBath, 0);
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
}
