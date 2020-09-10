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
	# Añadir campo con URL para liberar equipo reservado para acceso remoto (ticket #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='urlrelease' AND TABLE_NAME='remotepc' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE remotepc
			ADD urlrelease VARCHAR(100) DEFAULT NULL;
	END IF;
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='clonator' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			ADD clonator varchar(100) NOT NULL DEFAULT '',
			ADD compressor varchar(100) NOT NULL DEFAULT '',
			ADD filesystem varchar(100) NOT NULL DEFAULT '',
			ADD datasize bigint NOT NULL DEFAULT 0;
	END IF;
END//
# Ejecutar actualización condicional.
DELIMITER ';'
CALL altercols();
DROP PROCEDURE altercols;

# Redefinir campos como no nulos.
ALTER TABLE aulas
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE imagenes
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;
# Redefinir campos como nulos por defecto.
ALTER TABLE remotepc
      MODIFY urllogin VARCHAR(100) DEFAULT NULL,
      MODIFY urllogout VARCHAR(100) DEFAULT NULL;
