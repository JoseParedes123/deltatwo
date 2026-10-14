<?php

require_once __DIR__ . '/../config/Conexion.php';

class UsuarioDAO
{
    private PDO $db;

    public function __construct()
    {
        // Conexion compartida para las consultas de usuarios.
        $this->db = (new Conexion())->conectar();
    }

    // Devuelve todos los usuarios (sin la clave) para el panel de administracion.
    public function obtenerTodos(): array
    {
        $sql = "SELECT id_usuario, nombre, apellido, dni, email, nombre_usuario, rol, activo
                FROM usuarios
                ORDER BY apellido, nombre";

        return $this->db->query($sql)->fetchAll();
    }

    // Se usa en login y registro para encontrar usuarios por email o por nombre de usuario.
    public function obtenerPorIdentificador(string $identificador): ?array
    {
        $sql = "SELECT * FROM usuarios WHERE email = :email OR nombre_usuario = :nombre_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'email' => $identificador,
            'nombre_usuario' => $identificador,
        ]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    // Busca un usuario por id para refrescar la sesion.
    public function obtenerPorId(int $idUsuario): ?array
    {
        $sql = "SELECT id_usuario, nombre, apellido, dni, email, nombre_usuario, rol, activo
                FROM usuarios
                WHERE id_usuario = :id_usuario";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_usuario' => $idUsuario]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    // Crea un usuario nuevo y devuelve su id (0 si fallo). La clave se guarda hasheada.
    public function registrar(string $nombre, string $apellido, ?string $dni, string $email, string $nombreUsuario, string $password, string $rol): int
    {
        $sql = "INSERT INTO usuarios (nombre, apellido, dni, email, nombre_usuario, password_hash, rol)
                VALUES (:nombre, :apellido, :dni, :email, :nombre_usuario, :password_hash, :rol)";

        $stmt = $this->db->prepare($sql);
        $guardado = $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'dni' => $dni,
            'email' => $email,
            'nombre_usuario' => $nombreUsuario,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'rol' => $rol,
        ]);

        return $guardado ? (int) $this->db->lastInsertId() : 0;
    }

    // Valida las credenciales y devuelve solo los datos necesarios para la sesion.
    public function validarLogin(string $identificador, string $password): ?array
    {
        $usuario = $this->obtenerPorIdentificador($identificador);

        if (!$usuario || !$usuario['activo']) {
            return null;
        }

        // Permite entrar con passwords hasheados y tambien con los SHA-256 de los datos del SQL original.
        $passwordCorrecta = password_verify($password, $usuario['password_hash'])
            || hash('sha256', $password) === $usuario['password_hash'];

        if (!$passwordCorrecta) {
            return null;
        }

        return [
            'id_usuario' => (int) $usuario['id_usuario'],
            'nombre' => $usuario['nombre'],
            'apellido' => $usuario['apellido'],
            'email' => $usuario['email'],
            'nombre_usuario' => $usuario['nombre_usuario'],
            'rol' => $usuario['rol'],
        ];
    }

    // Actualiza datos visibles del perfil del usuario.
    public function actualizarPerfil(int $idUsuario, string $nombre, string $apellido): bool
    {
        $sql = "UPDATE usuarios
                SET nombre = :nombre, apellido = :apellido
                WHERE id_usuario = :id_usuario";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nombre' => $nombre,
            'apellido' => $apellido,
            'id_usuario' => $idUsuario,
        ]);
    }

    // No se borra: el usuario queda inactivo para conservar el historial.
    public function desactivar(int $idUsuario): bool
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = :id_usuario");

        return $stmt->execute(['id_usuario' => $idUsuario]);
    }
}
