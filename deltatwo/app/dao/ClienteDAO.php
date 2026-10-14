<?php

require_once __DIR__ . '/../config/Conexion.php';

class ClienteDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de clientes.
        $this->db = (new Conexion())->conectar();
    }

    // Trae todos los clientes junto con los datos de su usuario.
    public function obtenerTodos(): array
    {
        $sql = "SELECT clientes.*, usuarios.nombre, usuarios.apellido, usuarios.email
                FROM clientes
                INNER JOIN usuarios ON clientes.id_usuario = usuarios.id_usuario
                ORDER BY clientes.fecha_registro DESC";

        return $this->db->query($sql)->fetchAll();
    }

    // Busca un cliente puntual.
    public function obtenerPorId(int $idCliente): ?array
    {
        $sql = "SELECT clientes.*, usuarios.nombre, usuarios.apellido, usuarios.email
                FROM clientes
                INNER JOIN usuarios ON clientes.id_usuario = usuarios.id_usuario
                WHERE clientes.id_cliente = :id_cliente";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_cliente' => $idCliente]);
        $cliente = $stmt->fetch();

        return $cliente ?: null;
    }

    // Busca el cliente que corresponde al usuario logueado.
    public function obtenerPorUsuario(int $idUsuario): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM clientes WHERE id_usuario = :id_usuario");
        $stmt->execute(['id_usuario' => $idUsuario]);
        $cliente = $stmt->fetch();

        return $cliente ?: null;
    }

    // Registra al cliente desde la web. Queda PENDIENTE de validacion.
    public function crear(int $idUsuario, string $tipoCliente, ?string $razonSocial, ?string $cuitDni, ?string $telefono, ?string $direccion): bool
    {
        $sql = "INSERT INTO clientes (id_usuario, tipo_cliente, razon_social, cuit_dni, telefono, direccion)
                VALUES (:id_usuario, :tipo_cliente, :razon_social, :cuit_dni, :telefono, :direccion)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id_usuario' => $idUsuario,
            'tipo_cliente' => $tipoCliente,
            'razon_social' => $razonSocial,
            'cuit_dni' => $cuitDni,
            'telefono' => $telefono,
            'direccion' => $direccion,
        ]);
    }

    // Actualiza los datos de contacto del cliente.
    public function actualizar(int $idCliente, ?string $razonSocial, ?string $cuitDni, ?string $telefono, ?string $direccion): bool
    {
        $sql = "UPDATE clientes
                SET razon_social = :razon_social, cuit_dni = :cuit_dni, telefono = :telefono, direccion = :direccion
                WHERE id_cliente = :id_cliente";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'razon_social' => $razonSocial,
            'cuit_dni' => $cuitDni,
            'telefono' => $telefono,
            'direccion' => $direccion,
            'id_cliente' => $idCliente,
        ]);
    }

    // Administracion valida o rechaza al cliente y define su descuento (tarifa acordada).
    public function actualizarValidacion(int $idCliente, string $estadoValidacion, bool $aprobadoCorporativo, float $descuentoPorcentaje): bool
    {
        $sql = "UPDATE clientes
                SET estado_validacion = :estado_validacion,
                    aprobado_corporativo = :aprobado_corporativo,
                    descuento_porcentaje = :descuento_porcentaje
                WHERE id_cliente = :id_cliente";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'estado_validacion' => $estadoValidacion,
            'aprobado_corporativo' => (int) $aprobadoCorporativo,
            'descuento_porcentaje' => $descuentoPorcentaje,
            'id_cliente' => $idCliente,
        ]);
    }
}
