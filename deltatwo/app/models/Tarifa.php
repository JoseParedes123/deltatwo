<?php

class Tarifa
{
    public function __construct(
        private int $idTarifa,
        private string $nombre,
        private int $idZona,
        private string $tipoEnvio,
        private string $precioBase,
        private string $precioPorKg,
        private string $precioPorM3,
        private string $recargoFragil,
        private string $vigenteDesde,
        private ?string $vigenteHasta,
        private bool $activo
    ) {
    }

    public function getIdTarifa(): int
    {
        return $this->idTarifa;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getIdZona(): int
    {
        return $this->idZona;
    }

    public function getTipoEnvio(): string
    {
        return $this->tipoEnvio;
    }

    public function getPrecioBase(): string
    {
        return $this->precioBase;
    }

    public function getPrecioPorKg(): string
    {
        return $this->precioPorKg;
    }

    public function getPrecioPorM3(): string
    {
        return $this->precioPorM3;
    }

    public function getRecargoFragil(): string
    {
        return $this->recargoFragil;
    }

    public function getVigenteDesde(): string
    {
        return $this->vigenteDesde;
    }

    public function getVigenteHasta(): ?string
    {
        return $this->vigenteHasta;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }
}
