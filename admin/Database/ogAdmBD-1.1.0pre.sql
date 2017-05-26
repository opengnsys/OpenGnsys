### Fichero de actualización de la base de datos.
# OpenGnsys 1.1.0
#use ogAdmBD

SET GLOBAL event_scheduler = ON;

# Eliminar procedimiento y disparador para evitar errores de ejecución.
DROP PROCEDURE IF EXISTS addcols;
DROP TRIGGER IF EXISTS registrar_acciones;
# Procedimiento para actualización condicional de tablas.
DELIMITER '//'
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
	# Añadir campo para clave de acceso a la API REST de OGAgent (ticket #718).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='agentkey' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD agentkey VARCHAR(32) DEFAULT NULL;
	END IF;
	# Añadir índice para mostrar correctamente el formulario de estado.
	IF NOT EXISTS (SELECT * FROM information_schema.STATISTICS
			WHERE INDEX_NAME='idaulaip' AND TABLE_NAME='ordenadores' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores
			ADD KEY idaulaip (idaula ASC, ip ASC);
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
	THEN
		ALTER TABLE entidades
			ADD ogunit TINYINT(1) NOT NULL DEFAULT 0;
	END IF;
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='directorio' AND TABLE_NAME='centros' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE centros
			ADD directorio VARCHAR(50) DEFAULT '';
	END IF;
	# Nº de revisión de imagen (ticket #737).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='revision' AND TABLE_NAME='imagenes' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE imagenes
			ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER nombreca;
	END IF;
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='revision' AND TABLE_NAME='ordenadores_particiones' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE ordenadores_particiones 
			ADD revision SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER idimagen;
	END IF;
	# Incluir campo sistema operativo en el perfil de software (tickets #738 #713)
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='idnombreso' AND TABLE_NAME='perfilessoft'  AND TABLE_SCHEMA=DATABASE())
	THEN 
		ALTER TABLE perfilessoft
			ADD idnombreso SMALLINT UNSIGNED AFTER idperfilsoft;
	END IF;
	# Añadir campo para clave de acceso a la API REST del repositorio (ticket #743).
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE COLUMN_NAME='apikey' AND TABLE_NAME='repositorios' AND TABLE_SCHEMA=DATABASE())
	THEN
		ALTER TABLE repositorios
			ADD apikey VARCHAR(32) NOT NULL DEFAULT '';
	END IF;
	# Codificar claves de los usuarios, si fuese necesario (ticket #778)
	IF ((SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
	      WHERE COLUMN_NAME='pasguor' AND TABLE_NAME='usuarios' AND TABLE_SCHEMA=DATABASE()) != 56)
	THEN
		ALTER TABLE usuarios
			MODIFY pasguor VARCHAR(56) NOT NULL DEFAULT '';
		INSERT INTO usuarios (idusuario, pasguor)
			SELECT idusuario, pasguor FROM usuarios
			ON DUPLICATE KEY UPDATE
				pasguor=SHA2(VALUES(pasguor),224);
	END IF;
	# Crear tabla de log para la cola de acciones (ticket #782)
	IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS
			WHERE TABLE_NAME='acciones_log' AND TABLE_SCHEMA=DATABASE())
	THEN
		CREATE TABLE acciones_log LIKE acciones;
		ALTER TABLE acciones_log ADD fecha_borrado DATETIME;
	END IF;
END//
# Disparador para mover acciones borradas a la tabla de registro de acciones.
CREATE TRIGGER registrar_acciones BEFORE DELETE ON acciones FOR EACH ROW
BEGIN
	INSERT INTO acciones_log VALUES
		(OLD.idaccion, OLD.tipoaccion, OLD.idtipoaccion, OLD.descriaccion,
		OLD.idordenador, OLD.ip, OLD.sesion, OLD.idcomando, OLD.parametros,
		OLD.fechahorareg, OLD.fechahorafin, OLD.estado, OLD.resultado,
		OLD.descrinotificacion, OLD.ambito, OLD.idambito, OLD.restrambito,
		OLD.idprocedimiento, OLD.idtarea, OLD.idcentro, OLD.idprogramacion,
		NOW());
