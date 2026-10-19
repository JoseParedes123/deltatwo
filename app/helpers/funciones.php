<?php

// Funciones de sesión y acceso utilizadas por las páginas.
function iniciarSesion(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

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
    return $estados[$estado] ?? 'En proceso';
}
