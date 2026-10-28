<?php

// representa una posicion dentro de un deposito y si esta ocupada.
class Ubicacion
{
    public function __construct(
        private int $idUbicacion,
        private int $idDeposito,
        private string $sector,
        private string $estante,
        private string $posicion,
        private bool $ocupada
    ) {
    }

    public function getIdUbicacion(): int
    {
        return $this->idUbicacion;
    }

    public function getIdDeposito(): int
    {
        return $this->idDeposito;
    }

    public function getSector(): string
    {
        return $this->sector;
    }

    public function getEstante(): string
    {
        return $this->estante;
    }

    public function getPosicion(): string
    {
        return $this->posicion;
    }

    public function getOcupada(): bool
    {
        return $this->ocupada;
    }
}
