-- crea el historial de sucursales que necesita el seguimiento de envíos
CREATE TABLE IF NOT EXISTS `movimientos_seguimiento` (
  `id_movimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_envio` int(11) NOT NULL,
  `sucursal` varchar(100) NOT NULL,
  `provincia` varchar(80) NOT NULL,
  `detalle` varchar(255) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_movimiento`),
  KEY `idx_seguimiento_envio_fecha` (`id_envio`, `fecha_hora`),
  CONSTRAINT `fk_seguimiento_envio`
    FOREIGN KEY (`id_envio`) REFERENCES `envios` (`id_envio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
