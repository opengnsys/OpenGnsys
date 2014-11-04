### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6
#use ogAdmBD

# Eliminar procedimiento para evitar errores de ejecución.
DROP PROCEDURE IF EXISTS addcols;
# Procedimiento para actualización condicional de tablas.
delimiter '//'
CREATE PROCEDURE addcols() BEGIN
	# Incluir fecha de despliegue/restauración (ticket #677).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='fechadespliegue' AND TABLE_NAME='ordenadores_particiones' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores_particiones
			ADD fechadespliegue DATETIME NULL AFTER idperfilsoft;
	END IF;
END//
# Ejecutar actualización condicional.
delimiter ';'
CALL addcols();
DROP PROCEDURE addcols;

# Mostrar protocolo de clonación en la cola de acciones (ticket #672).
UPDATE parametros
	SET tipopa = 0
	WHERE idparametro = 30;

