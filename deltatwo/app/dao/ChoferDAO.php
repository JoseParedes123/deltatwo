<?php

require_once __DIR__ . '/../config/Conexion.php';

class ChoferDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de choferes.
        $this->db = (new Conexion())->conectar();
    }

    // Trae todos los choferes junto con los datos de su usuario.
    public function obtenerTodos(): array
    {
        $sql = "SELECT choferes.*, usuarios.nombre, usuarios.apellido, usuarios.dni
                FROM choferes
                INNER JOIN usuarios ON choferes.id_usuario = usuarios.id_usuario
                ORDER BY usuarios.apellido, usuarios.nombre";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un chofer puntual.
    public function obtenerPorId(int $idChofer): ?array
    {
        $sql = "SELECT choferes.*, usuarios.nombre, usuarios.apellido, usuarios.dni
                FROM choferes
                INNER JOIN usuarios ON choferes.id_usuario = usuarios.id_usuario
                WHERE choferes.id_chofer = :id_chofer";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_chofer' => $idChofer]);
        $chofer = $stmt->fetch();

        return $chofer ?: null;
    }

    // Busca el chofer que corresponde al usuario logueado.
    public function obtenerPorUsuario(int $idUsuario): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM choferes WHERE id_usuario = :id_usuario");
        $stmt->execute(['id_usuario' => $idUsuario]);
        $chofer = $stmt->fetch();

        return $chofer ?: null;
    }

    // Carga un chofer nuevo (el usuario ya tiene que existir).
    public function crear(int $idUsuario, string $licencia, string $categoriaLicencia, string $fechaVencimientoLicencia): bool
    {
        $sql = "INSERT INTO choferes (id_usuario, licencia, categoria_licencia, fecha_vencimiento_licencia)
                VALUES (:id_usuario, :licencia, :categoria_licencia, :fecha_vencimiento_licencia)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_usuario' => $idUsuario,
            'licencia' => $licencia,
            'categoria_licencia' => $categoriaLicencia,
            'fecha_vencimiento_licencia' => $fechaVencimientoLicencia,
        ]);
    }

    // Modifica los datos de la licencia.
    public function actualizar(int $idChofer, string $licencia, string $categoriaLicencia, string $fechaVencimientoLicencia): bool
    {
        $sql = "UPDATE choferes
                SET licencia = :licencia, categoria_licencia = :categoria_licencia,
                    fecha_vencimiento_licencia = :fecha_vencimiento_licencia
                WHERE id_chofer = :id_chofer";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'licencia' => $licencia,
            'categoria_licencia' => $categoriaLicencia,
            'fecha_vencimiento_licencia' => $fechaVencimientoLicencia,
            'id_chofer' => $idChofer,
        ]);
    }

    // Cambia el estado (ACTIVO, INACTIVO, SUSPENDIDO). El chofer no se borra.
    public function cambiarEstado(int $idChofer, string $estado): bool
    {
        $stmt = $this->db->prepare("UPDATE choferes SET estado = :estado WHERE id_chofer = :id_chofer");

        return $stmt->execute([
            'estado' => $estado,
            'id_chofer' => $idChofer,
        ]);
    }
}
