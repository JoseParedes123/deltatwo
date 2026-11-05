-- Migración en el lugar para la estructura del volcado s11_logistica_delta.sql.
-- Hace falta respaldo previo. Conserva usuarios, clientes, paquetes, envíos,
-- contactos, rutas y movimientos operativos; crea seguimiento y consultas web.
-- Ejecutar una sola vez sobre la base existente, no sobre una base vacía.
-- MySQL confirma muchos ALTER/CREATE automáticamente: sacar un respaldo exportado
-- y probar la migración en una copia antes de aplicarla al hosting.

-- Habilita el rol web de Atención al Cliente y quita el campo vacío de último acceso.
ALTER TABLE usuarios
    MODIFY rol ENUM('ADMINISTRADOR','OPERADOR_DEPOSITO','CHOFER','ADMINISTRACION','CLIENTE','ATENCION_CLIENTE','SUPERADMIN') NOT NULL,
    DROP COLUMN ultimo_login;

-- El volcado entregado no contiene dimensiones. Se agregan para la cotización
-- web y la validación de volumen del vehículo; la cantidad era 1 en todos los paquetes.
ALTER TABLE paquetes
    ADD COLUMN largo_cm DECIMAL(10,2) DEFAULT NULL AFTER peso_kg,
    ADD COLUMN ancho_cm DECIMAL(10,2) DEFAULT NULL AFTER largo_cm,
    ADD COLUMN alto_cm DECIMAL(10,2) DEFAULT NULL AFTER ancho_cm,
    DROP COLUMN cantidad_bultos;

-- Guarda fecha y hora de la incidencia en un solo campo; las columnas de resolución
-- estaban vacías en el volcado y ninguna de las dos aplicaciones las usa.
ALTER TABLE incidencias ADD COLUMN fecha_hora DATETIME NULL AFTER id_envio;
UPDATE incidencias
SET fecha_hora = STR_TO_DATE(CONCAT(fecha, ' ', hora), '%Y-%m-%d %H:%i:%s')
WHERE fecha_hora IS NULL;
ALTER TABLE incidencias
    MODIFY fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    DROP COLUMN fecha,
    DROP COLUMN hora,
    DROP COLUMN resolucion,
    DROP COLUMN fecha_resolucion;

