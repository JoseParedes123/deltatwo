<?php

require_once __DIR__ . '/../config/Conexion.php';

class PaqueteDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de paquetes.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve todos los paquetes registrados.
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM paquetes ORDER BY fecha_registro DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un paquete puntual.
    public function obtenerPorId(int $idPaquete): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM paquetes WHERE id_paquete = :id_paquete");
        $stmt->execute(['id_paquete' => $idPaquete]);
        $paquete = $stmt->fetch();

        return $paquete ?: null;
    }

    // Busca un paquete por su codigo, ej: DEL-0001.
    public function obtenerPorCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM paquetes WHERE codigo = :codigo");
        $stmt->execute(['codigo' => $codigo]);
        $paquete = $stmt->fetch();

        return $paquete ?: null;
    }

    // Registra un paquete nuevo y devuelve su id (0 si fallo).
    public function crear(string $codigo, ?string $descripcion, float $pesoKg, ?float $largoCm, ?float $anchoCm, ?float $altoCm, int $cantidadBultos, string $tipoMercaderia, ?float $valorDeclarado, bool $esFragil): int
    {
        $sql = "INSERT INTO paquetes (codigo, descripcion, peso_kg, largo_cm, ancho_cm, alto_cm, cantidad_bultos, tipo_mercaderia, valor_declarado, es_fragil)
                VALUES (:codigo, :descripcion, :peso_kg, :largo_cm, :ancho_cm, :alto_cm, :cantidad_bultos, :tipo_mercaderia, :valor_declarado, :es_fragil)";

        $stmt = $this->db->prepare($sql);
        $guardado = $stmt->execute([
            'codigo' => $codigo,
            'descripcion' => $descripcion,
            'peso_kg' => $pesoKg,
            'largo_cm' => $largoCm,
            'ancho_cm' => $anchoCm,
            'alto_cm' => $altoCm,
            'cantidad_bultos' => $cantidadBultos,
            'tipo_mercaderia' => $tipoMercaderia,
            'valor_declarado' => $valorDeclarado,
            'es_fragil' => (int) $esFragil,
        ]);

        return $guardado ? (int) $this->db->lastInsertId() : 0;
    }

    // Modifica los datos de un paquete.
    public function actualizar(int $idPaquete, ?string $descripcion, float $pesoKg, ?float $largoCm, ?float $anchoCm, ?float $altoCm, int $cantidadBultos, string $tipoMercaderia, ?float $valorDeclarado, bool $esFragil): bool
    {
        $sql = "UPDATE paquetes
                SET descripcion = :descripcion, peso_kg = :peso_kg, largo_cm = :largo_cm, ancho_cm = :ancho_cm,
                    alto_cm = :alto_cm, cantidad_bultos = :cantidad_bultos, tipo_mercaderia = :tipo_mercaderia,
                    valor_declarado = :valor_declarado, es_fragil = :es_fragil
                WHERE id_paquete = :id_paquete";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'descripcion' => $descripcion,
            'peso_kg' => $pesoKg,
            'largo_cm' => $largoCm,
            'ancho_cm' => $anchoCm,
            'alto_cm' => $altoCm,
            'cantidad_bultos' => $cantidadBultos,
            'tipo_mercaderia' => $tipoMercaderia,
            'valor_declarado' => $valorDeclarado,
            'es_fragil' => (int) $esFragil,
            'id_paquete' => $idPaquete,
        ]);
    }
}
