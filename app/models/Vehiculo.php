<?php

class Vehiculo
{
    public function __construct(
        private int $idVehiculo,
        private string $patente,
        private string $tipo,
        private ?string $marca,
        private ?string $modelo,
        private ?int $anio,
        private string $capacidadPesoKg,
        private string $capacidadVolumenM3,
        private string $estado,
        private ?int $idZonaRestringida,
        private ?string $motivoRestriccion
    ) {
    }

    public function getIdVehiculo(): int
    {
        return $this->idVehiculo;
    }

    public function getPatente(): string
    {
        return $this->patente;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function getModelo(): ?string
    {
        return $this->modelo;
    }

    public function getAnio(): ?int
    {
        return $this->anio;
    }

    public function getCapacidadPesoKg(): string
    {
        return $this->capacidadPesoKg;
    }

    public function getCapacidadVolumenM3(): string
    {
        return $this->capacidadVolumenM3;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getIdZonaRestringida(): ?int
    {
        return $this->idZonaRestringida;
    }

    public function getMotivoRestriccion(): ?string
    {
        return $this->motivoRestriccion;
    }
}
