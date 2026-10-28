<?php

// representa un paquete con sus dimensiones y tipo de mercaderia.
class Paquete
{
    public function __construct(
        private int $idPaquete,
        private string $codigo,
        private ?string $descripcion,
        private string $pesoKg,
        private ?string $largoCm,
        private ?string $anchoCm,
        private ?string $altoCm,
        private int $cantidadBultos,
        private string $tipoMercaderia,
        private ?string $valorDeclarado,
        private bool $esFragil,
        private ?string $fechaRegistro
    ) {
    }

    public function getIdPaquete(): int
    {
        return $this->idPaquete;
    }

    public function getCodigo(): string
    {
        return $this->codigo;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function getPesoKg(): string
    {
        return $this->pesoKg;
    }

    public function getLargoCm(): ?string
    {
        return $this->largoCm;
    }

    public function getAnchoCm(): ?string
    {
        return $this->anchoCm;
    }

    public function getAltoCm(): ?string
    {
        return $this->altoCm;
    }

    public function getCantidadBultos(): int
    {
        return $this->cantidadBultos;
    }

    public function getTipoMercaderia(): string
    {
        return $this->tipoMercaderia;
    }

    public function getValorDeclarado(): ?string
    {
        return $this->valorDeclarado;
    }

    public function getEsFragil(): bool
    {
        return $this->esFragil;
    }

    public function getFechaRegistro(): ?string
    {
        return $this->fechaRegistro;
    }
}
