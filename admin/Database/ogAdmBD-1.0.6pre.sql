### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6
#use ogAdmBD

# Eliminar procedimiento para evitar errores de ejecución.
DROP PROCEDURE IF EXISTS addcols;
# Procedimiento para actualización condicional de tablas.
delimiter '//'
CREATE PROCEDURE addcols() BEGIN
	# Incluir ordenador modelo y fecha de creación de imagen y
	# establecer valores por defecto (ticket #677).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='fechacreacion' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			MODIFY idrepositorio INT(11) NOT NULL DEFAULT 0,
			MODIFY numdisk SMALLINT NOT NULL DEFAULT 0,
			MODIFY numpar SMALLINT NOT NULL DEFAULT 0,
			MODIFY codpar INT(8) NOT NULL DEFAULT 0,
			ADD idordenador INT(11) NOT NULL DEFAULT 0 AFTER idrepositorio,
			ADD fechacreacion DATETIME DEFAULT NULL;
	else
		ALTER TABLE imagenes
			MODIFY idrepositorio INT(11) NOT NULL DEFAULT 0,
			MODIFY idordenador INT(11) NOT NULL DEFAULT 0,
			MODIFY numdisk SMALLINT NOT NULL DEFAULT 0,
			MODIFY numpar SMALLINT NOT NULL DEFAULT 0,
			MODIFY codpar INT(8) NOT NULL DEFAULT 0;
	END IF;
	# Incluir fecha de despliegue/restauración de imagen (ticket #677).
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

# Mostrar disco en comandos Inventario de software e Iniciar sesión.
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;mac;dsk;par'
	WHERE idcomando = 7;
UPDATE comandos
	SET visuparametros = 'dsk;par', parametros = 'nfn;iph;dsk;par'
	WHERE idcomando = 9;

