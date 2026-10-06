<?php

class Envio
{
    public function __construct(
        private int $idEnvio,
        private string $codigoEnvio,
        private string $estado,
        private string $origen,
        private string $destino,
        private ?string $descripcionPaquete,
        private ?string $pesoKg,
        private string $fechaSolicitud,
        private ?string $fechaEntregaEstimada
    ) {
    }

    public function getIdEnvio(): int
    {
        return $this->idEnvio;
    }

    public function getCodigoEnvio(): string
    {
        return $this->codigoEnvio;
    }

    public function getEstado(): string
    {
        return $this->estado;
    }

    public function getEstadoLegible(): string
    {
        $estados = [
            'REGISTRADO' => 'Registrado',
            'RECIBIDO_DEPOSITO' => 'Recibido en deposito',
            'PREPARADO' => 'En preparacion',
            'EN_TRANSITO' => 'En transito',
            'EN_REPARTO' => 'En reparto',
            'ENTREGADO' => 'Entregado',
            'NO_ENTREGADO' => 'No entregado',
            'DEVUELTO' => 'Devuelto',
            'EXTRAVIADO' => 'Extraviado',
            'CANCELADO' => 'Cancelado',
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    public function getOrigen(): string
    {
        return $this->origen;
    }

    public function getDestino(): string
    {
        return $this->destino;
    }

    public function getDescripcionPaquete(): ?string
    {
        return $this->descripcionPaquete;
    }

    public function getPesoKg(): ?string
    {
        return $this->pesoKg;
    }

    public function getFechaSolicitud(): string
    {
        return $this->fechaSolicitud;
    }

    public function getFechaEntregaEstimada(): ?string
    {
        return $this->fechaEntregaEstimada;
    }
}