<?php

class Comprobante
{
    public function __construct(
        private int $idComprobante,
        private int $idEnvio,
        private string $numeroComprobante,
        private ?string $fechaEmision,
        private string $tipo,
        private ?string $rutaArchivo,
        private int $idUsuarioEmisor
    ) {
    }

    public function getIdComprobante(): int
    {
        return $this->idComprobante;
    }

    public function getIdEnvio(): int
    {
        return $this->idEnvio;
    }

    public function getNumeroComprobante(): string
    {
        return $this->numeroComprobante;
    }

    public function getFechaEmision(): ?string
    {
        return $this->fechaEmision;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getRutaArchivo(): ?string
    {
        return $this->rutaArchivo;
    }

    public function getIdUsuarioEmisor(): int
    {
        return $this->idUsuarioEmisor;
    }
}
