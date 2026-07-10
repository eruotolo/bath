<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class SetInvoiceState
{
    private const ANULADA = 3;

    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    /**
     * Al pasar a "Anulada" (3), libera los servicios asociados para que puedan volver a facturarse
     * -- misma regla que invoice-delete.php e invoice-estado.php ya aplicaban por separado.
     */
    public function handle(int $id, int $state): void
    {
        $this->repository->setState($id, $state);

        if ($state === self::ANULADA) {
            $this->repository->releaseServices($id);
        }
    }
}
