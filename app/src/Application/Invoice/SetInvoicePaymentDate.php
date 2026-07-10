<?php

namespace App\Application\Invoice;

use App\Domain\Invoice\InvoiceRepositoryInterface;

final class SetInvoicePaymentDate
{
    public function __construct(private readonly InvoiceRepositoryInterface $repository) {}

    public function handle(int $id, ?string $paymentDate): void
    {
        $this->repository->setPaymentDate($id, $paymentDate);
    }
}
