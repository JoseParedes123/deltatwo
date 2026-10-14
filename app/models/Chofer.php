<?php

class Chofer
{
    public function __construct(
        private int $idChofer,
        private int $idUsuario,
        private string $licencia,
        private string $categoriaLicencia,
        private string $fechaVencimientoLicencia,
        private string $estado
    ) {
    }

    public function getIdChofer(): int
    {
        return $this->idChofer;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getLicencia(): string
    {
        return $this->licencia;
    }

    public function getCategoriaLicencia(): string
    {
        return $this->categoriaLicencia;
    }

    public function getFechaVencimientoLicencia(): string
    {
        return $this->fechaVencimientoLicencia;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }
}
