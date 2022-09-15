### Database update file.
# OpenGnsys 1.1.1, 1.1.1a, 1.1.1b, 1.1.1c - OpenGnsys 1.1.1d
#use ogAdmBD

DROP PROCEDURE IF EXISTS altercols;
# Procedure to perform conditional table update.
DELIMITER '//'
CREATE PROCEDURE altercols() BEGIN
	# Add maintenance and remote access fields for computers (tickets #991 y #992).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='inremotepc' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
	ALTER TABLE ordenadores
			ADD inremotepc SMALLINT NOT NULL DEFAULT 0,
			ADD maintenance SMALLINT NOT NULL DEFAULT 0;
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

# Support hard disk bigger 2Tb (ticket #1012)
ALTER TABLE ordenadores_particiones
      MODIFY tamano BIGINT NOT NULL DEFAULT '0';

# Compatibility with UDS 3.5 (ticket #1077)
ALTER TABLE remotepc
      MODIFY urllogin VARCHAR(255),
      MODIFY urllogout VARCHAR(255);

