<?php

// Funciones de sesión y acceso utilizadas por las páginas.
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Mantiene el idioma elegido en la sesion y prepara los textos de la interfaz.
function idiomaActual(): string
{
    iniciarSesion();
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['es', 'en'], true)) {
        $_SESSION['idioma'] = $_GET['lang'];
    }
    return $_SESSION['idioma'] ?? 'es';
}

function urlIdioma(string $idioma): string
{
    $parametros = $_GET;
    $parametros['lang'] = $idioma;
    return '?' . http_build_query($parametros);
}

function t(string $texto): string
{
    static $idioma = null;
    static $traduccionesEn = null;

    // El idioma de la solicitud no cambia durante la carga de una página.
    $idioma ??= idiomaActual();
    if ($idioma !== 'en') {
        return $texto;
    }

    $traduccionesEn ??= require __DIR__ . '/../lang/en.php';
    // Si falta una traducción, muestra el texto original en vez de dejarlo vacío.
    return $traduccionesEn[$texto] ?? $texto;
}

// Reune los datos basicos de la sesion y protege las pantallas por rol.
function usuarioActual(): ?array
{
    iniciarSesion();
    return empty($_SESSION['id_usuario']) ? null : [
        'id_usuario' => (int) $_SESSION['id_usuario'],
        'nombre' => $_SESSION['nombre'] ?? 'Usuario',
        'rol' => $_SESSION['rol'] ?? '',
    ];
}

function limpiar($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function protegerCliente(): void
{
    $usuario = usuarioActual();
    if (!$usuario || $usuario['rol'] !== 'CLIENTE') {
        header('Location: login.php');
        exit;
    }
}

function protegerPersonal(): void
{
    $usuario = usuarioActual();
    $roles = ['ADMINISTRADOR', 'OPERADOR_DEPOSITO', 'ADMINISTRACION'];
    if (!$usuario || !in_array($usuario['rol'], $roles, true)) {
        header('Location: panel-admin.php');
        exit;
    }
}

// Recupera mensajes de una sola vista y presenta estados legibles al cliente.
function mensajeFlasheado(): ?string
{
    iniciarSesion();
    $mensaje = $_SESSION['mensaje'] ?? null;
    unset($_SESSION['mensaje']);
    return $mensaje;
}

function estadoLegible(string $estado): string
{
    $estados = [
        'REGISTRADO' => 'Envío registrado', 'RECIBIDO_DEPOSITO' => 'Recibido en sucursal',
        'PREPARADO' => 'Preparado para salir', 'EN_TRANSITO' => 'En tránsito',
        'EN_REPARTO' => 'En camino a destino', 'ENTREGADO' => 'Entregado',
        'NO_ENTREGADO' => 'No se pudo entregar', 'DEVUELTO' => 'Devuelto al remitente',
        'EXTRAVIADO' => 'En revisión', 'CANCELADO' => 'Cancelado',
    ];
    return t($estados[$estado] ?? 'En proceso');
}
