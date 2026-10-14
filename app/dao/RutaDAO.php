<?php

require_once __DIR__ . '/../config/Conexion.php';

class RutaDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de rutas.
        $this->db = (new Conexion())->conectar();
    }

    // Trae todas las rutas junto con la patente del vehiculo y el nombre del chofer.
    public function obtenerTodos(): array
    {
        $sql = "SELECT rutas.*, vehiculos.patente, CONCAT(usuarios.nombre, ' ', usuarios.apellido) AS chofer
                FROM rutas
                INNER JOIN vehiculos ON rutas.id_vehiculo = vehiculos.id_vehiculo
                INNER JOIN choferes ON rutas.id_chofer = choferes.id_chofer
                INNER JOIN usuarios ON choferes.id_usuario = usuarios.id_usuario
                ORDER BY rutas.fecha DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca una ruta puntual.
    public function obtenerPorId(int $idRuta): ?array
    {
        $sql = "SELECT rutas.*, vehiculos.patente, vehiculos.capacidad_peso_kg, vehiculos.capacidad_volumen_m3
                FROM rutas
                INNER JOIN vehiculos ON rutas.id_vehiculo = vehiculos.id_vehiculo
                WHERE rutas.id_ruta = :id_ruta";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_ruta' => $idRuta]);
        $ruta = $stmt->fetch();

        return $ruta ?: null;
    }

    // Rutas de un chofer, para que vea las suyas.
    public function obtenerPorChofer(int $idChofer): array
    {
        $sql = "SELECT rutas.*, vehiculos.patente
                FROM rutas
                INNER JOIN vehiculos ON rutas.id_vehiculo = vehiculos.id_vehiculo
                WHERE rutas.id_chofer = :id_chofer
                ORDER BY rutas.fecha DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_chofer' => $idChofer]);

        return $stmt->fetchAll();
    }

    // Arma una ruta nueva y devuelve su id (0 si fallo). La organiza el responsable de logistica.
    public function crear(string $fecha, string $origen, string $destino, ?string $recorrido, ?string $observaciones, int $idVehiculo, int $idChofer, ?int $idZona): int
    {
        $sql = "INSERT INTO rutas (fecha, origen, destino, recorrido, observaciones, id_vehiculo, id_chofer, id_zona)
                VALUES (:fecha, :origen, :destino, :recorrido, :observaciones, :id_vehiculo, :id_chofer, :id_zona)";

        $stmt = $this->db->prepare($sql);
        $guardado = $stmt->execute([
            'fecha' => $fecha,
            'origen' => $origen,
            'destino' => $destino,
            'recorrido' => $recorrido,
            'observaciones' => $observaciones,
            'id_vehiculo' => $idVehiculo,
            'id_chofer' => $idChofer,
            'id_zona' => $idZona,
        ]);

        return $guardado ? (int) $this->db->lastInsertId() : 0;
    }

    // Modifica los datos de una ruta.
    public function actualizar(int $idRuta, string $fecha, string $origen, string $destino, ?string $recorrido, ?string $observaciones, int $idVehiculo, int $idChofer, ?int $idZona): bool
    {
        $sql = "UPDATE rutas
                SET fecha = :fecha, origen = :origen, destino = :destino, recorrido = :recorrido,
                    observaciones = :observaciones, id_vehiculo = :id_vehiculo, id_chofer = :id_chofer, id_zona = :id_zona
                WHERE id_ruta = :id_ruta";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'fecha' => $fecha,
            'origen' => $origen,
            'destino' => $destino,
            'recorrido' => $recorrido,
            'observaciones' => $observaciones,
            'id_vehiculo' => $idVehiculo,
            'id_chofer' => $idChofer,
            'id_zona' => $idZona,
            'id_ruta' => $idRuta,
        ]);
    }

    // Cambia el estado (PLANIFICADA, EN_CURSO, FINALIZADA, CANCELADA). La ruta no se borra.
    public function cambiarEstado(int $idRuta, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE rutas SET estado = :estado WHERE id_ruta = :id_ruta");

        return $stmt->execute([
            'estado' => $estado,
            'id_ruta' => $idRuta,
        ]);
    }
}