END//
# Ejecutar actualización condicional.
DELIMITER ';'
CALL addcols();
DROP PROCEDURE addcols;

# Nuevos tipos de particiones y de sistemas de ficheros.
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
INSERT INTO sistemasficheros (idsistemafichero, nemonico, descripcion) VALUES
	(19, 'LINUX-SWAP', 'LINUX-SWAP'),
	(20, 'F2FS', 'F2FS'),
	(21, 'NILFS2', 'NILFS2')
		ON DUPLICATE KEY UPDATE
		idsistemafichero=VALUES(idsistemafichero), nemonico=VALUES(nemonico), descripcion=VALUES(descripcion);


# Preparar generación de claves de acceso a la API REST para el usuario principal y a la del repositorio principal (tickets #708 y #743).
UPDATE usuarios
	SET apikey = 'APIKEY'
	WHERE idusuario = 1 AND apikey = '';
UPDATE repositorios
	SET apikey = 'REPOKEY'
	WHERE idrepositorio = 1 AND apikey = '';

# Nuevos componentes hardware (ticket #713)
INSERT INTO tipohardwares (idtipohardware, descripcion, urlimg, nemonico) VALUES
	(17, 'Chasis del Sistema', '', 'cha'),
	(18, 'Controladores de almacenamiento', '../images/iconos/almacenamiento.png', 'sto'),
	(19, 'Tipo de proceso de arranque', '../images/iconos/arranque.png', 'boo')
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), urlimg=VALUES(urlimg), nemonico=VALUES(nemonico);

# Número de puestos del aula permite valores hasta 32768 (ticket #747)
ALTER TABLE aulas
	MODIFY puestos SMALLINT DEFAULT NULL;

# Nueva tabla para datos del proyecto Remote PC (ticket #708).
CREATE TABLE IF NOT EXISTS remotepc ( 
	id INT(11) NOT NULL, 
	reserved DATETIME DEFAULT NULL, 
	urllogin VARCHAR(100), 
	urllogout VARCHAR(100), 
	PRIMARY KEY (id) 
	) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
ALTER TABLE remotepc
       MODIFY reserved DATETIME DEFAULT NULL;

# Nuevo comando "Enviar mensaje" (ticket #779)
INSERT INTO comandos (idcomando, descripcion, pagina, gestor, funcion, urlimg, 
	aplicambito, visuparametros, parametros, comentarios, activo, submenu) VALUES
	(16, 'Enviar mensaje', '../comandos/EnviarMensaje.php', '../comandos/gestores/gestor_Comandos.php', 'EnviarMensaje', '', 31, '', '', '', 1, '' )
	ON DUPLICATE KEY UPDATE
		descripcion=VALUES(descripcion), pagina=VALUES(pagina),
		gestor=VALUES(gestor), funcion=VALUES(funcion),
		aplicambito=VALUES(aplicambito), activo=VALUES(activo);
INSERT INTO parametros (idparametro, nemonico, descripcion, nomidentificador, nomtabla, nomliteral, tipopa, visual) VALUES
	(39, 'tit', 'Título', '', '', '', 0, 1),
	(40, 'msj', 'Contenido', '', '', '', 0, 1)
	ON DUPLICATE KEY UPDATE
		nemonico=VALUES(nemonico), descripcion=VALUES(descripcion),
		tipopa=VALUES(tipopa), visual=VALUES(visual);

# Evitar error de MySQL con modo NO_ZERO_DATE (ticket #730).
ALTER TABLE acciones
	MODIFY fechahorareg DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00',
	MODIFY fechahorafin DATETIME NOT NULL DEFAULT '1970-01-01 00:00:00';

