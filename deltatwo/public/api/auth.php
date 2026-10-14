<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../../app/config/Conexion.php';

function respuesta(int $status, array $payload): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respuesta(405, ['mensaje' => 'Método no permitido.']);
}

$action = $_GET['action'] ?? '';
try {
    $db = (new Conexion())->conectar();
    if ($action === 'register') {
        $nombre = trim($_POST['first-name'] ?? '');
        $apellido = trim($_POST['last-name'] ?? '');
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $usuario = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if (!$nombre || !$apellido || !$email || !$usuario || strlen($password) < 8) {
            respuesta(422, ['mensaje' => 'Completá todos los campos y usá una contraseña de al menos 8 caracteres.']);
        }
        $db->beginTransaction();
        $stmt = $db->prepare("INSERT INTO usuarios (nombre, apellido, email, nombre_usuario, password_hash, rol) VALUES (:nombre, :apellido, :email, :usuario, :hash, 'CLIENTE')");
        $stmt->execute(['nombre' => $nombre, 'apellido' => $apellido, 'email' => $email, 'usuario' => $usuario, 'hash' => password_hash($password, PASSWORD_DEFAULT)]);
        $idUsuario = (int) $db->lastInsertId();
        $cliente = $db->prepare("INSERT INTO clientes (id_usuario, tipo_cliente, estado_validacion) VALUES (:id_usuario, 'PARTICULAR', 'PENDIENTE')");
        $cliente->execute(['id_usuario' => $idUsuario]);
        $db->commit();
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = $idUsuario;
        $_SESSION['nombre'] = $nombre;
        $_SESSION['rol'] = 'CLIENTE';
        respuesta(201, ['mensaje' => 'Cuenta creada.', 'redirect' => 'mi-cuenta.html']);
    }

    if ($action === 'login') {
        $identificador = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $db->prepare('SELECT id_usuario, nombre, rol, activo, password_hash FROM usuarios WHERE email = :email OR nombre_usuario = :usuario LIMIT 1');
        $stmt->execute(['email' => $identificador, 'usuario' => $identificador]);
        $usuario = $stmt->fetch();
        $valido = $usuario && (password_verify($password, $usuario['password_hash']) || hash('sha256', $password) === $usuario['password_hash']);
        if (!$valido || !$usuario['activo'] || $usuario['rol'] !== 'CLIENTE') {
            respuesta(401, ['mensaje' => 'No pudimos iniciar sesión. Revisá tus datos o registrate como cliente.']);
        }
        session_regenerate_id(true);
        $_SESSION['id_usuario'] = (int) $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre'];
        $_SESSION['rol'] = $usuario['rol'];
        respuesta(200, ['mensaje' => 'Sesión iniciada.', 'redirect' => 'mi-cuenta.html']);
    }

    if ($action === 'logout') {
        $_SESSION = [];
        session_destroy();
        respuesta(200, ['mensaje' => 'Sesión cerrada.']);
    }
    respuesta(400, ['mensaje' => 'Acción no válida.']);
} catch (PDOException $error) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    if ($error->getCode() === '23000') respuesta(409, ['mensaje' => 'Ese email o nombre de usuario ya está registrado.']);
    error_log($error->getMessage());
    respuesta(500, ['mensaje' => 'No pudimos completar la operación. Verificá la conexión con la base de datos.']);
}


