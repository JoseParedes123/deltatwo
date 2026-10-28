<?php

require_once __DIR__ . '/../config/Conexion.php';

// registra y consulta los movimientos visibles en el seguimiento publico.
class MovimientoSeguimientoDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Trae el recorrido en el orden en que pasaron los movimientos.
    public function obtenerPorEnvio(int $idEnvio): array
    {
        $stmt = $this->db->prepare("SELECT sucursal, provincia, detalle, fecha_hora
                                    FROM movimientos_seguimiento
                                    WHERE id_envio = :id_envio
                                    ORDER BY fecha_hora, id_movimiento");
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Guarda una ubicacion y un detalle para el seguimiento publico.
    public function crear(int $idEnvio, string $sucursal, string $provincia, string $detalle): bool
    {
        $sql = "INSERT INTO movimientos_seguimiento (id_envio, sucursal, provincia, detalle)
                VALUES (:id_envio, :sucursal, :provincia, :detalle)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_envio' => $idEnvio,
            'sucursal' => $sucursal,
            'provincia' => $provincia,
            'detalle' => $detalle,
        ]);
    }
}
