<?php

require_once __DIR__ . '/../config/Conexion.php';

class UbicacionDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de ubicaciones.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve las ubicaciones de un deposito (sector, estante y posicion).
    public function obtenerPorDeposito(int $idDeposito): array
    {
        $sql = "SELECT * FROM ubicaciones
                WHERE id_deposito = :id_deposito
                ORDER BY sector, estante, posicion";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_deposito' => $idDeposito]);

        return $stmt->fetchAll();
    }

    // Busca una ubicacion puntual.
    public function obtenerPorId(int $idUbicacion): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM ubicaciones WHERE id_ubicacion = :id_ubicacion");
        $stmt->execute(['id_ubicacion' => $idUbicacion]);
        $ubicacion = $stmt->fetch();

        return $ubicacion ?: null;
    }

    // Carga una ubicacion nueva. Los empleados despues eligen entre las existentes.
    public function crear(int $idDeposito, string $sector, string $estante, string $posicion): bool
    {
        $sql = "INSERT INTO ubicaciones (id_deposito, sector, estante, posicion)
                VALUES (:id_deposito, :sector, :estante, :posicion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_deposito' => $idDeposito,
            'sector' => $sector,
            'estante' => $estante,
            'posicion' => $posicion,
        ]);
    }

    // Marca si la posicion esta ocupada o libre.
    public function actualizarOcupada(int $idUbicacion, bool $ocupada): bool
    {
        $sql = "UPDATE ubicaciones SET ocupada = :ocupada WHERE id_ubicacion = :id_ubicacion";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'ocupada' => (int) $ocupada,
            'id_ubicacion' => $idUbicacion,
        ]);
    }
}
