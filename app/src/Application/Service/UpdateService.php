<?php

namespace App\Application\Service;

use App\Domain\Service\Service;
use App\Domain\Service\ServiceRepositoryInterface;

final class UpdateService
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    public function handle(int $id, array $input): void
    {
        $service = new Service(
            id: $id,
            contractId: (int) $input['id_Contrato'],
            nro: (int) $input['nro_Servicio'],
            date: $input['fecha_Servicio'],
            observations: $input['observaciones_Servicio'],
            state: 1,
            value: (int) ($input['valor_Servicio'] ?? 0),
            installation: !empty($input['instalacion_Tipo']),
            repair: !empty($input['reparacion_Tipo']),
            cleaning: !empty($input['limpieza_Tipo']),
            disinfection: !empty($input['desinfeccion_Tipo']),
            sanitization: !empty($input['sanitizacion_Tipo']),
            toiletPaper: !empty($input['higienico_Tipo']),
            soap: !empty($input['jabon_Tipo']),
            others: !empty($input['otros_Tipo']),
            removal: !empty($input['retiro_Tipo']),
        );

        $this->repository->update($service);
    }
}
