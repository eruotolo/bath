<?php

namespace App\Domain\Invoice;

interface InvoiceRepositoryInterface
{
    public function insert(Invoice $invoice): int;

    public function update(Invoice $invoice): void;

    public function setState(int $id, int $state): void;

    public function setPaymentDate(int $id, ?string $paymentDate): void;

    public function find(int $id): ?Invoice;

    /**
     * Factura + cliente + contrato (join), para detail/print/pdf.
     * @return array<string, mixed>|null
     */
    public function findWithCustomerAndContract(int $id, int $contractId): ?array;

    public function existsByNumber(string $number): bool;

    /**
     * Facturas activas (estado 1 o 2) con cliente/obra, para dash-invoices-list.php.
     * @return array<int, array<string, mixed>>
     */
    public function listActiveWithDetails(): array;

    /**
     * Libera los servicios asociados a una factura anulada (para que puedan volver a facturarse).
     */
    public function releaseServices(int $idFactura): void;

    public function assignService(int $idFactura, int $idServicio): void;

    public function removeAssignedService(int $idRelacion): void;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listAssignedServices(int $idFactura): array;

    /**
     * Servicios del contrato que todavía no fueron facturados, para el selector de "Agregar Servicios".
     * @return array<int, array<string, mixed>>
     */
    public function listUnbilledServicesForContract(int $customerId, int $contractId): array;
}
