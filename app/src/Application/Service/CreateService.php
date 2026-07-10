<?php

namespace App\Application\Service;

use App\Domain\Service\Service;
use App\Domain\Service\ServiceRepositoryInterface;

final class CreateService
{
    public function __construct(private readonly ServiceRepositoryInterface $repository) {}

    public function handle(array $input): int
    {
        $service = new Service(
            id: null,
            contractId: (int) $input['id_Contrato'],
            nro: (int) str_pad((string) mt_rand(1, 999999), 6, '0', STR_PAD_LEFT),
            date: $input['fecha_Servicio'],
            observations: $input['observaciones_Servicio'],
            state: 1,
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

        return $this->repository->insert($service);
    }
}
