<?php

class Incidencia
{
    public function __construct(
        private int $idIncidencia,
        private int $idEnvio,
        private string $fecha,
        private string $hora,
        private string $lugar,
        private int $idUsuarioRegistro,
        private string $tipo,
        private string $descripcion,
        private string $estado,
        private ?string $resolucion,
        private ?string $fechaResolucion
    ) {
    }

    public function getIdIncidencia(): int
    {
        return $this->idIncidencia;
    }

    public function getIdEnvio(): int
    {
        return $this->idEnvio;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getHora(): string
    {
        return $this->hora;
    }

    public function getLugar(): string
    {
        return $this->lugar;
    }

    public function getIdUsuarioRegistro(): int
    {
        return $this->idUsuarioRegistro;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getResolucion(): ?string
    {
        return $this->resolucion;
    }

    public function getFechaResolucion(): ?string
    {
        return $this->fechaResolucion;
    }
}
