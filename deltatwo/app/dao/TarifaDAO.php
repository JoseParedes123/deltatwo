<?php

require_once __DIR__ . '/../config/Conexion.php';

class TarifaDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de tarifas.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve las tarifas activas junto con el nombre de su zona.
    public function obtenerTodos(): array
    {
        $sql = "SELECT tarifas.*, zonas.nombre AS zona
                FROM tarifas
                INNER JOIN zonas ON tarifas.id_zona = zonas.id_zona
                WHERE tarifas.activo = 1
                ORDER BY zonas.nombre, tarifas.tipo_envio";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca una tarifa puntual.
    public function obtenerPorId(int $idTarifa): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM tarifas WHERE id_tarifa = :id_tarifa");
        $stmt->execute(['id_tarifa' => $idTarifa]);
        $tarifa = $stmt->fetch();

        return $tarifa ?: null;
    }

    // Busca la tarifa vigente para una zona y un tipo de envio.
    public function obtenerVigente(int $idZona, string $tipoEnvio): ?array
    {
        $sql = "SELECT * FROM tarifas
                WHERE id_zona = :id_zona AND tipo_envio = :tipo_envio AND activo = 1
                  AND vigente_desde <= CURDATE()
                  AND (vigente_hasta IS NULL OR vigente_hasta >= CURDATE())
                ORDER BY vigente_desde DESC
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'id_zona' => $idZona,
            'tipo_envio' => $tipoEnvio,
        ]);
        $tarifa = $stmt->fetch();

        return $tarifa ?: null;
    }

    // Carga una tarifa nueva desde administracion.
    public function crear(string $nombre, int $idZona, string $tipoEnvio, float $precioBase, float $precioPorKg, float $precioPorM3, float $recargoFragil, string $vigenteDesde, ?string $vigenteHasta): bool
    {
        $sql = "INSERT INTO tarifas (nombre, id_zona, tipo_envio, precio_base, precio_por_kg, precio_por_m3, recargo_fragil, vigente_desde, vigente_hasta)
                VALUES (:nombre, :id_zona, :tipo_envio, :precio_base, :precio_por_kg, :precio_por_m3, :recargo_fragil, :vigente_desde, :vigente_hasta)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'id_zona' => $idZona,
            'tipo_envio' => $tipoEnvio,
            'precio_base' => $precioBase,
            'precio_por_kg' => $precioPorKg,
            'precio_por_m3' => $precioPorM3,
            'recargo_fragil' => $recargoFragil,
            'vigente_desde' => $vigenteDesde,
            'vigente_hasta' => $vigenteHasta,
        ]);
    }

    // Modifica los datos de una tarifa.
    public function actualizar(int $idTarifa, string $nombre, int $idZona, string $tipoEnvio, float $precioBase, float $precioPorKg, float $precioPorM3, float $recargoFragil, string $vigenteDesde, ?string $vigenteHasta): bool
    {
        $sql = "UPDATE tarifas
                SET nombre = :nombre, id_zona = :id_zona, tipo_envio = :tipo_envio, precio_base = :precio_base,
                    precio_por_kg = :precio_por_kg, precio_por_m3 = :precio_por_m3, recargo_fragil = :recargo_fragil,
                    vigente_desde = :vigente_desde, vigente_hasta = :vigente_hasta
                WHERE id_tarifa = :id_tarifa";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'id_zona' => $idZona,
            'tipo_envio' => $tipoEnvio,
            'precio_base' => $precioBase,
            'precio_por_kg' => $precioPorKg,
            'precio_por_m3' => $precioPorM3,
            'recargo_fragil' => $recargoFragil,
            'vigente_desde' => $vigenteDesde,
            'vigente_hasta' => $vigenteHasta,
            'id_tarifa' => $idTarifa,
        ]);
    }

    // No se borra: los envios ya cotizados siguen apuntando a la tarifa.
    public function desactivar(int $idTarifa): bool
    {
        $stmt = $this->db->prepare("UPDATE tarifas SET activo = 0 WHERE id_tarifa = :id_tarifa");

        return $stmt->execute(['id_tarifa' => $idTarifa]);
    }
}
