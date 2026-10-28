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
}
