<?php

namespace App\Application\Bathroom;

use App\Domain\Bathroom\Bathroom;
use App\Domain\Bathroom\BathroomRepositoryInterface;

final class UpdateBathroom
{
    public function __construct(private readonly BathroomRepositoryInterface $repository) {}

    /**
     * @return bool false si el código ya existe en otro baño
     */
    public function handle(int $id, array $input): bool
    {
        $codigo = $input['codigo_Bath'];

        if ($this->repository->codeExists($codigo, $id)) {
            return false;
        }

        $bathroom = new Bathroom(
            id: $id,
            codigoBath: $codigo,
            fechaCompraBath: $input['fechaCompra_Bath'],
            observacionBath: $input['observacion_Bath'],
            estadoBath: (int) $input['estado_Bath'],
        );

        $this->repository->update($bathroom);

        return true;
    }
}