-- Conserva las zonas antiguas como inactivas para no romper envíos históricos.
ALTER TABLE zonas MODIFY cobertura VARCHAR(30) NOT NULL;
UPDATE zonas SET activo = 0;
INSERT INTO zonas (nombre,cobertura,descripcion,activo) VALUES
    ('Carapachay','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('Munro','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('Olivos','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('San Martín','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('Tigre','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('Villa Adelina','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1),
    ('Villa Ballester','BUENOS_AIRES','Localidad de la provincia de Buenos Aires',1)
ON DUPLICATE KEY UPDATE
    cobertura = VALUES(cobertura), descripcion = VALUES(descripcion), activo = 1;

-- Las tarifas anteriores quedan vigentes solo para los envíos históricos.
UPDATE tarifas SET activo = 0;
INSERT INTO tarifas (nombre,id_zona,tipo_envio,precio_base,precio_por_kg,precio_por_m3,recargo_fragil,vigente_desde)
SELECT CONCAT('Envío estándar ',z.nombre),z.id_zona,'ESTANDAR',
       CASE z.nombre
           WHEN 'Carapachay' THEN 2500.00
           WHEN 'Munro' THEN 2500.00
           WHEN 'Olivos' THEN 2500.00
           WHEN 'San Martín' THEN 3000.00
           WHEN 'Villa Adelina' THEN 3000.00
           WHEN 'Villa Ballester' THEN 3500.00
           WHEN 'Tigre' THEN 4000.00
       END,
       350.00,0.00,500.00,'2026-11-04'
FROM zonas z
WHERE z.cobertura = 'BUENOS_AIRES' AND z.activo = 1;

CREATE TABLE sucursales (
    id_sucursal INT NOT NULL AUTO_INCREMENT,
    id_zona INT NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    provincia VARCHAR(80) NOT NULL DEFAULT 'Buenos Aires',
    activa TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_sucursal),
    UNIQUE KEY uq_sucursal_direccion (id_zona,direccion),
    CONSTRAINT fk_sucursales_zona FOREIGN KEY (id_zona) REFERENCES zonas (id_zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO sucursales (id_zona,direccion,provincia)
SELECT z.id_zona,v.direccion,'Buenos Aires'
FROM (
    SELECT 'Olivos' localidad,'Avenida Maipú 2683' direccion UNION ALL
    SELECT 'Olivos','Avenida Maipú 3028' UNION ALL
    SELECT 'Olivos','Calle Uruguay 1841' UNION ALL
    SELECT 'Olivos','Calle Uruguay 2404' UNION ALL
    SELECT 'Olivos','Avenida Maipú 2881' UNION ALL
    SELECT 'Munro','Avenida Vélez Sarsfield 4319' UNION ALL
    SELECT 'Munro','Avenida Vélez Sarsfield 4819' UNION ALL
    SELECT 'Munro','Avenida Vélez Sarsfield 4945' UNION ALL
    SELECT 'Munro','Avenida Mitre 3870' UNION ALL
    SELECT 'Carapachay','Calle Deheza 5798' UNION ALL
    SELECT 'Villa Adelina','Avenida Paraná 6001' UNION ALL
    SELECT 'Villa Ballester','Calle Buenos Aires 4479' UNION ALL
    SELECT 'Villa Ballester','Calle Buenos Aires 4755' UNION ALL
    SELECT 'Villa Ballester','Calle Lacroze 1850' UNION ALL
    SELECT 'Villa Ballester','Calle Marengo 4479' UNION ALL
    SELECT 'San Martín','Calle Campos 1951' UNION ALL
    SELECT 'San Martín','Calle Pueyrredón 4694' UNION ALL
    SELECT 'San Martín','Calle Mitre 3166' UNION ALL
    SELECT 'San Martín','Calle Belgrano 4514' UNION ALL
    SELECT 'San Martín','Calle Pellegrini 1980' UNION ALL
    SELECT 'Tigre','Avenida Cazón 1140'
) v
JOIN zonas z ON z.nombre = v.localidad AND z.activo = 1
ON DUPLICATE KEY UPDATE activa = 1;

CREATE TABLE movimientos_seguimiento (
    id_movimiento INT NOT NULL AUTO_INCREMENT,
    id_envio INT NOT NULL,
    id_sucursal INT DEFAULT NULL,
    detalle VARCHAR(255) NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_movimiento),
    KEY idx_seguimiento_envio_fecha (id_envio,fecha_hora),
    CONSTRAINT fk_seguimiento_envio FOREIGN KEY (id_envio) REFERENCES envios (id_envio),
    CONSTRAINT fk_seguimiento_sucursal FOREIGN KEY (id_sucursal) REFERENCES sucursales (id_sucursal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE consultas_web (
    id_consulta INT NOT NULL AUTO_INCREMENT,
    id_cliente INT DEFAULT NULL,
    id_envio INT DEFAULT NULL,
    nombre VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL,
    asunto VARCHAR(80) NOT NULL,
    mensaje TEXT NOT NULL,
    estado ENUM('PENDIENTE','RESPUESTA_PREPARADA','RESPONDIDA') NOT NULL DEFAULT 'PENDIENTE',
    respuesta TEXT DEFAULT NULL,
    id_usuario_respuesta INT DEFAULT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_respuesta DATETIME DEFAULT NULL,
    PRIMARY KEY (id_consulta),
    KEY idx_consultas_estado_fecha (estado,fecha_creacion),
    CONSTRAINT fk_consultas_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT fk_consultas_envio FOREIGN KEY (id_envio) REFERENCES envios (id_envio),
    CONSTRAINT fk_consultas_usuario_respuesta FOREIGN KEY (id_usuario_respuesta) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- No contienen datos en el volcado hosteado y ninguna aplicación las usa.
DROP TABLE comprobantes;
DROP TABLE intentos_entrega;

-- Importante: la autorización de ATENCION_CLIENTE en Java se bloquea en código;
-- ese rol solo accede a la bandeja web de atención.
