<?php

// representa un envio y reúne sus datos principales de seguimiento.
class Envio
{
    private int $idEnvio;
    private string $codigoEnvio;
    private string $estado;
    private string $origen;
    private string $destino;
    private ?string $descripcionPaquete;
    private ?float $pesoKg;
    private string $fechaSolicitud;
    private ?string $fechaEntregaEstimada;

    public function __construct(
        int $idEnvio,
        string $codigoEnvio,
        string $estado,
        string $origen,
        string $destino,
        ?string $descripcionPaquete,
        ?float $pesoKg,
        string $fechaSolicitud,
        ?string $fechaEntregaEstimada
    ) {
        $this->idEnvio = $idEnvio;
        $this->codigoEnvio = $codigoEnvio;
        $this->estado = $estado;
        $this->origen = $origen;
        $this->destino = $destino;
        $this->descripcionPaquete = $descripcionPaquete;
        $this->pesoKg = $pesoKg;
        $this->fechaSolicitud = $fechaSolicitud;
        $this->fechaEntregaEstimada = $fechaEntregaEstimada;
    }

    public function getIdEnvio(): int { return $this->idEnvio; }
    public function getCodigoEnvio(): string { return $this->codigoEnvio; }
    public function getEstado(): string { return $this->estado; }
    public function getOrigen(): string { return $this->origen; }
    public function getDestino(): string { return $this->destino; }
    public function getDescripcionPaquete(): ?string { return $this->descripcionPaquete; }
    public function getPesoKg(): ?float { return $this->pesoKg; }
    public function getFechaSolicitud(): string { return $this->fechaSolicitud; }
    public function getFechaEntregaEstimada(): ?string { return $this->fechaEntregaEstimada; }
}
