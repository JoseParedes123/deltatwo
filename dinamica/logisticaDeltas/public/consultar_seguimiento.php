<?php

require_once __DIR__ . '/../app/dao/EnvioDAO.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'mensaje' => 'Metodo no permitido.']);
    exit;
}

$codigo = trim((string) ($_POST['codigo'] ?? ''));

if ($codigo === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'mensaje' => 'Ingresa el codigo de tu envio.']);
    exit;
}

try {
    $envioDAO = new EnvioDAO();
    $envio = $envioDAO->obtenerPorCodigo(strtoupper($codigo));

    if ($envio === null) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'mensaje' => 'No encontramos un envio activo con ese codigo.']);
        exit;
    }

    echo json_encode([
        'ok' => true,
        'envio' => [
            'id' => $envio->getIdEnvio(),
            'codigo' => $envio->getCodigoEnvio(),
            'estado' => $envio->getEstado(),
            'estado_legible' => $envio->getEstadoLegible(),
            'origen' => $envio->getOrigen(),
            'destino' => $envio->getDestino(),
            'descripcion_paquete' => $envio->getDescripcionPaquete(),
            'peso_kg' => $envio->getPesoKg(),
            'fecha_solicitud' => $envio->getFechaSolicitud(),
            'fecha_entrega_estimada' => $envio->getFechaEntregaEstimada(),
        ],
    ], JSON_THROW_ON_ERROR);
} catch (Throwable $error) {
    error_log($error->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'mensaje' => 'No se pudo consultar el envio.']);
}