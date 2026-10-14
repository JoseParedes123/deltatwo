<?php

class Zona
{
    public function __construct(
        private int $idZona,
        private string $nombre,
        private string $cobertura,
        private ?string $descripcion,
        private bool $activo
    ) {
    }

    public function getIdZona(): int
    {
        return $this->idZona;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getCobertura(): string
    {
        return $this->cobertura;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }
}
