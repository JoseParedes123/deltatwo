<?php

require_once __DIR__ . '/../config/Conexion.php';

class IntentoEntregaDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de intentos de entrega.
        $this->db = (new Conexion())->conectar();
    }

    // Todos los intentos de un envio, del primero al ultimo.
    public function obtenerPorEnvio(int $idEnvio): array
    {
        $sql = "SELECT intentos_entrega.*, CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS chofer
                FROM intentos_entrega
                INNER JOIN choferes ON intentos_entrega.id_chofer = choferes.id_chofer
                INNER JOIN usuarios ON choferes.id_usuario = usuarios.id_usuario
                WHERE intentos_entrega.id_envio = :id_envio
                ORDER BY intentos_entrega.numero_intento";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Cuantos intentos lleva un envio (para saber el numero del proximo).
    public function contarPorEnvio(int $idEnvio): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM intentos_entrega WHERE id_envio = :id_envio");
        $stmt->execute(['id_envio' => $idEnvio]);

        return (int) $stmt->fetchColumn();
    }

    // Registra un intento de entrega (EXITOSO o FALLIDO con su motivo).
    public function crear(int $idEnvio, int $numeroIntento, string $fecha, string $hora, int $idChofer, string $resultado, ?string $motivoFalla, ?string $receptorNombre, ?string $receptorDocumento, ?string $observaciones): bool
    {
        $sql = "INSERT INTO intentos_entrega (id_envio, numero_intento, fecha, hora, id_chofer, resultado, motivo_falla, receptor_nombre, receptor_documento, observaciones)
                VALUES (:id_envio, :numero_intento, :fecha, :hora, :id_chofer, :resultado, :motivo_falla, :receptor_nombre, :receptor_documento, :observaciones)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_envio' => $idEnvio,
            'numero_intento' => $numeroIntento,
            'fecha' => $fecha,
            'hora' => $hora,
            'id_chofer' => $idChofer,
            'resultado' => $resultado,
            'motivo_falla' => $motivoFalla,
            'receptor_nombre' => $receptorNombre,
            'receptor_documento' => $receptorDocumento,
            'observaciones' => $observaciones,
        ]);
    }
}
