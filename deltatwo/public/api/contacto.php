<?php
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once __DIR__ . '/../../app/config/Conexion.php';
function contactoRespuesta(int $status, array $data): void { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST') contactoRespuesta(405, ['mensaje' => 'Método no permitido.']);
$nombre = trim($_POST['name'] ?? '');
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$codigo = strtoupper(trim($_POST['codigo'] ?? ''));
$asunto = trim($_POST['subject'] ?? '');
$mensaje = trim($_POST['message'] ?? '');
if (!$nombre || !$email || !$asunto || !$mensaje || strlen($mensaje) > 3000) contactoRespuesta(422, ['mensaje' => 'Completá tu nombre, email, motivo y mensaje.']);
try {
    $db = (new Conexion())->conectar();
    $idEnvio = null;
    if ($codigo !== '') {
        $lookup = $db->prepare('SELECT e.id_envio FROM envios e INNER JOIN paquetes p ON p.id_paquete = e.id_paquete WHERE (e.codigo_envio = :envio OR p.codigo = :paquete) AND e.activo = 1 LIMIT 1');
        $lookup->execute(['envio' => $codigo, 'paquete' => $codigo]);
        $idEnvio = $lookup->fetchColumn();
        if (!$idEnvio) contactoRespuesta(422, ['mensaje' => 'No encontramos ese código. Revisalo o dejá el campo vacío.']);
    }
    $idCliente = null;
    if (!empty($_SESSION['id_usuario'])) {
        $clienteStmt = $db->prepare('SELECT id_cliente FROM clientes WHERE id_usuario = :id_usuario');
        $clienteStmt->execute(['id_usuario' => $_SESSION['id_usuario']]);
        $idCliente = $clienteStmt->fetchColumn() ?: null;
    }
    $insert = $db->prepare('INSERT INTO consultas_web (id_cliente, id_envio, nombre, email, asunto, mensaje) VALUES (:cliente, :envio, :nombre, :email, :asunto, :mensaje)');
    $insert->execute(['cliente' => $idCliente, 'envio' => $idEnvio, 'nombre' => $nombre, 'email' => $email, 'asunto' => $asunto, 'mensaje' => $mensaje]);
    contactoRespuesta(201, ['mensaje' => 'Listo, recibimos tu consulta. El equipo de Logística Delta se pondrá en contacto.']);
} catch (Throwable $error) {
    error_log($error->getMessage());
    contactoRespuesta(500, ['mensaje' => 'No pudimos guardar tu consulta. Intentá nuevamente más tarde.']);
}
