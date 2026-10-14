<?php

class Usuario
{
    public function __construct(
        private int $idUsuario,
        private string $nombre,
        private string $apellido,
        private ?string $dni,
        private string $email,
        private string $nombreUsuario,
        private string $passwordHash,
        private string $rol,
        private bool $activo,
        private ?string $fechaCreacion,
        private ?string $ultimoLogin
    ) {
    }

    public function getIdUsuario(): int
    {
        return $this->idUsuario;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getApellido(): string
    {
        return $this->apellido;
    }

    public function getDni(): ?string
    {
        return $this->dni;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getNombreUsuario(): string
    {
        return $this->nombreUsuario;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function getRol(): string
    {
        return $this->rol;
    }

    public function getActivo(): bool
    {
        return $this->activo;
    }

    public function getFechaCreacion(): ?string
    {
        return $this->fechaCreacion;
    }

    public function getUltimoLogin(): ?string
    {
        return $this->ultimoLogin;
    }
}
