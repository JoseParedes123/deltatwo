<?php

require_once __DIR__ . '/../config/Conexion.php';

// centraliza el historial interno de ubicaciones de cada paquete.
class MovimientoDepositoDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Recorrido interno de un paquete: de la ubicacion anterior a la nueva, quien y cuando.
    public function obtenerPorPaquete(int $idPaquete): array
    {
        $sql = "SELECT movimientos_deposito.*,
                       CONCAT('Sector ', nueva.sector, ' / Estante ', nueva.estante, ' / Posicion ', nueva.posicion) AS ubicacion_nueva,
                       CONCAT('Sector ', anterior.sector, ' / Estante ', anterior.estante, ' / Posicion ', anterior.posicion) AS ubicacion_anterior,
                       CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS usuario
                FROM movimientos_deposito
                INNER JOIN ubicaciones nueva ON movimientos_deposito.id_ubicacion_nueva = nueva.id_ubicacion
                LEFT JOIN ubicaciones anterior ON movimientos_deposito.id_ubicacion_anterior = anterior.id_ubicacion
                INNER JOIN usuarios ON movimientos_deposito.id_usuario = usuarios.id_usuario
                WHERE movimientos_deposito.id_paquete = :id_paquete
                ORDER BY movimientos_deposito.fecha_hora DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_paquete' => $idPaquete]);

        return $stmt->fetchAll();
    }

    // Donde esta guardado hoy el paquete (su ultimo movimiento).
    public function obtenerActualPorPaquete(int $idPaquete): ?array
    {
        $sql = "SELECT movimientos_deposito.*, ubicaciones.sector, ubicaciones.estante, ubicaciones.posicion
                FROM movimientos_deposito
                INNER JOIN ubicaciones ON movimientos_deposito.id_ubicacion_nueva = ubicaciones.id_ubicacion
                WHERE movimientos_deposito.id_paquete = :id_paquete AND movimientos_deposito.es_actual = 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_paquete' => $idPaquete]);
        $movimiento = $stmt->fetch();

        return $movimiento ?: null;
    }

    // Antes de registrar un movimiento nuevo, el anterior deja de ser el actual.
    public function marcarNoActual(int $idPaquete): bool
    {
        $sql = "UPDATE movimientos_deposito SET es_actual = 0 WHERE id_paquete = :id_paquete AND es_actual = 1";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id_paquete' => $idPaquete]);
    }

    // Registra el movimiento de un paquete: ubicacion anterior, nueva, usuario y motivo.
    public function crear(int $idPaquete, ?int $idUbicacionAnterior, int $idUbicacionNueva, int $idUsuario, string $motivo): bool
    {
        $sql = "INSERT INTO movimientos_deposito (id_paquete, id_ubicacion_anterior, id_ubicacion_nueva, id_usuario, motivo)
                VALUES (:id_paquete, :id_ubicacion_anterior, :id_ubicacion_nueva, :id_usuario, :motivo)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_paquete' => $idPaquete,
            'id_ubicacion_anterior' => $idUbicacionAnterior,
            'id_ubicacion_nueva' => $idUbicacionNueva,
            'id_usuario' => $idUsuario,
            'motivo' => $motivo,
        ]);
    }
}
