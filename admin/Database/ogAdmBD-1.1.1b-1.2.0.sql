### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.1, 1.1.1a, 1.1.1b - OpenGnsys 1.2.0
#use ogAdmBD

DROP PROCEDURE IF EXISTS addcols;
# Procedimiento para actualización condicional de tablas.
DELIMITER '//'
CREATE PROCEDURE addcols() BEGIN
	# Añadir campos fila y columna para localización de ordenador en el aula (ticket #944).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='n_row' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD n_row SMALLINT DEFAULT 0,
			ADD n_col SMALLINT DEFAULT 0;
	END IF;
END//
# Ejecutar actualización condicional.
DELIMITER ';'
CALL addcols();
DROP PROCEDURE addcols;

