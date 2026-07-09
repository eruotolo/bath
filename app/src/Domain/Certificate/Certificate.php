<?php

namespace App\Domain\Certificate;

final class Certificate
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nroCertificado,
        public readonly int $idCliente,
        public readonly int $idContrato,
        public readonly string $fechaServicio,
        public readonly int $mts,
    ) {}

    /**
     * Número visible al usuario (no se guarda): fecha de emisión + "A" + correlativo diario.
     * Ej: fechaHoy=2026-07-09, nroCertificado="00001" -> "09072026A00001".
     */
    public static function displayNumber(string $fechaHoy, string $nroCertificado): string
    {
        return date('dmY', strtotime($fechaHoy)) . 'A' . $nroCertificado;
    }
}
