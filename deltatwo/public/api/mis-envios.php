<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if (empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'CLIENTE') {
    http_response_code(401);
    echo json_encode(['mensaje' => 'Iniciá sesión para ver tus envíos.']);
    exit;
}
require_once __DIR__ . '/../../app/config/Conexion.php';
try {
    $db = (new Conexion())->conectar();
    $stmt = $db->prepare("SELECT e.codigo_envio, e.estado, e.fecha_solicitud, e.fecha_entrega_estimada,
            e.precio_final, destinatario.nombre AS destinatario, destinatario.direccion AS destino
        FROM envios e
        INNER JOIN clientes c ON c.id_cliente = e.id_cliente
        INNER JOIN contactos destinatario ON destinatario.id_contacto = e.id_destinatario
        WHERE c.id_usuario = :id_usuario AND e.activo = 1 ORDER BY e.fecha_solicitud DESC");
    $stmt->execute(['id_usuario' => $_SESSION['id_usuario']]);
    $zones = $db->query('SELECT id_zona, nombre FROM zonas WHERE activo = 1 ORDER BY nombre')->fetchAll();
    echo json_encode(['nombre' => $_SESSION['nombre'], 'envios' => $stmt->fetchAll(), 'zonas' => $zones], JSON_UNESCAPED_UNICODE);
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(['mensaje' => 'No pudimos cargar tus envíos.']);
}



