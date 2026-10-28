<?php

require_once __DIR__ . '/../config/Conexion.php';

// centraliza las consultas y cambios de estado de las incidencias.
class IncidenciaDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Trae todas las incidencias junto con el codigo del envio.
    public function obtenerTodos(): array
    {
        $sql = "SELECT incidencias.*, envios.codigo_envio
                FROM incidencias
                INNER JOIN envios ON incidencias.id_envio = envios.id_envio
                ORDER BY incidencias.fecha DESC, incidencias.hora DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca una incidencia puntual.
    public function obtenerPorId(int $idIncidencia): ?array
    {
        $sql = "SELECT incidencias.*, envios.codigo_envio, CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS registrada_por
                FROM incidencias
                INNER JOIN envios ON incidencias.id_envio = envios.id_envio
                INNER JOIN usuarios ON incidencias.id_usuario_registro = usuarios.id_usuario
                WHERE incidencias.id_incidencia = :id_incidencia";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_incidencia' => $idIncidencia]);
        $incidencia = $stmt->fetch();

        return $incidencia ?: null;
    }

    // Incidencias de un envio.
    public function obtenerPorEnvio(int $idEnvio): array
    {
        $sql = "SELECT * FROM incidencias WHERE id_envio = :id_envio ORDER BY fecha DESC, hora DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Registra una incidencia nueva (queda ABIERTA).
    public function crear(int $idEnvio, string $fecha, string $hora, string $lugar, int $idUsuarioRegistro, string $tipo, string $descripcion): bool
    {
        $sql = "INSERT INTO incidencias (id_envio, fecha, hora, lugar, id_usuario_registro, tipo, descripcion)
                VALUES (:id_envio, :fecha, :hora, :lugar, :id_usuario_registro, :tipo, :descripcion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_envio' => $idEnvio,
            'fecha' => $fecha,
            'hora' => $hora,
            'lugar' => $lugar,
            'id_usuario_registro' => $idUsuarioRegistro,
            'tipo' => $tipo,
            'descripcion' => $descripcion,
        ]);
    }

    // Cambia el estado de la incidencia y guarda su resolucion. La incidencia no se borra.
    public function actualizarEstado(int $idIncidencia, string $estado, ?string $resolucion): bool
    {
        $sql = "UPDATE incidencias
                SET estado = :estado, resolucion = :resolucion,
                    fecha_resolucion = IF(:estado_cierre IN ('RESUELTA', 'CERRADA'), NOW(), NULL)
                WHERE id_incidencia = :id_incidencia";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'estado' => $estado,
            'resolucion' => $resolucion,
            'estado_cierre' => $estado,
            'id_incidencia' => $idIncidencia,
        ]);
    }
}
