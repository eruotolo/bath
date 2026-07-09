<?php

namespace App\Application\Contract;

use App\Domain\Contract\Contract;
use App\Domain\Contract\ContractRepositoryInterface;

final class UpdateContract
{
    public function __construct(private readonly ContractRepositoryInterface $repository) {}

    public function handle(int $id, array $input): void
    {
        $contract = new Contract(
            id: $id,
            customerId: (int) $input['id_Cliente'],
            obra: $input['obra_Contrato'],
            address: $input['direccion_Contrato'],
            state: (int) $input['estado_Contrato'],
            startDate: $input['fechaInicio_Contrato'],
            endDate: $input['fechaFin_Contrato'],
            monthlyValue: (int) $input['valorMensual_Contrato'],
            totalValue: (int) $input['valorTotal_Contrato'],
            observation: $input['observacion_Contrato'],
        );

        $this->repository->update($contract);
    }
}
