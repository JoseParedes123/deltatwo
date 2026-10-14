<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once __DIR__ . '/../app/config/Conexion.php';

function responder(int $codigo, array $datos): void
{
    http_response_code($codigo);
    echo json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$codigo = strtoupper(trim($_POST['codigo'] ?? ''));
if ($codigo === '' || !preg_match('/^[A-Z0-9-]{4,20}$/', $codigo)) {
    responder(400, ['mensaje' => 'Ingresá un código de envío válido.']);
}

try {
    $db = (new Conexion())->conectar();
    $consulta = $db->prepare("SELECT e.id_envio, e.codigo_envio, e.estado,
            remitente.direccion AS origen, destinatario.direccion AS destino,
            p.codigo AS codigo_paquete, p.descripcion AS descripcion_paquete, p.peso_kg,
            e.fecha_solicitud, e.fecha_entrega_estimada
        FROM envios e
        INNER JOIN contactos remitente ON remitente.id_contacto = e.id_remitente
        INNER JOIN contactos destinatario ON destinatario.id_contacto = e.id_destinatario
        INNER JOIN paquetes p ON p.id_paquete = e.id_paquete
        WHERE (e.codigo_envio = :codigo_envio OR p.codigo = :codigo_paquete)
          AND e.activo = 1 LIMIT 1");
    $consulta->execute(['codigo_envio' => $codigo, 'codigo_paquete' => $codigo]);
    $envio = $consulta->fetch();

    if (!$envio) {
        responder(404, ['mensaje' => 'No encontramos un envío con ese código. Revisá que esté bien escrito.']);
    }

    $movimientos = $db->prepare("SELECT sucursal, provincia, detalle, fecha_hora
        FROM movimientos_seguimiento WHERE id_envio = :id_envio ORDER BY fecha_hora, id_movimiento");
    $movimientos->execute(['id_envio' => $envio['id_envio']]);

    $estados = [
        'REGISTRADO' => 'Envío registrado', 'RECIBIDO_DEPOSITO' => 'Recibido en sucursal',
        'PREPARADO' => 'Preparado para salir', 'EN_TRANSITO' => 'En tránsito',
        'EN_REPARTO' => 'En camino a destino', 'ENTREGADO' => 'Entregado',
        'NO_ENTREGADO' => 'No se pudo entregar', 'DEVUELTO' => 'Devuelto al remitente',
        'EXTRAVIADO' => 'En revisión', 'CANCELADO' => 'Cancelado',
    ];
    $envio['estado_legible'] = $estados[$envio['estado']] ?? 'En proceso';
    $envio['movimientos'] = $movimientos->fetchAll();
    responder(200, ['envio' => $envio]);
} catch (Throwable $error) {
    error_log($error->getMessage());
    responder(500, ['mensaje' => 'No pudimos consultar el envío. Verificá que la base de datos esté importada y MySQL esté activo.']);
}

