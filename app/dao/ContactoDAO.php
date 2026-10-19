<?php

require_once __DIR__ . '/../config/Conexion.php';

class ContactoDAO
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        $this->db = $db ?? (new Conexion())->conectar();
    }

    // Busca un contacto puntual.
    public function obtenerPorId(int $idContacto): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM contactos WHERE id_contacto = :id_contacto");
        $stmt->execute(['id_contacto' => $idContacto]);
        $contacto = $stmt->fetch();

        return $contacto ?: null;
    }

    // Remitentes guardados por un cliente, para no cargarlos de nuevo en cada envio.
    public function obtenerPorCliente(int $idCliente): array
    {
        $sql = "SELECT * FROM contactos
                WHERE id_cliente = :id_cliente AND tipo = 'REMITENTE'
                ORDER BY nombre";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_cliente' => $idCliente]);

        return $stmt->fetchAll();
    }

    // Carga un remitente o destinatario y devuelve su id (0 si fallo).
    public function crear(string $tipo, string $nombre, ?string $telefono, ?string $email, string $direccion, ?int $idZona, ?int $idCliente): int
    {
        $sql = "INSERT INTO contactos (tipo, nombre, telefono, email, direccion, id_zona, id_cliente)
                VALUES (:tipo, :nombre, :telefono, :email, :direccion, :id_zona, :id_cliente)";

        $stmt = $this->db->prepare($sql);
        $guardado = $stmt->execute([
            'tipo' => $tipo,
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $email,
            'direccion' => $direccion,
            'id_zona' => $idZona,
            'id_cliente' => $idCliente,
        ]);

        return $guardado ? (int) $this->db->lastInsertId() : 0;
    }

    // Modifica los datos de un contacto.
    public function actualizar(int $idContacto, string $nombre, ?string $telefono, ?string $email, string $direccion, ?int $idZona): bool
    {
        $sql = "UPDATE contactos
                SET nombre = :nombre, telefono = :telefono, email = :email, direccion = :direccion, id_zona = :id_zona
                WHERE id_contacto = :id_contacto";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'telefono' => $telefono,
            'email' => $email,
            'direccion' => $direccion,
            'id_zona' => $idZona,
            'id_contacto' => $idContacto,
        ]);
    }
}
