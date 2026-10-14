<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../../app/config/Conexion.php';

function respuestaPanel(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function personalAutorizado(): bool
{
    return !empty($_SESSION['id_usuario']) && in_array($_SESSION['rol'] ?? '', ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'], true);
}

$action = $_GET['action'] ?? '';
try {
    $db = (new Conexion())->conectar();

    if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $identificador = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $db->prepare('SELECT id_usuario, nombre, rol, activo, password_hash FROM usuarios WHERE email = :email OR nombre_usuario = :usuario LIMIT 1');
        $stmt->execute(['email' => $identificador, 'usuario' => $identificador]);
        $usuario = $stmt->fetch();
        $rolesPermitidos = ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'];
        $valido = $usuario && (password_verify($password, $usuario['password_hash']) || hash('sha256', $password) === $usuario['password_hash']);
        if (!$valido || !$usuario['activo'] || !in_array($usuario['rol'], $rolesPermitidos, true)) {
            respuestaPanel(401, ['mensaje' => 'Credenciales incorrectas o cuenta sin permisos de personal.']);
        }
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        respuestaPanel(200, ['mensaje' => 'Sesión de personal iniciada.', 'nombre' => $usuario['nombre'], 'rol' => $usuario['rol']]);
    }

    if ($action === 'logout' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $_SESSION = [];
        session_destroy();
        respuestaPanel(200, ['mensaje' => 'Sesión cerrada.']);
    }

    if (!personalAutorizado()) {
        respuestaPanel(401, ['mensaje' => 'Iniciá sesión con una cuenta de personal.']);
    }

    if ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $db->query("SELECT e.codigo_envio, e.estado, e.fecha_solicitud, d.nombre AS destino,
                (SELECT CONCAT(m.sucursal, ' · ', m.provincia) FROM movimientos_seguimiento m WHERE m.id_envio = e.id_envio ORDER BY m.fecha_hora DESC, m.id_movimiento DESC LIMIT 1) AS ubicacion
            FROM envios e INNER JOIN contactos d ON d.id_contacto = e.id_destinatario
            WHERE e.activo = 1 ORDER BY e.fecha_solicitud DESC LIMIT 100");
        respuestaPanel(200, ['nombre' => $_SESSION['nombre'] ?? 'Personal', 'rol' => $_SESSION['rol'], 'envios' => $stmt->fetchAll()]);
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $codigo = strtoupper(trim($_POST['codigo'] ?? ''));
        $estado = $_POST['estado'] ?? '';
        $sucursal = trim($_POST['sucursal'] ?? '');
        $provincia = trim($_POST['provincia'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');
        $estados = ['REGISTRADO', 'RECIBIDO_DEPOSITO', 'PREPARADO', 'EN_TRANSITO', 'EN_REPARTO', 'ENTREGADO', 'NO_ENTREGADO', 'DEVUELTO', 'EXTRAVIADO', 'CANCELADO'];
        $provincias = ['Ciudad Autónoma de Buenos Aires', 'Buenos Aires', 'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa', 'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro', 'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero', 'Tierra del Fuego', 'Tucumán'];
        if (!$codigo || !in_array($estado, $estados, true) || !$sucursal || !in_array($provincia, $provincias, true) || !$detalle || mb_strlen($sucursal) > 100 || mb_strlen($detalle) > 255) {
            respuestaPanel(422, ['mensaje' => 'Revisá el código, estado, sucursal, provincia y detalle.']);
        }
        $db->beginTransaction();
        $stmt = $db->prepare('SELECT id_envio FROM envios WHERE codigo_envio = :codigo AND activo = 1 LIMIT 1 FOR UPDATE');
        $stmt->execute(['codigo' => $codigo]);
        $envio = $stmt->fetch();
        if (!$envio) {
            $db->rollBack();
            respuestaPanel(404, ['mensaje' => 'No encontramos un envío activo con ese código.']);
        }
        $update = $db->prepare('UPDATE envios SET estado = :estado WHERE id_envio = :id');
        $update->execute(['estado' => $estado, 'id' => $envio['id_envio']]);
        $movement = $db->prepare('INSERT INTO movimientos_seguimiento (id_envio, sucursal, provincia, detalle) VALUES (:id, :sucursal, :provincia, :detalle)');
        $movement->execute(['id' => $envio['id_envio'], 'sucursal' => $sucursal, 'provincia' => $provincia, 'detalle' => $detalle]);
        $db->commit();
        respuestaPanel(200, ['mensaje' => 'Estado y recorrido actualizados. El cliente ya puede ver el movimiento.']);
    }

    respuestaPanel(400, ['mensaje' => 'Acción no válida.']);
} catch (Throwable $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    error_log($error->getMessage());
    respuestaPanel(500, ['mensaje' => 'No pudimos completar la operación. Verificá la conexión con MySQL.']);
}
