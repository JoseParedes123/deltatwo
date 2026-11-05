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
        $stmt = $this->db->prepare("SELECT COALESCE(z.nombre, 'Ubicación no informada') AS localidad,
                                           COALESCE(s.direccion, '') AS direccion, COALESCE(s.provincia, '') AS provincia,
                                           m.detalle, m.fecha_hora
                                    FROM movimientos_seguimiento m
                                    LEFT JOIN sucursales s ON s.id_sucursal = m.id_sucursal
                                    LEFT JOIN zonas z ON z.id_zona = s.id_zona
                                    WHERE m.id_envio = :id_envio
                                    ORDER BY m.fecha_hora, m.id_movimiento");
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Guarda una ubicacion y un detalle para el seguimiento publico.
    public function crear(int $idEnvio, int $idSucursal, string $detalle): bool
    {
        $sql = "INSERT INTO movimientos_seguimiento (id_envio, id_sucursal, detalle)
                VALUES (:id_envio, :id_sucursal, :detalle)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_envio' => $idEnvio,
            'id_sucursal' => $idSucursal,
            'detalle' => $detalle,
        ]);
    }
}
