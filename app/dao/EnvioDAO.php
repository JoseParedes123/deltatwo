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

    // Trae todos los envios activos con el cliente, el destinatario y el paquete.
    public function obtenerTodos(): array
    {
        $sql = "SELECT envios.*, paquetes.codigo AS codigo_paquete, contactos.nombre AS destinatario, zonas.nombre AS zona_destino
                FROM envios
                INNER JOIN paquetes ON envios.id_paquete = paquetes.id_paquete
                INNER JOIN contactos ON envios.id_destinatario = contactos.id_contacto
                INNER JOIN zonas ON envios.id_zona_destino = zonas.id_zona
                WHERE envios.activo = 1
                ORDER BY envios.fecha_solicitud DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un envio puntual para mostrar su detalle.
    public function obtenerPorId(int $idEnvio): ?array
    {
        $sql = "SELECT envios.*, paquetes.codigo AS codigo_paquete, paquetes.descripcion AS descripcion_paquete, paquetes.peso_kg,
                       remitente.nombre AS remitente, remitente.direccion AS origen,
                       destinatario.nombre AS destinatario, destinatario.direccion AS destino,
                       zonas.nombre AS zona_destino
                FROM envios
                INNER JOIN paquetes ON envios.id_paquete = paquetes.id_paquete
                INNER JOIN contactos remitente ON envios.id_remitente = remitente.id_contacto
                INNER JOIN contactos destinatario ON envios.id_destinatario = destinatario.id_contacto
                INNER JOIN zonas ON envios.id_zona_destino = zonas.id_zona
                WHERE envios.id_envio = :id_envio";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_envio' => $idEnvio]);
        $envio = $stmt->fetch();

        return $envio ?: null;
    }

    // Envios de un cliente: cada cliente solo ve los suyos (pantalla "Mis envios").
    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT envios.id_envio, envios.codigo_envio, envios.estado, envios.precio_final, envios.fecha_solicitud,
                       envios.fecha_entrega_estimada, contactos.nombre AS destinatario, zonas.nombre AS zona_destino
                FROM envios
                INNER JOIN contactos ON envios.id_destinatario = contactos.id_contacto
                INNER JOIN zonas ON envios.id_zona_destino = zonas.id_zona
                WHERE envios.id_cliente = :id_cliente AND envios.activo = 1
                ORDER BY envios.fecha_solicitud DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_cliente' => $idCliente]);

        return $stmt->fetchAll();
    }

    // Entregas de una ruta, en el orden sugerido de paradas (lo que ve el chofer).
    public function obtenerPorRuta(int $idRuta): array
    {
        $sql = "SELECT envios.id_envio, envios.codigo_envio, envios.estado, envios.orden_parada,
                       contactos.nombre AS destinatario, contactos.direccion, contactos.telefono,
                       paquetes.descripcion AS descripcion_paquete, paquetes.peso_kg, paquetes.es_fragil
                FROM envios
                INNER JOIN contactos ON envios.id_destinatario = contactos.id_contacto
                INNER JOIN paquetes ON envios.id_paquete = paquetes.id_paquete
                WHERE envios.id_ruta = :id_ruta AND envios.activo = 1
                ORDER BY envios.orden_parada";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ruta' => $idRuta]);

        return $stmt->fetchAll();
    }

    // Registra un envio nuevo (nace REGISTRADO) y devuelve su id (0 si fallo).
    public function crear(string $codigoEnvio, int $idCliente, int $idRemitente, int $idDestinatario, int $idPaquete, int $idZonaDestino, ?int $idTarifaAplicada, ?float $precioFinal, ?string $fechaEntregaEstimada, int $idUsuarioRegistro): int
    {
        $sql = "INSERT INTO envios (codigo_envio, id_cliente, id_remitente, id_destinatario, id_paquete, id_zona_destino,
                                    id_tarifa_aplicada, precio_final, fecha_entrega_estimada, id_usuario_registro)
                VALUES (:codigo_envio, :id_cliente, :id_remitente, :id_destinatario, :id_paquete, :id_zona_destino,
                        :id_tarifa_aplicada, :precio_final, :fecha_entrega_estimada, :id_usuario_registro)";

        $stmt = $this->db->prepare($sql);
        $guardado = $stmt->execute([
            'codigo_envio' => $codigoEnvio,
            'id_cliente' => $idCliente,
            'id_remitente' => $idRemitente,
            'id_destinatario' => $idDestinatario,
            'id_paquete' => $idPaquete,
            'id_zona_destino' => $idZonaDestino,
            'id_tarifa_aplicada' => $idTarifaAplicada,
            'precio_final' => $precioFinal,
            'fecha_entrega_estimada' => $fechaEntregaEstimada,
            'id_usuario_registro' => $idUsuarioRegistro,
        ]);

        return $guardado ? (int) $this->db->lastInsertId() : 0;
    }

    // Asigna el envio a una ruta con su orden de parada (o lo saca de la ruta con null).
    public function asignarRuta(int $idEnvio, ?int $idRuta, ?int $ordenParada): bool
    {
        $sql = "UPDATE envios SET id_ruta = :id_ruta, orden_parada = :orden_parada WHERE id_envio = :id_envio";
        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_ruta' => $idRuta,
            'orden_parada' => $ordenParada,
            'id_envio' => $idEnvio,
        ]);
    }

    // Cambia el estado del envio. El estado es un ENUM: la base solo acepta los estados definidos.
    public function cambiarEstado(int $idEnvio, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE envios SET estado = :estado WHERE id_envio = :id_envio");

        return $stmt->execute([
            'estado' => $estado,
            'id_envio' => $idEnvio,
        ]);
    }

    // No se borra: el envio queda inactivo y su historial se conserva.
    public function desactivar(int $idEnvio): bool
    {
        $stmt = $this->db->prepare("UPDATE envios SET activo = 0 WHERE id_envio = :id_envio");

        return $stmt->execute(['id_envio' => $idEnvio]);
    }
}
