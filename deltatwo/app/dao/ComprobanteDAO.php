<?php

require_once __DIR__ . '/../config/Conexion.php';

class ComprobanteDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de comprobantes.
        $this->db = (new Conexion())->conectar();
    }

    // Busca un comprobante puntual.
    public function obtenerPorId(int $idComprobante): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM comprobantes WHERE id_comprobante = :id_comprobante");
        $stmt->execute(['id_comprobante' => $idComprobante]);
        $comprobante = $stmt->fetch();

        return $comprobante ?: null;
    }

    // Comprobantes de un envio.
    public function obtenerPorEnvio(int $idEnvio): array
    {
        $sql = "SELECT * FROM comprobantes WHERE id_envio = :id_envio ORDER BY fecha_emision DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_envio' => $idEnvio]);

        return $stmt->fetchAll();
    }

    // Comprobantes de los envios de un cliente (cada cliente solo ve los suyos).
    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT comprobantes.*, envios.codigo_envio
                FROM comprobantes
                INNER JOIN envios ON comprobantes.id_envio = envios.id_envio
                WHERE envios.id_cliente = :id_cliente
                ORDER BY comprobantes.fecha_emision DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_cliente' => $idCliente]);

        return $stmt->fetchAll();
    }

    // Registra un comprobante de un envio.
    public function crear(int $idEnvio, string $numeroComprobante, string $tipo, ?string $rutaArchivo, int $idUsuarioEmisor): bool
    {
        $sql = "INSERT INTO comprobantes (id_envio, numero_comprobante, tipo, ruta_archivo, id_usuario_emisor)
                VALUES (:id_envio, :numero_comprobante, :tipo, :ruta_archivo, :id_usuario_emisor)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_envio' => $idEnvio,
            'numero_comprobante' => $numeroComprobante,
            'tipo' => $tipo,
            'ruta_archivo' => $rutaArchivo,
            'id_usuario_emisor' => $idUsuarioEmisor,
        ]);
    }
}
