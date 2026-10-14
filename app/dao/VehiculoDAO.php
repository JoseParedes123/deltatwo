<?php

require_once __DIR__ . '/../config/Conexion.php';

class VehiculoDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de vehiculos.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve todos los vehiculos.
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM vehiculos ORDER BY patente";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un vehiculo puntual.
    public function obtenerPorId(int $idVehiculo): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM vehiculos WHERE id_vehiculo = :id_vehiculo");
        $stmt->execute(['id_vehiculo' => $idVehiculo]);
        $vehiculo = $stmt->fetch();

        return $vehiculo ?: null;
    }

    // Carga un vehiculo nuevo desde administracion.
    public function crear(string $patente, string $tipo, ?string $marca, ?string $modelo, ?int $anio, float $capacidadPesoKg, float $capacidadVolumenM3, ?int $idZonaRestringida, ?string $motivoRestriccion): bool
    {
        $sql = "INSERT INTO vehiculos (patente, tipo, marca, modelo, anio, capacidad_peso_kg, capacidad_volumen_m3, id_zona_restringida, motivo_restriccion)
                VALUES (:patente, :tipo, :marca, :modelo, :anio, :capacidad_peso_kg, :capacidad_volumen_m3, :id_zona_restringida, :motivo_restriccion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'patente' => $patente,
            'tipo' => $tipo,
            'marca' => $marca,
            'modelo' => $modelo,
            'anio' => $anio,
            'capacidad_peso_kg' => $capacidadPesoKg,
            'capacidad_volumen_m3' => $capacidadVolumenM3,
            'id_zona_restringida' => $idZonaRestringida,
            'motivo_restriccion' => $motivoRestriccion,
        ]);
    }

    // Modifica los datos de un vehiculo.
    public function actualizar(int $idVehiculo, string $patente, string $tipo, ?string $marca, ?string $modelo, ?int $anio, float $capacidadPesoKg, float $capacidadVolumenM3, ?int $idZonaRestringida, ?string $motivoRestriccion): bool
    {
        $sql = "UPDATE vehiculos
                SET patente = :patente, tipo = :tipo, marca = :marca, modelo = :modelo, anio = :anio,
                    capacidad_peso_kg = :capacidad_peso_kg, capacidad_volumen_m3 = :capacidad_volumen_m3,
                    id_zona_restringida = :id_zona_restringida, motivo_restriccion = :motivo_restriccion
                WHERE id_vehiculo = :id_vehiculo";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'patente' => $patente,
            'tipo' => $tipo,
            'marca' => $marca,
            'modelo' => $modelo,
            'anio' => $anio,
            'capacidad_peso_kg' => $capacidadPesoKg,
            'capacidad_volumen_m3' => $capacidadVolumenM3,
            'id_zona_restringida' => $idZonaRestringida,
            'motivo_restriccion' => $motivoRestriccion,
            'id_vehiculo' => $idVehiculo,
        ]);
    }

    // Cambia el estado (DISPONIBLE, EN_RUTA, MANTENIMIENTO, INACTIVO). El vehiculo no se borra.
    public function cambiarEstado(int $idVehiculo, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE vehiculos SET estado = :estado WHERE id_vehiculo = :id_vehiculo");

        return $stmt->execute([
            'estado' => $estado,
            'id_vehiculo' => $idVehiculo,
        ]);
    }
}
