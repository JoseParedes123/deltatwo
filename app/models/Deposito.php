<?php

// representa un deposito y su estado de disponibilidad.
class Deposito
{
    public function __construct(
        private int $idDeposito,
        private string $nombre,
        private string $direccion,
        private bool $activo
    ) {
    }

    public function getIdDeposito(): int
    {
        return $this->idDeposito;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }
}
