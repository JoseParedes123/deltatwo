-- Esquema compartido de Logística Delta para las aplicaciones PHP y Java.
-- Importar en una base vacía. Para conservar la base hosteada, usar migrar_host_s11.sql.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(80) NOT NULL,
    apellido VARCHAR(80) NOT NULL,
    dni VARCHAR(15) DEFAULT NULL,
    email VARCHAR(120) NOT NULL,
    nombre_usuario VARCHAR(60) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('ADMINISTRADOR','OPERADOR_DEPOSITO','CHOFER','ADMINISTRACION','CLIENTE','ATENCION_CLIENTE','SUPERADMIN') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuarios_email (email),
    UNIQUE KEY uq_usuarios_nombre_usuario (nombre_usuario),
    UNIQUE KEY uq_usuarios_dni (dni)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE zonas (
    id_zona INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(80) NOT NULL,
    cobertura VARCHAR(30) NOT NULL DEFAULT 'BUENOS_AIRES',
    descripcion VARCHAR(255) DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_zona),
    UNIQUE KEY uq_zonas_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE clientes (
    id_cliente INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    tipo_cliente ENUM('PARTICULAR','CORPORATIVO') NOT NULL DEFAULT 'PARTICULAR',
    razon_social VARCHAR(150) DEFAULT NULL,
    cuit_dni VARCHAR(20) DEFAULT NULL,
    telefono VARCHAR(30) DEFAULT NULL,
    direccion VARCHAR(200) DEFAULT NULL,
    estado_validacion ENUM('PENDIENTE','VALIDADO','RECHAZADO') NOT NULL DEFAULT 'PENDIENTE',
    aprobado_corporativo TINYINT(1) NOT NULL DEFAULT 0,
    descuento_porcentaje DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_cliente),
    UNIQUE KEY uq_clientes_usuario (id_usuario),
    CONSTRAINT fk_clientes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE choferes (
    id_chofer INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    licencia VARCHAR(30) NOT NULL,
    categoria_licencia VARCHAR(10) NOT NULL,
    fecha_vencimiento_licencia DATE NOT NULL,
    estado ENUM('ACTIVO','INACTIVO','SUSPENDIDO') NOT NULL DEFAULT 'ACTIVO',
    PRIMARY KEY (id_chofer),
    UNIQUE KEY uq_choferes_usuario (id_usuario),
    UNIQUE KEY uq_choferes_licencia (licencia),
    CONSTRAINT fk_choferes_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE depositos (
    id_deposito INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(80) NOT NULL,
    direccion VARCHAR(200) NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_deposito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE paquetes (
    id_paquete INT NOT NULL AUTO_INCREMENT,
    codigo VARCHAR(20) NOT NULL,
    descripcion VARCHAR(255) DEFAULT NULL,
    peso_kg DECIMAL(10,3) NOT NULL,
    largo_cm DECIMAL(10,2) DEFAULT NULL,
    ancho_cm DECIMAL(10,2) DEFAULT NULL,
    alto_cm DECIMAL(10,2) DEFAULT NULL,
    tipo_mercaderia VARCHAR(80) NOT NULL,
    valor_declarado DECIMAL(12,2) DEFAULT NULL,
    es_fragil TINYINT(1) NOT NULL DEFAULT 0,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_paquete),
    UNIQUE KEY uq_paquetes_codigo (codigo),
    KEY idx_paquetes_fecha (fecha_registro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehiculos (
    id_vehiculo INT NOT NULL AUTO_INCREMENT,
    patente VARCHAR(15) NOT NULL,
    tipo VARCHAR(40) NOT NULL,
    marca VARCHAR(50) DEFAULT NULL,
    modelo VARCHAR(50) DEFAULT NULL,
    anio SMALLINT DEFAULT NULL,
    capacidad_peso_kg DECIMAL(10,2) NOT NULL,
    capacidad_volumen_m3 DECIMAL(10,3) NOT NULL,
    estado ENUM('DISPONIBLE','EN_RUTA','MANTENIMIENTO','INACTIVO') NOT NULL DEFAULT 'DISPONIBLE',
    id_zona_restringida INT DEFAULT NULL,
    motivo_restriccion VARCHAR(200) DEFAULT NULL,
    PRIMARY KEY (id_vehiculo),
    UNIQUE KEY uq_vehiculos_patente (patente),
    KEY idx_vehiculos_zona (id_zona_restringida),
    CONSTRAINT fk_vehiculos_zona FOREIGN KEY (id_zona_restringida) REFERENCES zonas (id_zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ubicaciones (
    id_ubicacion INT NOT NULL AUTO_INCREMENT,
    id_deposito INT NOT NULL,
    sector VARCHAR(20) NOT NULL,
    estante VARCHAR(20) NOT NULL,
    posicion VARCHAR(20) NOT NULL,
    ocupada TINYINT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (id_ubicacion),
    UNIQUE KEY uq_ubicacion (id_deposito,sector,estante,posicion),
    CONSTRAINT fk_ubicaciones_deposito FOREIGN KEY (id_deposito) REFERENCES depositos (id_deposito)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE rutas (
    id_ruta INT NOT NULL AUTO_INCREMENT,
    fecha DATE NOT NULL,
    origen VARCHAR(200) NOT NULL,
    destino VARCHAR(200) NOT NULL,
    recorrido TEXT DEFAULT NULL,
    observaciones VARCHAR(255) DEFAULT NULL,
    id_vehiculo INT NOT NULL,
    id_chofer INT NOT NULL,
    id_zona INT DEFAULT NULL,
    estado ENUM('PLANIFICADA','EN_CURSO','FINALIZADA','CANCELADA') NOT NULL DEFAULT 'PLANIFICADA',
    PRIMARY KEY (id_ruta),
    KEY idx_rutas_chofer_estado (id_chofer,estado,fecha),
    CONSTRAINT fk_rutas_chofer FOREIGN KEY (id_chofer) REFERENCES choferes (id_chofer),
    CONSTRAINT fk_rutas_vehiculo FOREIGN KEY (id_vehiculo) REFERENCES vehiculos (id_vehiculo),
    CONSTRAINT fk_rutas_zona FOREIGN KEY (id_zona) REFERENCES zonas (id_zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE contactos (
    id_contacto INT NOT NULL AUTO_INCREMENT,
    tipo ENUM('REMITENTE','DESTINATARIO') NOT NULL,
    nombre VARCHAR(120) NOT NULL,
    telefono VARCHAR(30) DEFAULT NULL,
    email VARCHAR(120) DEFAULT NULL,
    direccion VARCHAR(200) NOT NULL,
    id_zona INT DEFAULT NULL,
    id_cliente INT DEFAULT NULL,
    PRIMARY KEY (id_contacto),
    KEY idx_contactos_cliente (id_cliente,tipo),
    KEY idx_contactos_zona (id_zona),
    CONSTRAINT fk_contactos_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT fk_contactos_zona FOREIGN KEY (id_zona) REFERENCES zonas (id_zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tarifas (
    id_tarifa INT NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    id_zona INT NOT NULL,
    tipo_envio VARCHAR(60) NOT NULL,
    precio_base DECIMAL(10,2) NOT NULL,
    precio_por_kg DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    precio_por_m3 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    recargo_fragil DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    vigente_desde DATE NOT NULL,
    vigente_hasta DATE DEFAULT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_tarifa),
    KEY idx_tarifa_vigente (id_zona,tipo_envio,activo,vigente_desde,vigente_hasta),
    CONSTRAINT fk_tarifas_zona FOREIGN KEY (id_zona) REFERENCES zonas (id_zona)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

CREATE TABLE envios (
    id_envio INT NOT NULL AUTO_INCREMENT,
    codigo_envio VARCHAR(20) NOT NULL,
    id_cliente INT NOT NULL,
    id_remitente INT NOT NULL,
    id_destinatario INT NOT NULL,
    id_paquete INT NOT NULL,
    estado ENUM('REGISTRADO','RECIBIDO_DEPOSITO','PREPARADO','EN_TRANSITO','EN_REPARTO','ENTREGADO','NO_ENTREGADO','DEVUELTO','EXTRAVIADO','CANCELADO') NOT NULL DEFAULT 'REGISTRADO',
    id_zona_destino INT NOT NULL,
    id_tarifa_aplicada INT DEFAULT NULL,
    precio_final DECIMAL(12,2) DEFAULT NULL,
    id_ruta INT DEFAULT NULL,
    orden_parada INT DEFAULT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega_estimada DATE DEFAULT NULL,
    id_usuario_registro INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (id_envio),
    UNIQUE KEY uq_envios_codigo (codigo_envio),
    UNIQUE KEY uq_envios_paquete (id_paquete),
    KEY idx_envios_cliente (id_cliente,activo,fecha_solicitud),
    KEY idx_envios_estado (estado),
    KEY idx_envios_ruta (id_ruta),
    CONSTRAINT fk_envios_cliente FOREIGN KEY (id_cliente) REFERENCES clientes (id_cliente),
    CONSTRAINT fk_envios_remitente FOREIGN KEY (id_remitente) REFERENCES contactos (id_contacto),
    CONSTRAINT fk_envios_destinatario FOREIGN KEY (id_destinatario) REFERENCES contactos (id_contacto),
    CONSTRAINT fk_envios_paquete FOREIGN KEY (id_paquete) REFERENCES paquetes (id_paquete),
    CONSTRAINT fk_envios_zona FOREIGN KEY (id_zona_destino) REFERENCES zonas (id_zona),
    CONSTRAINT fk_envios_tarifa FOREIGN KEY (id_tarifa_aplicada) REFERENCES tarifas (id_tarifa),
    CONSTRAINT fk_envios_ruta FOREIGN KEY (id_ruta) REFERENCES rutas (id_ruta),
    CONSTRAINT fk_envios_usuario_reg FOREIGN KEY (id_usuario_registro) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE movimientos_deposito (
    id_movimiento INT NOT NULL AUTO_INCREMENT,
    id_paquete INT NOT NULL,
    id_ubicacion_anterior INT DEFAULT NULL,
    id_ubicacion_nueva INT NOT NULL,
    es_actual TINYINT(1) NOT NULL DEFAULT 1,
    id_usuario INT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    motivo VARCHAR(200) NOT NULL,
    PRIMARY KEY (id_movimiento),
    KEY idx_movdep_paquete_fecha (id_paquete,fecha_hora),
    CONSTRAINT fk_movdep_paquete FOREIGN KEY (id_paquete) REFERENCES paquetes (id_paquete),
    CONSTRAINT fk_movdep_ubic_ant FOREIGN KEY (id_ubicacion_anterior) REFERENCES ubicaciones (id_ubicacion),
    CONSTRAINT fk_movdep_ubic_nueva FOREIGN KEY (id_ubicacion_nueva) REFERENCES ubicaciones (id_ubicacion),
    CONSTRAINT fk_movdep_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE historial (
    id_historial INT NOT NULL AUTO_INCREMENT,
    tipo_evento ENUM('CAMBIO_ESTADO','MODIFICACION') NOT NULL,
    tabla_afectada VARCHAR(60) NOT NULL,
    registro_id INT NOT NULL,
    campo_modificado VARCHAR(60) DEFAULT NULL,
    valor_anterior VARCHAR(255) DEFAULT NULL,
    valor_nuevo VARCHAR(255) DEFAULT NULL,
    id_usuario INT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    descripcion VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id_historial),
    KEY idx_historial_registro (tabla_afectada,registro_id,fecha_hora),
    CONSTRAINT fk_historial_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE incidencias (
    id_incidencia INT NOT NULL AUTO_INCREMENT,
    id_envio INT NOT NULL,
    fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    lugar VARCHAR(150) NOT NULL,
    id_usuario_registro INT NOT NULL,
    tipo ENUM('EXTRAVIO','DANIO','DIRECCION_INCORRECTA','PROBLEMA_ENTREGA','PROBLEMA_PAQUETE','OTRO') NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('ABIERTA','EN_INVESTIGACION','RESUELTA','CERRADA') NOT NULL DEFAULT 'ABIERTA',
    PRIMARY KEY (id_incidencia),
    KEY idx_incidencias_envio_fecha (id_envio,fecha_hora),
    KEY idx_incidencias_estado (estado),
    CONSTRAINT fk_incidencias_envio FOREIGN KEY (id_envio) REFERENCES envios (id_envio),
    CONSTRAINT fk_incidencias_usuario FOREIGN KEY (id_usuario_registro) REFERENCES usuarios (id_usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

SET FOREIGN_KEY_CHECKS = 1;

-- Zonas de servicio: provincia de Buenos Aires.
INSERT INTO zonas (id_zona,nombre,cobertura,descripcion) VALUES
(1,'Carapachay','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(2,'Munro','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(3,'Olivos','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(4,'San Martín','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(5,'Tigre','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(6,'Villa Adelina','BUENOS_AIRES','Localidad de la provincia de Buenos Aires'),
(7,'Villa Ballester','BUENOS_AIRES','Localidad de la provincia de Buenos Aires');

-- Tarifas estimadas a partir de la tarifa histórica de CABA. Actualizables desde SQL.
INSERT INTO tarifas (nombre,id_zona,tipo_envio,precio_base,precio_por_kg,precio_por_m3,recargo_fragil,vigente_desde) VALUES
('Envío estándar Carapachay',1,'ESTANDAR',2500.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar Munro',2,'ESTANDAR',2500.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar Olivos',3,'ESTANDAR',2500.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar San Martín',4,'ESTANDAR',3000.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar Tigre',5,'ESTANDAR',4000.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar Villa Adelina',6,'ESTANDAR',3000.00,350.00,0.00,500.00,'2026-11-04'),
('Envío estándar Villa Ballester',7,'ESTANDAR',3500.00,350.00,0.00,500.00,'2026-11-04');

-- Direcciones que antes estaban escritas directamente en panel-admin.php.
INSERT INTO sucursales (id_sucursal,id_zona,direccion,provincia) VALUES
(1,3,'Avenida Maipú 2683','Buenos Aires'),(2,3,'Avenida Maipú 3028','Buenos Aires'),
(3,3,'Calle Uruguay 1841','Buenos Aires'),(4,3,'Calle Uruguay 2404','Buenos Aires'),
(5,3,'Avenida Maipú 2881','Buenos Aires'),(6,2,'Avenida Vélez Sarsfield 4319','Buenos Aires'),
(7,2,'Avenida Vélez Sarsfield 4819','Buenos Aires'),(8,2,'Avenida Vélez Sarsfield 4945','Buenos Aires'),
(9,2,'Avenida Mitre 3870','Buenos Aires'),(10,1,'Calle Deheza 5798','Buenos Aires'),
(11,6,'Avenida Paraná 6001','Buenos Aires'),(12,7,'Calle Buenos Aires 4479','Buenos Aires'),
(13,7,'Calle Buenos Aires 4755','Buenos Aires'),(14,7,'Calle Lacroze 1850','Buenos Aires'),
(15,7,'Calle Marengo 4479','Buenos Aires'),(16,4,'Calle Campos 1951','Buenos Aires'),
(17,4,'Calle Pueyrredón 4694','Buenos Aires'),(18,4,'Calle Mitre 3166','Buenos Aires'),
(19,4,'Calle Belgrano 4514','Buenos Aires'),(20,4,'Calle Pellegrini 1980','Buenos Aires'),
(21,5,'Avenida Cazón 1140','Buenos Aires');

-- Datos mínimos para comenzar a operar el depósito en una instalación nueva.
INSERT INTO depositos (id_deposito,nombre,direccion,activo)
VALUES (1,'Depósito Central','Provincia de Buenos Aires',1);
INSERT INTO ubicaciones (id_ubicacion,id_deposito,sector,estante,posicion,ocupada) VALUES
(1,1,'A','01','01',0),(2,1,'A','01','02',0),(3,1,'A','01','03',0),
(4,1,'A','02','01',0),(5,1,'A','02','02',0),(6,1,'A','02','03',0),
(7,1,'B','01','01',0),(8,1,'B','01','02',0),(9,1,'B','01','03',0),
(10,1,'B','02','01',0),(11,1,'B','02','02',0),(12,1,'B','02','03',0);

-- Cuentas de práctica iniciales; cambiar las claves al instalar en un entorno real.
INSERT INTO usuarios (id_usuario,nombre,apellido,email,nombre_usuario,password_hash,rol,activo) VALUES
(1,'Admin','Delta','admin@delta.local','admin',SHA2('DeltaAdmin123',256),'ADMINISTRADOR',1),
(2,'Operador','Delta','operador@delta.local','operador',SHA2('123456',256),'OPERADOR_DEPOSITO',1);

-- Vehículo inicial para probar asignación de rutas en Java.
INSERT INTO vehiculos (id_vehiculo,patente,tipo,marca,modelo,anio,capacidad_peso_kg,capacidad_volumen_m3,estado)
VALUES (1,'DEMO-001','FURGON','Delta','Unidad inicial',2024,1200.00,8.000,'DISPONIBLE');
