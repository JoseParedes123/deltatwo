<?php

require_once __DIR__ . '/../config/Conexion.php';

class DepositoDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de depositos.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve los depositos activos.
    public function obtenerTodos(): array
    {
        $sql = "SELECT * FROM depositos WHERE activo = 1 ORDER BY nombre";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un deposito puntual.
    public function obtenerPorId(int $idDeposito): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM depositos WHERE id_deposito = :id_deposito");
        $stmt->execute(['id_deposito' => $idDeposito]);
        $deposito = $stmt->fetch();

        return $deposito ?: null;
    }

    // Carga un deposito nuevo desde administracion.
    public function crear(string $nombre, string $direccion): bool
    {
        $sql = "INSERT INTO depositos (nombre, direccion)
                VALUES (:nombre, :direccion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'direccion' => $direccion,
        ]);
    }

    // Modifica los datos de un deposito.
    public function actualizar(int $idDeposito, string $nombre, string $direccion): bool
    {
        $sql = "UPDATE depositos
                SET nombre = :nombre, direccion = :direccion
                WHERE id_deposito = :id_deposito";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'direccion' => $direccion,
            'id_deposito' => $idDeposito,
        ]);
    }

    // No se borra: queda inactivo para conservar la informacion historica.
    public function desactivar(int $idDeposito): bool
    {
        $stmt = $this->db->prepare("UPDATE depositos SET activo = 0 WHERE id_deposito = :id_deposito");

        return $stmt->execute(['id_deposito' => $idDeposito]);
    }
}
