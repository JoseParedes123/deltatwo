<?php

require_once __DIR__ . '/../config/Conexion.php';

// guarda las consultas que llegan desde el formulario publico.
class ConsultaWebDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Guarda un mensaje enviado desde el formulario de contacto.
    public function crear(?int $idCliente, ?int $idEnvio, string $nombre, string $email, string $asunto, string $mensaje): bool
    {
        $sql = "INSERT INTO consultas_web (id_cliente, id_envio, nombre, email, asunto, mensaje)
                VALUES (:id_cliente, :id_envio, :nombre, :email, :asunto, :mensaje)";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_cliente' => $idCliente,
            'id_envio' => $idEnvio,
            'nombre' => $nombre,
            'email' => $email,
            'asunto' => $asunto,
            'mensaje' => $mensaje,
        ]);
    }

    /** Devuelve consultas para la bandeja interna, priorizando las pendientes. */
    public function obtenerBandeja(string $filtro = 'TODAS'): array
    {
        $sql = "SELECT c.*, u.nombre AS nombre_responsable, u.apellido AS apellido_responsable
                FROM consultas_web c
                LEFT JOIN usuarios u ON u.id_usuario = c.id_usuario_respuesta";
        if ($filtro !== 'TODAS') {
            $sql .= " WHERE c.estado = :estado";
        }
        $sql .= " ORDER BY FIELD(c.estado, 'PENDIENTE', 'RESPUESTA_PREPARADA', 'RESPONDIDA'), c.id_consulta DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($filtro === 'TODAS' ? [] : ['estado' => $filtro]);
        return $stmt->fetchAll();
    }

    public function obtenerPorId(int $idConsulta): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM consultas_web WHERE id_consulta = :id_consulta");
        $stmt->execute(['id_consulta' => $idConsulta]);
        $consulta = $stmt->fetch();
        return $consulta ?: null;
    }

    /** Guarda el borrador de respuesta sin marcarlo como enviado. */
    public function prepararRespuesta(int $idConsulta, int $idUsuario, string $respuesta): bool
    {
        $stmt = $this->db->prepare("UPDATE consultas_web
            SET respuesta = :respuesta, estado = 'RESPUESTA_PREPARADA',
                id_usuario_respuesta = :id_usuario, fecha_respuesta = CURRENT_TIMESTAMP
            WHERE id_consulta = :id_consulta AND estado <> 'RESPONDIDA'");
        return $stmt->execute([
            'respuesta' => $respuesta,
            'id_usuario' => $idUsuario,
            'id_consulta' => $idConsulta,
        ]);
    }

    /** Se usa después de que el trabajador envía el correo desde su cliente de email. */
    public function marcarRespondida(int $idConsulta): bool
    {
        $stmt = $this->db->prepare("UPDATE consultas_web
            SET estado = 'RESPONDIDA'
            WHERE id_consulta = :id_consulta AND estado = 'RESPUESTA_PREPARADA'");
        return $stmt->execute(['id_consulta' => $idConsulta]) && $stmt->rowCount() === 1;
    }
}
