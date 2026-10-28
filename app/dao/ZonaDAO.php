<?php

require_once __DIR__ . '/../config/Conexion.php';

// centraliza la consulta y administracion de zonas de cobertura.
class ZonaDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Devuelve las zonas activas para listarlas en formularios y vistas.
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM zonas WHERE activo = 1 ORDER BY nombre";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca una zona puntual.
    public function obtenerPorId(int $idZona): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM zonas WHERE id_zona = :id_zona");
        $stmt->execute(['id_zona' => $idZona]);
        $zona = $stmt->fetch();

        return $zona ?: null;
    }

    // Carga una zona nueva desde administracion.
    public function crear(string $nombre, string $cobertura, ?string $descripcion): bool
    {
        $sql = "INSERT INTO zonas (nombre, cobertura, descripcion)
                VALUES (:nombre, :cobertura, :descripcion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'cobertura' => $cobertura,
            'descripcion' => $descripcion,
        ]);
    }

    // Modifica los datos de una zona.
    public function actualizar(int $idZona, string $nombre, string $cobertura, ?string $descripcion): bool
    {
        $sql = "UPDATE zonas
                SET nombre = :nombre, cobertura = :cobertura, descripcion = :descripcion
                WHERE id_zona = :id_zona";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'cobertura' => $cobertura,
            'descripcion' => $descripcion,
            'id_zona' => $idZona,
        ]);
    }

    // No se borra: queda inactiva para conservar los envios que la usaron.
    public function desactivar(int $idZona): bool
    {
        $stmt = $this->db->prepare("UPDATE zonas SET activo = 0 WHERE id_zona = :id_zona");

        return $stmt->execute(['id_zona' => $idZona]);
    }
}
