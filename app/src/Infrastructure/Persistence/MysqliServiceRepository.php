<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Service\Service;
use App\Domain\Service\ServiceRepositoryInterface;
use mysqli;

final class MysqliServiceRepository implements ServiceRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function insert(Service $service): int
    {
        $contractId = $service->contractId;
        $nro = $service->nro;
        $date = $service->date;
        $observations = $service->observations;
        $state = $service->state;

        $stmt = $this->connection->prepare(
            'INSERT INTO servicios (id_Contrato, nro_Servicio, fecha_Servicio, observaciones_Servicio, estado_Servicio)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iissi', $contractId, $nro, $date, $observations, $state);
        $stmt->execute();
        $id = $stmt->insert_id;

        $this->insertTipoServicio($service);

        return $id;
    }

    public function update(Service $service): void
    {
        $id = $service->id;
        $contractId = $service->contractId;
        $nro = $service->nro;
        $date = $service->date;
        $observations = $service->observations;
        $state = $service->state;

        $stmt = $this->connection->prepare(
            'UPDATE servicios SET id_Contrato = ?, nro_Servicio = ?, fecha_Servicio = ?, observaciones_Servicio = ?, estado_Servicio = ?
             WHERE id_Servicio = ?'
        );
        $stmt->bind_param('iissii', $contractId, $nro, $date, $observations, $state, $id);
        $stmt->execute();

        $this->updateTipoServicio($service);
    }

    public function setInactive(int $id): void
    {
        $stmt = $this->connection->prepare('UPDATE servicios SET estado_Servicio = 0 WHERE id_Servicio = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function find(int $id): ?Service
    {
        $stmt = $this->connection->prepare(
            'SELECT SR.*, TS.*
             FROM servicios SR
             JOIN tipo_servicio TS ON SR.nro_Servicio = TS.nro_Servicio
             WHERE SR.id_Servicio = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function findWithContractAndCustomer(int $id): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT SR.*, CT.obra_Contrato, CT.direccion_Contrato, CT.fechaInicio_Contrato, CT.fechaFin_Contrato,
                    CL.nombre_Cliente, CL.direccion_Cliente, CL.email_Cliente, CL.telefono_Cliente, CL.ciudad_Cliente, CL.region_Cliente,
                    TS.*
             FROM servicios SR
             JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             JOIN tipo_servicio TS ON SR.nro_Servicio = TS.nro_Servicio
             WHERE SR.id_Servicio = ?'
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function listActiveWithDetails(): array
    {
        $result = $this->connection->query(
            'SELECT SR.*, CT.obra_Contrato, CL.nombre_Cliente,
                    EXISTS(SELECT 1 FROM factura_servicio FS WHERE FS.id_Servicio = SR.id_Servicio) AS facturado
             FROM servicios SR
             JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             WHERE SR.estado_Servicio = 1
             ORDER BY SR.created_at DESC, SR.id_Servicio DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function assignBathrooms(int $idServicio, array $bathIds): void
    {
        $stmt = $this->connection->prepare('INSERT INTO servicios_bathrooms (id_Servicio, id_Bath) VALUES (?, ?)');
        foreach ($bathIds as $idBath) {
            $idBath = (int) $idBath;
            $stmt->bind_param('ii', $idServicio, $idBath);
            $stmt->execute();
        }
    }

    public function removeAssignedBathroom(int $idRelacion): void
    {
        $stmt = $this->connection->prepare('DELETE FROM servicios_bathrooms WHERE id_Relacion = ?');
        $stmt->bind_param('i', $idRelacion);
        $stmt->execute();
    }

    public function listAssignedBathrooms(int $idServicio): array
    {
        $stmt = $this->connection->prepare(
            'SELECT SB.id_Relacion, SB.id_Servicio, BT.id_Bath, BT.codigo_Bath, BT.fechaCompra_Bath
             FROM servicios_bathrooms SB
             JOIN bathrooms BT ON SB.id_Bath = BT.id_Bath
             WHERE SB.id_Servicio = ?'
        );
        $stmt->bind_param('i', $idServicio);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function insertTipoServicio(Service $service): void
    {
        $nro = $service->nro;
        $installation = (int) $service->installation;
        $repair = (int) $service->repair;
        $cleaning = (int) $service->cleaning;
        $disinfection = (int) $service->disinfection;
        $sanitization = (int) $service->sanitization;
        $toiletPaper = (int) $service->toiletPaper;
        $soap = (int) $service->soap;
        $others = (int) $service->others;
        $removal = (int) $service->removal;

        $stmt = $this->connection->prepare(
            'INSERT INTO tipo_servicio (nro_Servicio, instalacion_Tipo, reparacion_Tipo, limpieza_Tipo, desinfeccion_Tipo,
             sanitizacion_Tipo, higienico_Tipo, jabon_Tipo, otros_Tipo, retiro_Tipo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'iiiiiiiiii',
            $nro, $installation, $repair, $cleaning, $disinfection, $sanitization, $toiletPaper, $soap, $others, $removal
        );
        $stmt->execute();
    }

    private function updateTipoServicio(Service $service): void
    {
        $nro = $service->nro;
        $installation = (int) $service->installation;
        $repair = (int) $service->repair;
        $cleaning = (int) $service->cleaning;
        $disinfection = (int) $service->disinfection;
        $sanitization = (int) $service->sanitization;
        $toiletPaper = (int) $service->toiletPaper;
        $soap = (int) $service->soap;
        $others = (int) $service->others;
        $removal = (int) $service->removal;

        $stmt = $this->connection->prepare(
            'UPDATE tipo_servicio SET instalacion_Tipo = ?, reparacion_Tipo = ?, limpieza_Tipo = ?, desinfeccion_Tipo = ?,
             sanitizacion_Tipo = ?, higienico_Tipo = ?, jabon_Tipo = ?, otros_Tipo = ?, retiro_Tipo = ?
             WHERE nro_Servicio = ?'
        );
        $stmt->bind_param(
            'iiiiiiiiii',
            $installation, $repair, $cleaning, $disinfection, $sanitization, $toiletPaper, $soap, $others, $removal, $nro
        );
        $stmt->execute();
    }

    private function hydrate(array $row): Service
    {
        return new Service(
            id: (int) $row['id_Servicio'],
            contractId: (int) $row['id_Contrato'],
            nro: (int) $row['nro_Servicio'],
            date: $row['fecha_Servicio'],
            observations: $row['observaciones_Servicio'],
            state: (int) $row['estado_Servicio'],
            installation: (bool) $row['instalacion_Tipo'],
            repair: (bool) $row['reparacion_Tipo'],
            cleaning: (bool) $row['limpieza_Tipo'],
            disinfection: (bool) $row['desinfeccion_Tipo'],
            sanitization: (bool) $row['sanitizacion_Tipo'],
            toiletPaper: (bool) $row['higienico_Tipo'],
            soap: (bool) $row['jabon_Tipo'],
            others: (bool) $row['otros_Tipo'],
            removal: (bool) $row['retiro_Tipo'],
        );
    }
}
