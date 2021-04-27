### Database update file.
# OpenGnsys 1.1.1, 1.1.1a, 1.1.1b, 1.1.1c - OpenGnsys 1.2.0
#use ogAdmBD

DROP PROCEDURE IF EXISTS altercols;
# Procedure to perform conditional table update.
DELIMITER '//'
CREATE PROCEDURE altercols() BEGIN
	# Add row and column fields to locate computer in the lab (ticket #944).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='n_row' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD n_row SMALLINT NOT NULL DEFAULT 0,
			ADD n_col SMALLINT NOT NULL DEFAULT 0;
	END IF;
	# Add maintenance and remote access fields for computers (tickets #991 y #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='inremotepc' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD inremotepc SMALLINT NOT NULL DEFAULT 0,
			ADD maintenance SMALLINT NOT NULL DEFAULT 0;
	END IF;
	# Add URL to release a reserved computer for remote access (ticket #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='urlrelease' AND TABLE_NAME='remotepc' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE remotepc
			ADD urlrelease VARCHAR(255) DEFAULT NULL AFTER urllogout;
	END IF;
	# Add flag field to indicate if a local session is open (ticket #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='islocal' AND TABLE_NAME='remotepc' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE remotepc
			ADD islocal TINYINT NOT NULL DEFAULT 0;
	END IF;
	#
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='clonator' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			ADD clonator varchar(100) NOT NULL DEFAULT '',
			ADD compressor varchar(100) NOT NULL DEFAULT '',
			ADD filesystem varchar(100) NOT NULL DEFAULT '',
			ADD datasize bigint NOT NULL DEFAULT 0;
	END IF;
	# Add tipodisco (ticket #1037).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='tdisk' AND TABLE_NAME='ordenadores_particiones' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores_particiones
			ADD tdisk VARCHAR(4) DEFAULT NULL AFTER idordenador;
	END IF;
END//
# Run conditional update.
DELIMITER ';'
CALL altercols();
DROP PROCEDURE altercols;

# Redefine some fields as not null.
ALTER TABLE aulas
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;
ALTER TABLE imagenes
	MODIFY inremotepc SMALLINT NOT NULL DEFAULT 0;
# Redefine some fields as null by default.
ALTER TABLE remotepc
      MODIFY urllogin VARCHAR(255) DEFAULT NULL,
      MODIFY urllogout VARCHAR(255) DEFAULT NULL,
      MODIFY urlrelease VARCHAR(255) DEFAULT NULL;

# Support hard disk bigger 2Tb (ticket #1012)
ALTER TABLE ordenadores_particiones
      MODIFY tamano BIGINT NOT NULL DEFAULT '0';
