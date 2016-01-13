### Fichero de actualización de la base de datos.
# OpenGnSys 1.0.6
#use ogAdmBD

# Eliminar procedimiento para evitar errores de ejecución.
DROP PROCEDURE IF EXISTS addcols;
# Procedimiento para actualización condicional de tablas.
delimiter '//'
CREATE PROCEDURE addcols() BEGIN
	# Añadir campo para incluir aulas en proyecto Remote PC (ticket #708).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='inremotepc' AND TABLE_NAME='aulas' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE aulas
			ADD inremotepc TINYINT DEFAULT 0;
	END IF;
	# Añadir campo para incluir imágenes en proyecto Remote PC (ticket #708).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='inremotepc' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			ADD inremotepc TINYINT DEFAULT 0;
	END IF;
	# Añadir campo para clave de acceso a la API REST (ticket #708).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='apikey' AND TABLE_NAME='usuarios' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE usuarios
			ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
	END IF;
	# Añadir porcentaje de uso de sistema de ficheros (ticket #711)
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='uso' AND TABLE_NAME='ordenadores_particiones' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores_particiones
			ADD uso TINYINT NOT NULL DEFAULT 0;
	END IF;
	# Añadir nº de serie (ticket #713)
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='numserie' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD numserie varchar(25) DEFAULT NULL AFTER nombreordenador;
	END IF;
	# Eliminar campos no usado en inventario de hardware (ticket #713).
	IF EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='pci' AND TABLE_NAME='tipohardwares' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE tipohardwares
			DROP pci;
	END IF;
	# Añadir servidor de sincronización horaria NTP (ticket #725).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='ntp' AND TABLE_NAME='aulas' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE aulas
			ADD ntp VARCHAR(30) AFTER proxy;
	END IF;
	# Directorios en repo para distintas UO (ticket #678).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='ogunit' AND TABLE_NAME='entidades' AND TABLE_SCHEMA=DATABASE())
		ALTER TABLE entidades
			ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
	END IF;
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='directorio' AND TABLE_NAME='centros' AND TABLE_SCHEMA=DATABASE())
		ALTER TABLE centros
			ADD directorio VARCHAR(50) DEFAULT '';
	END IF;
END//
# Ejecutar actualización condicional.
delimiter ';'
CALL addcols();
DROP PROCEDURE addcols;

# Nuevos tipos de particiones.
INSERT INTO tipospar (codpar, tipopar, clonable) VALUES
	(CONV('A9',16,10), 'NETBSD', 1),
	(CONV('2700',16,10), 'WIN-RECOV', 1),
	(CONV('8302',16,10), 'LINUX', 1),
	(CONV('A504',16,10), 'FREEBSD', 1),
	(CONV('A901',16,10), 'NETBSD-SWAP', 0),
	(CONV('A902',16,10), 'NETBSD', 1),
	(CONV('A903',16,10), 'NETBSD', 1),
	(CONV('A904',16,10), 'NETBSD', 1),
	(CONV('A905',16,10), 'NETBSD', 1),
	(CONV('A906',16,10), 'NETBSD-RAID', 1),
	(CONV('AF02',16,10), 'HFS-RAID', 1),
	(CONV('FB00',16,10), 'VMFS', 1),
	(CONV('FB01',16,10), 'VMFS-RESERV', 1),
	(CONV('FB02',16,10), 'VMFS-KRN', 1)
	ON DUPLICATE KEY UPDATE
		codpar=VALUES(codpar), tipopar=VALUES(tipopar), clonable=VALUES(clonable);

# Preparar generación de clave de acceso a la API REST para el usuario principal (ticket #708).
UPDATE usuarios
	SET apikey = 'APIKEY'
	WHERE idusuario = 1 AND apikey = '';

# Nuevos componentes hardware (ticket #713)
INSERT INTO tipohardwares (idtipohardware, descripcion, urlimg, nemonico) VALUES
	(17, 'Chasis del Sistema', '', 'cha'),
	(18, 'Controladores de almacenamiento', '', 'sto'),
	(19, 'Tipo de proceso de arranque', '', 'boo')
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), urlimg=VALUES(urlimg), nemonico=VALUES(nemonico);

