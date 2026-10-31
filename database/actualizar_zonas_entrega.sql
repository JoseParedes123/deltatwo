-- Activa las zonas de entrega iniciales del corredor norte bonaerense.
-- Las zonas anteriores quedan inactivas para conservar los envíos históricos.

START TRANSACTION;

UPDATE zonas
SET activo = 0
WHERE nombre NOT IN (
    'Carapachay',
    'Munro',
    'Olivos',
    'San Martín',
    'Tigre',
    'Villa Adelina',
    'Villa Ballester'
);

INSERT INTO zonas (nombre, cobertura, descripcion, activo) VALUES
    ('Carapachay', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('Munro', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('Olivos', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('San Martín', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('Tigre', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('Villa Adelina', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1),
    ('Villa Ballester', 'CABA_GBA', 'Localidad de la provincia de Buenos Aires', 1)
ON DUPLICATE KEY UPDATE
    cobertura = VALUES(cobertura),
    descripcion = VALUES(descripcion),
    activo = 1;

COMMIT;