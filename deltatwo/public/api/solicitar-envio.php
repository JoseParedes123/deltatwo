<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['id_usuario']) || ($_SESSION['rol'] ?? '') !== 'CLIENTE') {
    http_response_code(401);
    echo json_encode(['mensaje' => 'Iniciá sesión como cliente para solicitar un envío.']);
    exit;
}
require_once __DIR__ . '/../../app/config/Conexion.php';
function enviar(int $status, array $data): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
$remitente = trim($_POST['remitente'] ?? '');
$direccionOrigen = trim($_POST['origen'] ?? '');
$destinatario = trim($_POST['destinatario'] ?? '');
$direccionDestino = trim($_POST['destino'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$peso = filter_var($_POST['peso'] ?? null, FILTER_VALIDATE_FLOAT);
$idZona = filter_var($_POST['zona'] ?? null, FILTER_VALIDATE_INT);
$fragil = isset($_POST['fragil']) ? 1 : 0;
if (!$remitente || !$direccionOrigen || !$destinatario || !$direccionDestino || !$descripcion || $peso === false || $peso <= 0 || !$idZona) {
    enviar(422, ['mensaje' => 'Completá los datos del envío y verificá el peso.']);
}
try {
    $db = (new Conexion())->conectar();
    $db->beginTransaction();
    $zone = $db->prepare('SELECT id_zona, nombre FROM zonas WHERE id_zona = :id AND activo = 1');
    $zone->execute(['id' => $idZona]);
    $zona = $zone->fetch();
    if (!$zona) { $db->rollBack(); enviar(422, ['mensaje' => 'Elegí una zona de entrega disponible.']); }
    $clienteStmt = $db->prepare('SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario');
    $clienteStmt->execute(['id_usuario' => $_SESSION['id_usuario']]);
    $cliente = $clienteStmt->fetch();
    if (!$cliente) { $db->rollBack(); enviar(403, ['mensaje' => 'La cuenta no tiene un perfil de cliente asociado.']); }

    $tarifaStmt = $db->prepare("SELECT id_tarifa, precio_base + (precio_por_kg * :peso) + (recargo_fragil * :fragil) AS precio
        FROM tarifas WHERE id_zona = :zona AND tipo_envio = 'ESTANDAR' AND activo = 1
          AND vigente_desde <= CURDATE() AND (vigente_hasta IS NULL OR vigente_hasta >= CURDATE())
        ORDER BY vigente_desde DESC LIMIT 1");
    $tarifaStmt->execute(['peso' => $peso, 'fragil' => $fragil, 'zona' => $idZona]);
    $tarifa = $tarifaStmt->fetch();

    $codigoPaquete = 'DEL-' . strtoupper(bin2hex(random_bytes(4)));
    $paqueteStmt = $db->prepare('INSERT INTO paquetes (codigo, descripcion, peso_kg, cantidad_bultos, tipo_mercaderia, es_fragil) VALUES (:codigo, :descripcion, :peso, 1, :tipo, :fragil)');
    $paqueteStmt->execute(['codigo' => $codigoPaquete, 'descripcion' => $descripcion, 'peso' => $peso, 'tipo' => 'Mercadería general', 'fragil' => $fragil]);
    $idPaquete = (int) $db->lastInsertId();

    $contactStmt = $db->prepare('INSERT INTO contactos (tipo, nombre, telefono, direccion, id_zona, id_cliente) VALUES (:tipo, :nombre, :telefono, :direccion, :zona, :cliente)');
    $contactStmt->execute(['tipo' => 'REMITENTE', 'nombre' => $remitente, 'telefono' => $telefono ?: null, 'direccion' => $direccionOrigen, 'zona' => null, 'cliente' => $cliente['id_cliente']]);
    $idRemitente = (int) $db->lastInsertId();
    $contactStmt->execute(['tipo' => 'DESTINATARIO', 'nombre' => $destinatario, 'telefono' => $telefono ?: null, 'direccion' => $direccionDestino, 'zona' => $idZona, 'cliente' => null]);
    $idDestinatario = (int) $db->lastInsertId();

    $codigoEnvio = 'ENV-' . strtoupper(bin2hex(random_bytes(4)));
    $envioStmt = $db->prepare('INSERT INTO envios (codigo_envio, id_cliente, id_remitente, id_destinatario, id_paquete, id_zona_destino, id_tarifa_aplicada, precio_final, fecha_entrega_estimada, id_usuario_registro) VALUES (:codigo, :cliente, :remitente, :destinatario, :paquete, :zona, :tarifa, :precio, DATE_ADD(CURDATE(), INTERVAL 3 DAY), :usuario)');
    $envioStmt->execute(['codigo' => $codigoEnvio, 'cliente' => $cliente['id_cliente'], 'remitente' => $idRemitente, 'destinatario' => $idDestinatario, 'paquete' => $idPaquete, 'zona' => $idZona, 'tarifa' => $tarifa['id_tarifa'] ?? null, 'precio' => $tarifa['precio'] ?? null, 'usuario' => $_SESSION['id_usuario']]);
    $idEnvio = (int) $db->lastInsertId();
    $movement = $db->prepare("INSERT INTO movimientos_seguimiento (id_envio, sucursal, provincia, detalle) VALUES (:id, 'Sucursal Logística Delta', 'Argentina', 'Solicitud recibida. El paquete espera su recepción en sucursal.')");
    $movement->execute(['id' => $idEnvio]);
    $db->commit();
    enviar(201, ['mensaje' => 'Solicitud registrada.', 'codigo' => $codigoEnvio, 'codigo_paquete' => $codigoPaquete, 'precio' => $tarifa['precio'] ?? null]);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log($error->getMessage());
    enviar(500, ['mensaje' => 'No pudimos registrar el envío. Verificá la base de datos e intentá de nuevo.']);
}
