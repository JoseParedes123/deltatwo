<?php

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../models/Envio.php';

class EnvioDAO
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new Conexion())->conectar();
    }

    public function obtenerPorCodigo(string $codigo): ?Envio
    {
        $sql = 'SELECT e.id_envio, e.codigo_envio, e.estado,
                       remitente.direccion AS origen,
                       destinatario.direccion AS destino,
                       paquete.descripcion AS descripcion_paquete,
                       paquete.peso_kg,
                       e.fecha_solicitud, e.fecha_entrega_estimada
                FROM envios e
                INNER JOIN contactos remitente ON remitente.id_contacto = e.id_remitente
                INNER JOIN contactos destinatario ON destinatario.id_contacto = e.id_destinatario
                INNER JOIN paquetes paquete ON paquete.id_paquete = e.id_paquete
                WHERE e.codigo_envio = :codigo AND e.activo = 1
                LIMIT 1';

        $consulta = $this->db->prepare($sql);
        $consulta->execute(['codigo' => $codigo]);
        $fila = $consulta->fetch();

        if (!$fila) {
            return null;
        }

        return new Envio(
            (int) $fila['id_envio'],
            $fila['codigo_envio'],
            $fila['estado'],
            $fila['origen'],
            $fila['destino'],
            $fila['descripcion_paquete'],
            $fila['peso_kg'],
            $fila['fecha_solicitud'],
            $fila['fecha_entrega_estimada']
        );
    }
}