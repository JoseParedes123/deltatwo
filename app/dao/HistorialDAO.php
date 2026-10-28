<?php

require_once __DIR__ . '/../config/Conexion.php';

// centraliza la consulta y el registro de eventos de auditoria.
class HistorialDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Historial de un registro puntual, ej: tabla 'envios' e id del envio.
    public function obtenerPorRegistro(string $tablaAfectada, int $registroId): array
    {
        $sql = "SELECT historial.*, CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS usuario
                FROM historial
                INNER JOIN usuarios ON historial.id_usuario = usuarios.id_usuario
                WHERE historial.tabla_afectada = :tabla_afectada AND historial.registro_id = :registro_id
                ORDER BY historial.fecha_hora DESC, historial.id_historial DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
        ]);

        return $stmt->fetchAll();
    }

    // Cambios de estado de un envio, sin datos de empleados (para mostrarle al cliente).
    public function obtenerEstadosDeEnvio(int $idEnvio): array
    {
        $sql = "SELECT valor_anterior, valor_nuevo, fecha_hora
                FROM historial
                WHERE tabla_afectada = 'envios' AND tipo_evento = 'CAMBIO_ESTADO' AND registro_id = :id_envio
                ORDER BY fecha_hora, id_historial";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Guarda un evento: quien, que tabla, que registro y el valor anterior y nuevo.
    // El historial solo se agrega y se consulta: no se modifica ni se borra.
    public function registrar(string $tipoEvento, string $tablaAfectada, int $registroId, ?string $campoModificado, ?string $valorAnterior, ?string $valorNuevo, int $idUsuario, ?string $descripcion): bool
    {
        $sql = "INSERT INTO historial (tipo_evento, tabla_afectada, registro_id, campo_modificado, valor_anterior, valor_nuevo, id_usuario, descripcion)
                VALUES (:tipo_evento, :tabla_afectada, :registro_id, :campo_modificado, :valor_anterior, :valor_nuevo, :id_usuario, :descripcion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'tipo_evento' => $tipoEvento,
            'tabla_afectada' => $tablaAfectada,
            'registro_id' => $registroId,
            'campo_modificado' => $campoModificado,
            'valor_anterior' => $valorAnterior,
            'valor_nuevo' => $valorNuevo,
            'id_usuario' => $idUsuario,
            'descripcion' => $descripcion,
        ]);
    }
}
