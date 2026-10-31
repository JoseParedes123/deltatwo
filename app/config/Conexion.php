<?php
// Clase encargada de crear la conexion a MySQL usando PDO.
class Conexion
{
    // Comparte los datos de conexion que usan los DAO.
    private string $host;
    private string $puerto;
    private string $dbName;
    private string $usuario;
    private string $password;
    private ?PDO $conexion = null;

    public function __construct()
    {
        // Usa primero el entorno del servidor y luego el archivo local ignorado por git.
        $archivoLocal = __DIR__ . '/Conexion.local.php';
        $datosLocales = is_file($archivoLocal) ? require $archivoLocal : [];
        $datosLocales = is_array($datosLocales) ? $datosLocales : [];

        $this->host = getenv('DELTA_DB_HOST') ?: ($datosLocales['host'] ?? 'localhost');
        $this->puerto = getenv('DELTA_DB_PORT') ?: ($datosLocales['puerto'] ?? '3306');
        $this->dbName = getenv('DELTA_DB_NAME') ?: ($datosLocales['base_datos'] ?? 'logistica_delta');
        $this->usuario = getenv('DELTA_DB_USER') ?: ($datosLocales['usuario'] ?? 'root');
        $this->password = getenv('DELTA_DB_PASSWORD') ?: ($datosLocales['clave'] ?? '');
    }

    public function conectar(): PDO
    {
        // Reutiliza la conexion si ya fue creada.
        if ($this->conexion === null) {
            $dsn = "mysql:host={$this->host};port={$this->puerto};dbname={$this->dbName};charset=utf8mb4";

            $this->conexion = new PDO($dsn, $this->usuario, $this->password);
            // Devuelve errores como excepciones y filas como arreglos asociativos.
            $this->conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return $this->conexion;
    }
}
