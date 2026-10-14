<?php

class IntentoEntrega
{
    public function __construct(
        private int $idIntento,
        private int $idEnvio,
        private int $numeroIntento,
        private string $fecha,
        private string $hora,
        private int $idChofer,
        private string $resultado,
        private ?string $motivoFalla,
        private ?string $receptorNombre,
        private ?string $receptorDocumento,
        private ?string $observaciones
    ) {
    }

    public function getIdIntento(): int
    {
        return $this->idIntento;
    }

    public function getIdEnvio(): int
    {
        return $this->idEnvio;
    }

    public function getNumeroIntento(): int
    {
        return $this->numeroIntento;
    }

    public function getFecha(): string
    {
        return $this->fecha;
    }

    public function getHora(): string
    {
        return $this->hora;
    }

    public function getIdChofer(): int
    {
        return $this->idChofer;
    }

    public function getResultado(): string
    {
        return $this->resultado;
    }

    public function getMotivoFalla(): ?string
    {
        return $this->motivoFalla;
    }

    public function getReceptorNombre(): ?string
    {
        return $this->receptorNombre;
    }

    public function getReceptorDocumento(): ?string
    {
        return $this->receptorDocumento;
    }

    public function getObservaciones(): ?string
    {
        return $this->observaciones;
    }
}
