<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Certificate\Certificate;
use App\Domain\Certificate\CertificateRepositoryInterface;
use mysqli;

final class MysqliCertificateRepository implements CertificateRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function nextCorrelativeForToday(): int
    {
        $stmt = $this->connection->prepare(
            'SELECT MAX(nro_Certificado) AS ultimo_correlativo FROM certificados WHERE fechahoy_Certificado = CURDATE()'
        );
        $stmt->execute();
        $ultimo = $stmt->get_result()->fetch_assoc()['ultimo_correlativo'];

        return $ultimo === null ? 1 : 1 + (int) $ultimo;
    }

    public function insert(Certificate $certificate): int
    {
        // bind_param necesita variables por referencia -> no puede apuntar directo
        // a propiedades readonly de la Entity, hace falta copiarlas antes.
        $nroCertificado = $certificate->nroCertificado;
        $idCliente = $certificate->idCliente;
        $idContrato = $certificate->idContrato;
        $fechaServicio = $certificate->fechaServicio;
        $mts = $certificate->mts;

        $stmt = $this->connection->prepare(
            'INSERT INTO certificados (nro_Certificado, id_Cliente, id_Contrato, fechahoy_Certificado, fecha_Servicio, mts_Certificado)
             VALUES (?, ?, ?, CURDATE(), ?, ?)'
        );
        $stmt->bind_param('siisi', $nroCertificado, $idCliente, $idContrato, $fechaServicio, $mts);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function delete(int $id): void
    {
        $stmt = $this->connection->prepare('DELETE FROM certificados WHERE id_Certificado = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public function findForPrint(int $id, int $idContrato): ?array
    {
        $stmt = $this->connection->prepare(
            'SELECT CR.nro_Certificado, CR.fechahoy_Certificado, CR.fecha_Servicio, CR.mts_Certificado,
                    CL.nombre_Cliente, CL.rut_Cliente, CT.obra_Contrato
             FROM certificados CR
             JOIN clientes CL ON CR.id_Cliente = CL.id_Cliente
             JOIN contratos CT ON CR.id_Contrato = CT.id_Contrato
             WHERE CR.id_Certificado = ? AND CR.id_Contrato = ?'
        );
        $stmt->bind_param('ii', $id, $idContrato);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function listWithDetails(): array
    {
        $result = $this->connection->query(
            'SELECT CR.id_Certificado, CR.id_Contrato, CR.nro_Certificado, CR.fechahoy_Certificado, CR.fecha_Servicio,
                    CL.nombre_Cliente, CL.rut_Cliente, CT.obra_Contrato
             FROM certificados CR
             JOIN clientes CL ON CR.id_Cliente = CL.id_Cliente
             JOIN contratos CT ON CR.id_Contrato = CT.id_Contrato
             ORDER BY CR.created_at DESC, CR.id_Certificado DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function count(): int
    {
        $result = $this->connection->query('SELECT COUNT(*) AS total FROM certificados');

        return (int) $result->fetch_assoc()['total'];
    }
}
