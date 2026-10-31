<?php
require_once __DIR__ . '/../app/helpers/funciones.php';
iniciarSesion();
$idiomaSalida = idiomaActual();

// una visita normal a esta url nunca debe cerrar la sesión.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $parametrosCookie = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parametrosCookie['path'], $parametrosCookie['domain'], $parametrosCookie['secure'], $parametrosCookie['httponly']);
}
session_destroy();
header('Location: index.php?lang=' . urlencode($idiomaSalida));
exit;
