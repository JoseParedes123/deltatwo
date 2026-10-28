<?php

// representa una ruta de reparto, su vehiculo y su chofer asignado.
class Ruta
{
    public function __construct(
        private int $idRuta,
        private string $fecha,
        private string $origen,
        private string $destino,
        private ?string $recorrido,
        private ?string $observaciones,
        private int $idVehiculo,
        private int $idChofer,
        private ?int $idZona,
        private string $estado
    ) {
    }

    public function getIdRuta(): int
    {
        return $this->idRuta;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getOrigen(): string
    {
        return $this->origen;
    }

    public function getDestino(): string
    {
        return $this->destino;
    }

    public function getRecorrido(): ?string
    {
        return $this->recorrido;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }

    public function getIdVehiculo(): int
    {
        return $this->idVehiculo;
    }

    public function getIdChofer(): int
    {
        return $this->idChofer;
    }

    public function getIdZona(): ?int
    {
        return $this->idZona;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }
}
