<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\Bathroom;
use App\Domain\Bathroom\BathroomRepositoryInterface;

final class CreateBathroom
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    /**
     * @return int|null id del baño nuevo, o null si el código ya existe
     */
    public function handle(array $input): ?int
    {
        $codigo = $input['codigo_Bath'];

        if ($this->repository->codeExists($codigo)) {
            return null;
        }

        $bathroom = new Bathroom(
            id: null,
            codigoBath: $codigo,
            fechaCompraBath: $input['fechaCompra_Bath'],
            observacionBath: $input['observacion_Bath'],
            estadoBath: (int) $input['estado_Bath'],
        );

        return $this->repository->insert($bathroom);
    }
}
