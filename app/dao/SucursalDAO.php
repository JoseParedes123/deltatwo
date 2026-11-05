<?php

require_once __DIR__ . '/../config/Conexion.php';

class SucursalDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    public function obtenerActivas(): array
    {
        $sql = "SELECT s.id_sucursal, s.id_zona, z.nombre AS localidad,
                       s.direccion, s.provincia
                FROM sucursales s
                INNER JOIN zonas z ON z.id_zona = s.id_zona
                WHERE s.activa = 1 AND z.activo = 1 AND z.cobertura = 'BUENOS_AIRES'
                ORDER BY z.nombre, s.direccion";
        return $this->db->query($sql)->fetchAll();
    }

    public function obtenerPrimeraPorZona(int $idZona): ?array
    {
        $stmt = $this->db->prepare("SELECT s.id_sucursal, s.id_zona, z.nombre AS localidad,
                                           s.direccion, s.provincia
                                    FROM sucursales s
                                    INNER JOIN zonas z ON z.id_zona = s.id_zona
                                    WHERE s.id_zona = :id_zona AND s.activa = 1 AND z.activo = 1
                                      AND z.cobertura = 'BUENOS_AIRES'
                                    ORDER BY s.id_sucursal LIMIT 1");
        $stmt->execute(['id_zona' => $idZona]);
        $sucursal = $stmt->fetch();
        return $sucursal ?: null;
    }

    public function obtenerActivaPorId(int $idSucursal): ?array
    {
        $stmt = $this->db->prepare("SELECT s.id_sucursal, s.id_zona, z.nombre AS localidad,
                                           s.direccion, s.provincia
                                    FROM sucursales s
                                    INNER JOIN zonas z ON z.id_zona = s.id_zona
                                    WHERE s.id_sucursal = :id_sucursal
                                      AND s.activa = 1 AND z.activo = 1 AND z.cobertura = 'BUENOS_AIRES'");
        $stmt->execute(['id_sucursal' => $idSucursal]);
        $sucursal = $stmt->fetch();
        return $sucursal ?: null;
    }
}
