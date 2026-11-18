<?php
// Clase encargada de crear la conexion a MySQL usando PDO.
class Conexion
{
    // Datos de hosting; el usuario y la clave se completan en el entorno del servidor.
    private const CONFIGURACION_HOSTING = [
        'host' => 'pma.torga.com.ar',
        'puerto' => '3306',
        'base_datos' => 's11_logistica_delta',
        'usuario' => 'u11_ri1RgHICT0',
        'clave' => 'QdhO09kHGLJSAVWjv.l^W@@@',
    ];

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

        // En XAMPP conserva la base local; en el hosting usa los datos de arriba.
        $servidor = strtolower(preg_replace('/:\\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));
        $esLocal = in_array($servidor, ['localhost', '127.0.0.1'], true);
        $datosPredeterminados = $esLocal
            ? ['host' => 'localhost', 'puerto' => '3306', 'base_datos' => 'logistica_delta', 'usuario' => 'root', 'clave' => '']
            : self::CONFIGURACION_HOSTING;

        $this->host = getenv('DELTA_DB_HOST') ?: ($datosLocales['host'] ?? $datosPredeterminados['host']);
        $this->puerto = getenv('DELTA_DB_PORT') ?: ($datosLocales['puerto'] ?? $datosPredeterminados['puerto']);
        $this->dbName = getenv('DELTA_DB_NAME') ?: ($datosLocales['base_datos'] ?? $datosPredeterminados['base_datos']);
        $this->usuario = getenv('DELTA_DB_USER') ?: ($datosLocales['usuario'] ?? $datosPredeterminados['usuario']);
        $this->password = getenv('DELTA_DB_PASSWORD') ?: ($datosLocales['clave'] ?? $datosPredeterminados['clave']);
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
