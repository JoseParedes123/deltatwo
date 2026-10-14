<?php
// Clase encargada de crear la conexion a MySQL usando PDO.
class Conexion
{
    // Datos de acceso para XAMPP/MySQL local.
    private string $host = 'localhost';
    private string $dbName = 'logistica_delta';
    private string $usuario = 'root';
    private string $password = '';
    private ?PDO $conexion = null;

    public function conectar(): PDO
    {
        // Si ya existe una conexion, se reutiliza para no abrir otra.
        if ($this->conexion === null) {
            $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

            $this->conexion = new PDO($dsn, $this->usuario, $this->password);
            // Muestra errores como excepciones y devuelve resultados como arrays asociativos.
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return $this->conexion;
    }
}
