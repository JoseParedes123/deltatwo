<?php

class Historial
{
    public function __construct(
        private int $idHistorial,
        private string $tipoEvento,
        private string $tablaAfectada,
        private int $registroId,
        private ?string $campoModificado,
        private ?string $valorAnterior,
        private ?string $valorNuevo,
        private int $idUsuario,
        private ?string $fechaHora,
        private ?string $descripcion
    ) {
    }

    public function getIdHistorial(): int
    {
        return $this->idHistorial;
    }

    public function getTipoEvento(): string
    {
        return $this->tipoEvento;
    }

    public function getTablaAfectada(): string
    {
        return $this->tablaAfectada;
    }

    public function getRegistroId(): int
    {
        return $this->registroId;
    }

    public function getCampoModificado(): ?string
    {
        return $this->campoModificado;
    }

    public function getValorAnterior(): ?string
    {
        return $this->valorAnterior;
    }

    public function getValorNuevo(): ?string
    {
        return $this->valorNuevo;
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getFechaHora(): ?string
    {
        return $this->fechaHora;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }
}
