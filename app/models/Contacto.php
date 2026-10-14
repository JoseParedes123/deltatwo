<?php

class Contacto
{
    public function __construct(
        private int $idContacto,
        private string $tipo,
        private string $nombre,
        private ?string $telefono,
        private ?string $email,
        private string $direccion,
        private ?int $idZona,
        private ?int $idCliente
    ) {
    }

    public function getIdContacto(): int
    {
        return $this->idContacto;
    }

    public function getTipo(): string
    {
        return $this->tipo;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getTelefono(): ?string
    {
        return $this->telefono;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getIdZona(): ?int
    {
        return $this->idZona;
    }

    public function getIdCliente(): ?int
    {
        return $this->idCliente;
    }
}
