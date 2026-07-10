<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\Invoice;
use App\Domain\Invoice\InvoiceRepositoryInterface;

final class CreateInvoice
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $invoice = new Invoice(
            id: null,
            customerId: (int) $input['id_Cliente'],
            contractId: (int) $input['id_Contrato'],
            number: $input['numero_Factura'],
            date: $input['fecha_Factura'],
            value: (int) $input['valor_Factura'],
            state: 1,
        );

        return $this->repository->insert($invoice);
    }
}
