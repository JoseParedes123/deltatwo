-- Corrige la cuenta de ejemplo y crea un administrador para una base ya importada.
-- Ejecutar desde phpMyAdmin con la base logistica_delta seleccionada.

-- Restablece las cuentas de práctica sin duplicar el administrador.
UPDATE usuarios
SET password_hash = SHA2('123456', 256), activo = 1
WHERE nombre_usuario = 'operador';

UPDATE usuarios
SET password_hash = SHA2('DeltaAdmin123', 256), rol = 'ADMINISTRADOR', activo = 1
WHERE nombre_usuario = 'admin';

INSERT INTO usuarios (nombre, apellido, dni, email, nombre_usuario, password_hash, rol, activo)
SELECT 'Admin', 'Delta', NULL, 'admin@delta.local', 'admin', SHA2('DeltaAdmin123', 256), 'ADMINISTRADOR', 1
WHERE NOT EXISTS (
    SELECT 1
    FROM usuarios
    WHERE nombre_usuario = 'admin'
);
