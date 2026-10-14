<?php

class Cliente
{
    public function __construct(
        private int $idCliente,
        private int $idUsuario,
        private string $tipoCliente,
        private ?string $razonSocial,
        private ?string $cuitDni,
        private ?string $telefono,
        private ?string $direccion,
        private string $estadoValidacion,
        private bool $aprobadoCorporativo,
        private string $descuentoPorcentaje,
        private ?string $fechaRegistro
    ) {
    }

    public function getIdCliente(): int
    {
        return $this->idCliente;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getTipoCliente(): string
    {
        return $this->tipoCliente;
    }

    public function getRazonSocial(): ?string
    {
        return $this->razonSocial;
    }

    public function getCuitDni(): ?string
    {
        return $this->cuitDni;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getDireccion(): ?string
    {
        return $this->direccion;
    }

    public function getEstadoValidacion(): string
    {
        return $this->estadoValidacion;
    }

    public function getAprobadoCorporativo(): bool
    {
        return $this->aprobadoCorporativo;
    }

    public function getDescuentoPorcentaje(): string
    {
        return $this->descuentoPorcentaje;
    }

    public function getFechaRegistro(): ?string
    {
        return $this->fechaRegistro;
    }
}
