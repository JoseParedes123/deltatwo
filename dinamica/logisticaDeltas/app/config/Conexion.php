<?php

class Conexion
{
    private string $host;
    private string $baseDatos;
    private string $usuario;
    private string $contrasena;
    private ?PDO $conexion = null;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->baseDatos = getenv('DB_NAME') ?: 'logistica_delta';
        $this->usuario = getenv('DB_USER') ?: 'root';
        $this->contrasena = getenv('DB_PASSWORD') ?: '';
    }

    public function conectar(): PDO
    {
        if ($this->conexion === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->baseDatos};charset=utf8mb4";

            $this->conexion = new PDO($dsn, $this->usuario, $this->contrasena, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return $this->conexion;
    }
}