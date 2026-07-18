<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceRepositoryInterface;
use mysqli;

final class MysqliInvoiceRepository implements InvoiceRepositoryInterface
{
    public function __construct(private readonly mysqli $connection) {}

    public function insert(Invoice $invoice): int
    {
        $customerId = $invoice->customerId;
        $contractId = $invoice->contractId;
        $number = $invoice->number;
        $date = $invoice->date;
        $value = $invoice->value;
        $state = $invoice->state;

        $stmt = $this->connection->prepare(
            'INSERT INTO facturas (id_Cliente, id_Contrato, numero_Factura, fecha_Factura, valor_Factura, estado_Factura)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param('iissii', $customerId, $contractId, $number, $date, $value, $state);
        $stmt->execute();

        return $stmt->insert_id;
    }

    public function update(Invoice $invoice): void
    {
        $id = $invoice->id;
        $customerId = $invoice->customerId;
        $contractId = $invoice->contractId;
        $number = $invoice->number;
        $date = $invoice->date;
        $value = $invoice->value;

        $stmt = $this->connection->prepare(
            'UPDATE facturas SET id_Cliente = ?, id_Contrato = ?, numero_Factura = ?, fecha_Factura = ?, valor_Factura = ?
             WHERE id_Factura = ?'
        );
        $stmt->bind_param('iissii', $customerId, $contractId, $number, $date, $value, $id);
        $stmt->execute();
    }

    public function setState(int $id, int $state): void
    {
        $stmt = $this->connection->prepare('UPDATE facturas SET estado_Factura = ? WHERE id_Factura = ?');
        $stmt->bind_param('ii', $state, $id);
        $stmt->execute();
    }

    public function setPaymentDate(int $id, ?string $paymentDate): void
    {
        if ($paymentDate === null) {
            $stmt = $this->connection->prepare('UPDATE facturas SET fecha_Pago = NULL WHERE id_Factura = ?');
            $stmt->bind_param('i', $id);
        } else {
            $stmt = $this->connection->prepare('UPDATE facturas SET fecha_Pago = ? WHERE id_Factura = ?');
            $stmt->bind_param('si', $paymentDate, $id);
        }
        $stmt->execute();
    }

    public function find(int $id): ?Invoice
    {
        $stmt = $this->connection->prepare('SELECT * FROM facturas WHERE id_Factura = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ? $this->hydrate($row) : null;
    }

    public function findWithCustomerAndContract(int $id, int $contractId): ?array
    {
        // Join directo FT.id_Contrato = CT.id_Contrato (el código original unia CL.id_Cliente = CT.id_Cliente,
        // indirecto, funcionaba solo porque el WHERE filtraba despues -- mismo hallazgo que en Certificates).
        $stmt = $this->connection->prepare(
            'SELECT FT.*, CL.nombre_Cliente, CL.rut_Cliente, CL.direccion_Cliente, CL.email_Cliente, CL.telefono_Cliente,
                    CT.obra_Contrato, CT.direccion_Contrato
             FROM facturas FT
             JOIN clientes CL ON FT.id_Cliente = CL.id_Cliente
             JOIN contratos CT ON FT.id_Contrato = CT.id_Contrato
             WHERE FT.id_Factura = ? AND CT.id_Contrato = ?'
        );
        $stmt->bind_param('ii', $id, $contractId);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        return $row ?: null;
    }

    public function existsByNumber(string $number): bool
    {
        $stmt = $this->connection->prepare('SELECT COUNT(*) AS total FROM facturas WHERE numero_Factura = ?');
        $stmt->bind_param('s', $number);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc()['total'] > 0;
    }

    public function listActiveWithDetails(): array
    {
        $result = $this->connection->query(
            'SELECT FT.*, CL.nombre_Cliente, CT.obra_Contrato
             FROM facturas FT
             JOIN clientes CL ON FT.id_Cliente = CL.id_Cliente
             LEFT JOIN contratos CT ON FT.id_Contrato = CT.id_Contrato
             WHERE FT.estado_Factura IN (1, 2)
             ORDER BY FT.created_at DESC, FT.id_Factura DESC'
        );

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function releaseServices(int $idFactura): void
    {
        $stmt = $this->connection->prepare('DELETE FROM factura_servicio WHERE id_Factura = ?');
        $stmt->bind_param('i', $idFactura);
        $stmt->execute();
    }

    public function assignService(int $idFactura, int $idServicio): void
    {
        $stmt = $this->connection->prepare('INSERT INTO factura_servicio (id_Factura, id_Servicio) VALUES (?, ?)');
        $stmt->bind_param('ii', $idFactura, $idServicio);
        $stmt->execute();
    }

    public function removeAssignedService(int $idRelacion): void
    {
        $stmt = $this->connection->prepare('DELETE FROM factura_servicio WHERE id_Relacion = ?');
        $stmt->bind_param('i', $idRelacion);
        $stmt->execute();
    }

    public function listAssignedServices(int $idFactura): array
    {
        $stmt = $this->connection->prepare(
            'SELECT FS.id_Relacion, FS.id_Factura, SR.id_Servicio, SR.nro_Servicio, SR.fecha_Servicio, SR.observaciones_Servicio, SR.valor_Servicio
             FROM factura_servicio FS
             JOIN servicios SR ON FS.id_Servicio = SR.id_Servicio
             WHERE FS.id_Factura = ?'
        );
        $stmt->bind_param('i', $idFactura);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function listUnbilledServicesForContract(int $customerId, int $contractId): array
    {
        $stmt = $this->connection->prepare(
            'SELECT SR.*
             FROM servicios SR
             JOIN contratos CT ON SR.id_Contrato = CT.id_Contrato
             JOIN clientes CL ON CT.id_Cliente = CL.id_Cliente
             WHERE CL.id_Cliente = ? AND CT.id_Contrato = ?
               AND NOT EXISTS (SELECT 1 FROM factura_servicio FS WHERE FS.id_Servicio = SR.id_Servicio)'
        );
        $stmt->bind_param('ii', $customerId, $contractId);
        $stmt->execute();

        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    private function hydrate(array $row): Invoice
    {
        return new Invoice(
            id: (int) $row['id_Factura'],
            customerId: (int) $row['id_Cliente'],
            contractId: (int) $row['id_Contrato'],
            number: $row['numero_Factura'],
            date: $row['fecha_Factura'],
            value: (int) $row['valor_Factura'],
            state: (int) $row['estado_Factura'],
            paymentDate: $row['fecha_Pago'],
        );
    }
}
