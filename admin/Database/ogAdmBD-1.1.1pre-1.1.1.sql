### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.1pre - OpenGnsys 1.1.1
#use ogAdmBD

# Eliminar procedimiento y disparador para evitar errores de ejecución.
DROP PROCEDURE IF EXISTS addcols;
# Procedimiento para actualización condicional de tablas.
DELIMITER '//'
CREATE PROCEDURE addcols() BEGIN
	# Añadir campo para incluir PC de profesor de aula (ticket #816).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='idordprofesor' AND TABLE_NAME='aulas' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE aulas
			ADD idordprofesor INT(11) DEFAULT 0 AFTER puestos;
	END IF;
	# Borrar campos sin uso del antiguo servicio ogAdmRepo (ticket #875).
	IF EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='puertorepo' AND TABLE_NAME='repositorios' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE repositorios
			DROP passguor,
			DROP puertorepo;
	END IF;
END//
# Ejecutar actualización condicional.
DELIMITER ';'
CALL addcols();
DROP PROCEDURE addcols;

# Cambio del nombre de las plantillas PXE para compatibilidad con UEFI.
UPDATE ordenadores SET arranque='10' WHERE arranque='01';

# Eliminar tabla sustituida por fichero de configuracion (ticket #812).
DROP TABLE IF EXISTS tipospar;

