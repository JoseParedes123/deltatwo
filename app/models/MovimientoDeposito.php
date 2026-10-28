<?php

// representa el cambio de ubicacion interna de un paquete.
class MovimientoDeposito
{
    public function __construct(
        private int $idMovimiento,
        private int $idPaquete,
        private ?int $idUbicacionAnterior,
        private int $idUbicacionNueva,
        private bool $esActual,
        private int $idUsuario,
        private ?string $fechaHora,
        private string $motivo
    ) {
    }

    public function getIdMovimiento(): int
    {
        return $this->idMovimiento;
    }

    public function getIdPaquete(): int
    {
        return $this->idPaquete;
    }

    public function getIdUbicacionAnterior(): ?int
    {
        return $this->idUbicacionAnterior;
    }

    public function getIdUbicacionNueva(): int
    {
        return $this->idUbicacionNueva;
    }

    public function getEsActual(): bool
    {
        return $this->esActual;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getFechaHora(): ?string
    {
        return $this->fechaHora;
    }

    public function getMotivo(): string
    {
        return $this->motivo;
    }
}
