### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.1, 1.1.1a, 1.1.1b, 1.1.1c - OpenGnsys 1.2.0
#use ogAdmBD

DROP PROCEDURE IF EXISTS altercols;
# Procedimiento para actualización condicional de tablas.
DELIMITER '//'
CREATE PROCEDURE altercols() BEGIN
	# Añadir campos fila y columna para localización de ordenador en el aula (ticket #944).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='n_row' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD n_row SMALLINT NOT NULL DEFAULT 0,
			ADD n_col SMALLINT NOT NULL DEFAULT 0;
	END IF;
	# Añadir campos de ordenador en mantenimiento y con acceso remoto (tickets #991 y #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='inremotepc' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD inremotepc SMALLINT NOT NULL DEFAULT 0,
			ADD maintenance SMALLINT NOT NULL DEFAULT 0;
	END IF;
END//
# Ejecutar actualización condicional.
DELIMITER ';'
CALL altercols();
DROP PROCEDURE altercols;

# Redefinir algunos campos como no nulos.
ALTER TABLE aulas
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE imagenes
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;

